<?php
// Kontrolér pre lekárov – vytvorené s pomocou AI (Claude), upravené autorom

namespace App\Controllers;

use App\Models\Doctor;
use App\Models\Specialization;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class DoctorController extends BaseController
{
    /**
     * Zoznam lekárov je verejný. Prípad default zakáže každú novú akciu, kým nedostane vlastné pravidlo.
     */
    public function authorize(Request $request, string $action): bool
    {
        return match ($action) {
            'index' => true,
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
}