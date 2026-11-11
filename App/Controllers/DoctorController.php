<?php
// Kontrolér pre lekárov – vytvorené s pomocou AI (Claude), upravené autorom

namespace App\Controllers;

use App\Helpers\DoctorSchedule;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\WorkingHour;
use Framework\Core\BaseController;
use Framework\Http\HttpException;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class DoctorController extends BaseController
{
    /** Na koľko týždňov dopredu sa dajú prezerať voľné termíny. */
    private const MAX_WEEKS = 12;

    /**
     * Zoznam lekárov aj profil lekára sú verejné. Prípad default zakáže každú novú akciu, kým nedostane vlastné pravidlo.
     */
    public function authorize(Request $request, string $action): bool
    {
        return match ($action) {
            'index', 'show' => true,
            default => false,
        };
    }

    /**
     * Zoznam lekárov načítaný z databázy (vyhľadávanie zatiaľ nefiltruje).
     */
    public function index(Request $request): Response
    {
        // Súvisiace záznamy pripravuje kontrolér, pohľad do databázy nesiaha.
        // Keďže lekári pochádzajú z jedného volania getAll(), framework načíta ich používateľov,
        // kliniky aj špecializácie hromadne, nie zvlášť pre každého lekára.
        $doctors = [];
        foreach (Doctor::getAll() as $doctor) {
            $doctors[] = [
                'doctor' => $doctor,
                'user' => $doctor->getUser(),
                'clinic' => $doctor->getClinic(),
                'specializations' => $doctor->getSpecializations(),
            ];
        }

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
        $doctor = Doctor::getOne((int)$request->value('id'));
        if ($doctor === null) {
            throw new HttpException(404, 'Lekár neexistuje');
        }

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
}