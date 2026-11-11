<?php
// Kontrolér pre lekárov (zoznam, profil a správa – CRUD) – vytvorené s pomocou AI (Claude), upravené autorom

namespace App\Controllers;

use App\Helpers\DoctorSchedule;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSpecialization;
use App\Models\Specialization;
use App\Models\User;
use App\Models\WorkingHour;
use Exception;
use Framework\Core\BaseController;
use Framework\DB\Connection;
use Framework\Http\HttpException;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class DoctorController extends BaseController
{
    /** Na koľko týždňov dopredu sa dajú prezerať voľné termíny. */
    private const MAX_WEEKS = 12;

    /**
     * Zoznam lekárov a profil sú verejné. Správa lekárov (manage, add, edit, save, delete) bude neskôr len pre administrátora,
     * prihlasovanie však zatiaľ nie je. Prípad default zakáže každú novú akciu, kým nedostane vlastné pravidlo.
     */
    public function authorize(Request $request, string $action): bool
    {
        return match ($action) {
            'index', 'show' => true,
            'manage', 'add', 'edit', 'save', 'delete' => true,
            default => false,
        };
    }

    /**
     * Zoznam lekárov pre pacientov (vyhľadávanie zatiaľ nefiltruje).
     */
    public function index(Request $request): Response
    {
        $doctors = $this->loadDoctorsWithDetails();

        // Do výberu vo vyhľadávaní dávame len aktívne špecializácie
        $specializations = Specialization::getAll('`is_active` = ?', [1], '`name` asc');

        return $this->html(compact('doctors', 'specializations'));
    }

    /**
     * Profil lekára s rozpisom voľných termínov na jeden týždeň.
     * URL: ?c=doctor&a=show&id=1&week=0 (week = 0 tento týždeň, 1 budúci …)
     */
    public function show(Request $request): Response
    {
        $doctor = $this->getDoctorOr404((int)$request->value('id'));

        // Číslo týždňa obmedzíme na 0 až MAX_WEEKS - 1, aby sa cez URL nedalo ísť do minulosti ani príliš ďaleko
        $week = max(0, min((int)$request->value('week'), self::MAX_WEEKS - 1));
        $monday = DoctorSchedule::mondayOfWeek($week);

        // Ordinačné hodiny zoradíme od pondelka po nedeľu a v rámci dňa podľa času
        $workingHours = $doctor->getWorkingHours();
        usort($workingHours, fn(WorkingHour $a, WorkingHour $b) =>
            [array_search($a->getDayOfWeek(), WorkingHour::DAYS), $a->getStartTime()]
            <=> [array_search($b->getDayOfWeek(), WorkingHour::DAYS), $b->getStartTime()]);

        return $this->html([
            'doctor' => $doctor,
            // nie 'user' – premenná $user je v pohľadoch rezervovaná pre prihláseného používateľa
            'doctorUser' => $doctor->getUser(),
            'clinic' => $doctor->getClinic(),
            'specializations' => $doctor->getSpecializations(),
            'workingHours' => $workingHours,
            'days' => DoctorSchedule::getWeek($doctor, $monday),
            'week' => $week,
            'maxWeek' => self::MAX_WEEKS - 1,
        ]);
    }

    /**
     * Read – tabuľka všetkých lekárov na správu.
     */
    public function manage(Request $request): Response
    {
        $doctors = $this->loadDoctorsWithDetails();

        // Jednorazové správy po uložení alebo zmazaní (flash správy uložené v session)
        $session = $this->app->getSession();
        $flash = $session->get('flash');
        $flashError = $session->get('flashError');
        $session->remove('flash');
        $session->remove('flashError');

        return $this->html(compact('doctors', 'flash', 'flashError'));
    }

    /**
     * Create (1. časť) – prázdny formulár.
     */
    public function add(Request $request): Response
    {
        return $this->form(new Doctor(), new User(), [], []);
    }

    /**
     * Update (1. časť) – formulár predvyplnený z databázy vrátane zaškrtnutých špecializácií.
     */
    public function edit(Request $request): Response
    {
        $doctor = $this->getDoctorOr404((int)$request->value('id'));
        return $this->form($doctor, $doctor->getUser(), $doctor->getSpecializationIds(), []);
    }

    /**
     * Create / Update (2. časť) – uloží lekára, jeho používateľský účet a väzby na špecializácie.
     */
    public function save(Request $request): Response
    {
        if (!$request->isPost()) {
            throw new HttpException(405); // Method Not Allowed
        }

        // nový lekár, alebo úprava existujúceho?
        $id = (int)$request->post('id');
        $doctor = $id > 0 ? $this->getDoctorOr404($id) : new Doctor();
        $doctorUser = $id > 0 ? $doctor->getUser() : new User();

        // Meno, e-mail a telefón sú v tabuľke users (lekár má vlastný používateľský účet, vzťah 1:1)
        $doctorUser->setFirstName(trim((string)$request->post('firstName')));
        $doctorUser->setLastName(trim((string)$request->post('lastName')));
        $doctorUser->setEmail(trim((string)$request->post('email')));
        $phone = trim((string)$request->post('phone'));
        $doctorUser->setPhone($phone === '' ? null : $phone);
        $doctorUser->setRole('doctor');

        // Údaje lekára sú v tabuľke doctors
        $doctor->setClinicId((int)$request->post('clinicId'));
        $bio = trim((string)$request->post('bio'));
        $doctor->setBio($bio === '' ? null : $bio);
        $doctor->setAppointmentLength((int)$request->post('appointmentLength'));

        // Zaškrtnuté špecializácie – názov "specializations[]" vráti pole id, alebo null, ak nie je zaškrtnutá žiadna
        $specializationIds = array_values(array_unique(array_map('intval', (array)$request->post('specializations'))));

        // Heslo: pri novom lekárovi povinné, pri úprave prázdne pole znamená "nemeniť"
        $password = (string)$request->post('password');

        $errors = $this->validate($doctor, $doctorUser, $specializationIds, $password);
        if (count($errors) > 0) {
            return $this->form($doctor, $doctorUser, $specializationIds, $errors);
        }

        if ($password !== '') {
            $doctorUser->setPassword(password_hash($password, PASSWORD_DEFAULT));
        }

        // Používateľ, lekár a väzby sa uložia buď všetky, alebo žiadne (transakcia)
        $db = Connection::getInstance();
        $db->beginTransaction();
        try {
            $doctorUser->save();
            $doctor->setUserId($doctorUser->getId()); // id nového používateľa doplní save()
            $doctor->save();
            $this->saveSpecializations($doctor, $specializationIds);
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->app->getSession()->set('flash', 'Lekár ' . $doctorUser->getFullName() . ' bol uložený.');
        // 303 See Other – presmerovanie po odoslaní formulára (Post/Redirect/Get)
        return $this->redirect($this->url('doctor.manage'))->setStatusCode(303);
    }

    /**
     * Delete – zmaže lekára. Lekár s termínmi sa zmazať nedá (v databáze by ich cudzí kľúč nedovolil zmazať).
     */
    public function delete(Request $request): Response
    {
        if (!$request->isPost()) {
            throw new HttpException(405);
        }

        $doctor = $this->getDoctorOr404((int)$request->post('id'));
        $doctorUser = $doctor->getUser();
        $session = $this->app->getSession();

        if (Appointment::getCount('`doctor_id` = ?', [$doctor->getId()]) > 0) {
            $session->set('flashError', 'Lekára ' . $doctorUser->getFullName() . ' nie je možné zmazať, pretože má termíny.');
        } else {
            // Zmazaním používateľa databáza zmaže aj lekára (ON DELETE CASCADE) a tým aj jeho väzby na špecializácie,
            // ordinačné hodiny a neprítomnosti
            $doctorUser->delete();
            $session->set('flash', 'Lekár ' . $doctorUser->getFullName() . ' bol zmazaný.');
        }

        return $this->redirect($this->url('doctor.manage'))->setStatusCode(303);
    }

    /**
     * Lekári spolu s používateľom, klinikou a špecializáciami – pohľad tak nemusí siahať do databázy.
     *
     * @return array<array{doctor: Doctor, user: User, clinic: Clinic, specializations: Specialization[]}>
     */
    private function loadDoctorsWithDetails(): array
    {
        $doctors = [];
        foreach (Doctor::getAll() as $doctor) {
            $doctors[] = [
                'doctor' => $doctor,
                'user' => $doctor->getUser(),
                'clinic' => $doctor->getClinic(),
                'specializations' => $doctor->getSpecializations(),
            ];
        }
        return $doctors;
    }

    /**
     * Zobrazí formulár na pridanie alebo úpravu lekára.
     *
     * @param int[] $selectedIds id zaškrtnutých špecializácií
     * @param string[] $errors
     */
    private function form(Doctor $doctor, User $doctorUser, array $selectedIds, array $errors): Response
    {
        return $this->html([
            'doctor' => $doctor,
            'doctorUser' => $doctorUser,
            'clinics' => Clinic::getAll(orderBy: '`name` asc'),
            'specializations' => Specialization::getAll(orderBy: '`name` asc'),
            'selectedIds' => $selectedIds,
            'errors' => $errors,
        ], 'form');
    }

    /**
     * Upraví väzby lekár ↔ špecializácia (tabuľka doctor_specializations) podľa zaškrtnutých políčok:
     * odškrtnuté väzby zmaže, novo zaškrtnuté pridá, nezmenené nechá tak.
     *
     * @param int[] $specializationIds
     */
    private function saveSpecializations(Doctor $doctor, array $specializationIds): void
    {
        $existingIds = [];
        // getAll namiesto getAllRelated: nového lekára ORM ešte nenačítalo z databázy a getAllRelated by skončil výnimkou
        foreach (DoctorSpecialization::getAll('`doctor_id` = ?', [$doctor->getId()]) as $link) {
            if (in_array($link->getSpecializationId(), $specializationIds, true)) {
                $existingIds[] = $link->getSpecializationId();
            } else {
                $link->delete();
            }
        }

        foreach (array_diff($specializationIds, $existingIds) as $specializationId) {
            $link = new DoctorSpecialization();
            $link->setDoctorId($doctor->getId());
            $link->setSpecializationId($specializationId);
            $link->save();
        }
    }

    /**
     * Načíta lekára podľa id, alebo vyhodí chybu 404.
     * Metóda je private, preto ju nie je možné zavolať ako akciu cez URL.
     */
    private function getDoctorOr404(int $id): Doctor
    {
        $doctor = Doctor::getOne($id);
        if ($doctor === null) {
            throw new HttpException(404, 'Lekár neexistuje');
        }
        return $doctor;
    }

    /**
     * @param int[] $specializationIds
     * @return string[] zoznam chybových hlásení (prázdny, ak je všetko v poriadku)
     */
    private function validate(Doctor $doctor, User $doctorUser, array $specializationIds, string $password): array
    {
        $errors = [];

        if ($doctorUser->getFirstName() === '' || $doctorUser->getLastName() === '') {
            $errors[] = 'Meno aj priezvisko sú povinné.';
        } elseif (mb_strlen($doctorUser->getFirstName()) > 50 || mb_strlen($doctorUser->getLastName()) > 50) {
            $errors[] = 'Meno aj priezvisko môžu mať najviac 50 znakov.';
        }

        if (filter_var($doctorUser->getEmail(), FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Zadajte platný e-mail.';
        } elseif (User::getCount('`email` = ? AND `id` <> ?', [$doctorUser->getEmail(), $doctorUser->getId() ?? 0]) > 0) {
            // V databáze je na e-maile UNIQUE kľúč – bez tejto kontroly by save() skončil výnimkou
            $errors[] = 'Používateľ s týmto e-mailom už existuje.';
        }

        if ($doctorUser->getId() === null && $password === '') {
            $errors[] = 'Heslo je pri novom lekárovi povinné.';
        } elseif ($password !== '' && mb_strlen($password) < 8) {
            $errors[] = 'Heslo musí mať aspoň 8 znakov.';
        }

        if (Clinic::getOne($doctor->getClinicId()) === null) {
            $errors[] = 'Vyberte kliniku.';
        }

        if ($doctor->getAppointmentLength() < 5 || $doctor->getAppointmentLength() > 120) {
            $errors[] = 'Dĺžka termínu musí byť 5 až 120 minút.';
        }

        if (count($specializationIds) === 0) {
            $errors[] = 'Vyberte aspoň jednu špecializáciu.';
        } elseif (Specialization::getCount('`id` IN (' . implode(',', array_fill(0, count($specializationIds), '?')) . ')',
                $specializationIds) !== count($specializationIds)) {
            $errors[] = 'Vybraná špecializácia neexistuje.';
        }

        return $errors;
    }
}