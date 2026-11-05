<?php
// Správa špecializácií (CRUD) – vytvorené s pomocou AI (Claude)

namespace App\Controllers;

use App\Models\Specialization;
use Framework\Core\BaseController;
use Framework\Http\HttpException;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class SpecializationController extends BaseController
{
    /**
     * Povolené akcie. Prihlasovanie zatiaľ nie je, preto sú dostupné všetkým;
     * neskôr sa sem doplní podmienka, že ich môže vykonať len administrátor.
     * Prípad default zakáže každú novú akciu, kým nedostane vlastné pravidlo.
     */
    public function authorize(Request $request, string $action): bool
    {
        return match ($action) {
            'index', 'add', 'edit', 'save', 'delete' => true,
            default => false,
        };
    }

    /**
     * Read – zoznam všetkých špecializácií z databázy.
     */
    public function index(Request $request): Response
    {
        $specializations = Specialization::getAll(orderBy: '`name` asc');

        // Dáta pre pohľad pripravuje kontrolér – pohľad sám do databázy nesiaha
        $doctorCounts = [];
        foreach ($specializations as $specialization) {
            $doctorCounts[$specialization->getId()] = $specialization->getDoctorCount();
        }

        // Jednorazová správa po uložení alebo zmazaní (flash správa uložená v session)
        $session = $this->app->getSession();
        $flash = $session->get('flash');
        $session->remove('flash');

        return $this->html(compact('specializations', 'doctorCounts', 'flash'));
    }

    /**
     * Create (1. časť) – prázdny formulár.
     */
    public function add(Request $request): Response
    {
        return $this->html(['specialization' => new Specialization(), 'errors' => []], 'form');
    }

    /**
     * Update (1. časť) – formulár predvyplnený z databázy.
     */
    public function edit(Request $request): Response
    {
        $specialization = $this->getSpecializationOr404((int)$request->value('id'));
        return $this->html(['specialization' => $specialization, 'errors' => []], 'form');
    }

    /**
     * Create / Update (2. časť) – uloží formulár.
     */
    public function save(Request $request): Response
    {
        if (!$request->isPost()) {
            throw new HttpException(405); // Method Not Allowed
        }

        // nová špecializácia, alebo úprava existujúcej?
        $id = (int)$request->post('id');
        $specialization = $id > 0 ? $this->getSpecializationOr404($id) : new Specialization();

        $specialization->setName(trim((string)$request->post('name')));
        $description = trim((string)$request->post('description'));
        $specialization->setDescription($description === '' ? null : $description);
        // nezaškrtnuté zaškrtávacie pole prehliadač vôbec neodošle
        $specialization->setIsActive($request->post('isActive') !== null);

        $errors = $this->validate($specialization);
        if (count($errors) > 0) {
            // znova zobrazíme formulár s chybami a zadanými údajmi
            return $this->html(['specialization' => $specialization, 'errors' => $errors], 'form');
        }

        $specialization->save();
        $this->app->getSession()->set('flash', 'Špecializácia bola uložená.');
        // 303 See Other – významovo správne presmerovanie po odoslaní formulára (Post/Redirect/Get)
        return $this->redirect($this->url('specialization.index'))->setStatusCode(303);
    }

    /**
     * Delete – vymaže špecializáciu. Väzby na lekárov zmaže databáza sama (ON DELETE CASCADE).
     */
    public function delete(Request $request): Response
    {
        if (!$request->isPost()) {
            throw new HttpException(405);
        }

        $specialization = $this->getSpecializationOr404((int)$request->post('id'));
        $specialization->delete();
        $this->app->getSession()->set('flash', 'Špecializácia bola zmazaná.');
        return $this->redirect($this->url('specialization.index'))->setStatusCode(303);
    }

    /**
     * Načíta špecializáciu podľa id, alebo vyhodí chybu 404.
     * Metóda je private, preto ju nie je možné zavolať ako akciu cez URL.
     */
    private function getSpecializationOr404(int $id): Specialization
    {
        $specialization = Specialization::getOne($id);
        if ($specialization === null) {
            throw new HttpException(404, 'Špecializácia neexistuje');
        }
        return $specialization;
    }

    /**
     * @return string[] zoznam chybových hlásení (prázdny, ak je všetko v poriadku)
     */
    private function validate(Specialization $specialization): array
    {
        $errors = [];

        if ($specialization->getName() === '') {
            $errors[] = 'Názov je povinný.';
        } elseif (mb_strlen($specialization->getName()) > 100) {
            $errors[] = 'Názov môže mať najviac 100 znakov.';
        } elseif (Specialization::getCount('`name` = ? AND `id` <> ?', [$specialization->getName(), $specialization->getId() ?? 0]) > 0) {
            // V databáze je na názve UNIQUE kľúč – bez tejto kontroly by save() skončil výnimkou
            $errors[] = 'Špecializácia s týmto názvom už existuje.';
        }

        if (mb_strlen((string)$specialization->getDescription()) > 1000) {
            $errors[] = 'Popis môže mať najviac 1000 znakov.';
        }

        return $errors;
    }
}