<?php

namespace App\Http\Controllers;

use App\Models\Zone;

class ZoneController extends Controller
{
    public function index()
    {
        return Zone::all();
    }
}
