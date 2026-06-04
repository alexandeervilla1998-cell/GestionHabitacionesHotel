<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ReservaServicio;

class ReservaServicioController extends Controller
{
    public function index()
    {
        $reservaServicios = ReservaServicio::with(['reserva.cliente', 'servicio'])->get();
        return view('reserva_servicio.index', compact('reservaServicios'));
    }
}
