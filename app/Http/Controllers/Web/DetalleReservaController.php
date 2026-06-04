<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DetalleReserva;

class DetalleReservaController extends Controller
{
    public function index()
    {
        $detalles = DetalleReserva::with(['reserva.usuario', 'habitacion'])->get();
        return view('detalle_reserva.index', compact('detalles'));
    }
}
