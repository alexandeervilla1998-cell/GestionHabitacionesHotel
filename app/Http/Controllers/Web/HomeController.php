<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Usuario;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'usuarios' => Usuario::count(),
            'habitaciones' => Habitacion::count(),
            'reservas' => Reserva::count(),
            'facturas' => Factura::count(),
            'pagos' => Pago::where('estado_pago', 'completado')->sum('monto'),
        ];

        return view('home.home', compact('stats'));
    }
}
