<?php
// Kontrolér pre lekárov – vytvorené s pomocou AI (Claude), upravené autorom

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class DoctorController extends BaseController
{
    /**
     * Zoznam lekárov s vyhľadávaním (zatiaľ statický obsah).
     */
    public function index(Request $request): Response
    {
        return $this->html();
    }
}