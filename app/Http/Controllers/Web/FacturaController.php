<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Reserva;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    const IVA = 0.13;

    public function index()
    {
        $facturas = Factura::with(['reserva.cliente', 'pagos'])->orderBy('fecha_emision', 'desc')->get();
        return view('facturas.index', compact('facturas'));
    }

    public function create()
    {
        $reservas = Reserva::with(['cliente', 'detalleReservas', 'servicios'])
            ->where('estado', 'confirmada')
            ->where('activo', true)
            ->doesntHave('factura')
            ->orderBy('id', 'desc')
            ->get();

        return view('facturas.create', compact('reservas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reserva_id' => 'required|exists:reservas,id|unique:facturas,reserva_id',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $reserva = Reserva::with(['detalleReservas', 'servicios'])->findOrFail($request->reserva_id);

                if ($reserva->estado !== 'confirmada') {
                    return redirect()->back()->withInput()->with('error', 'Solo se pueden generar facturas para reservas confirmadas.');
                }

                $subtotal  = $reserva->subtotal_habitaciones + $reserva->subtotal_servicios;
                $impuestos = round($subtotal * self::IVA, 2);
                $total     = $subtotal + $impuestos;

                $factura = new Factura();
                $factura->reserva_id     = $reserva->id;
                $factura->numero_factura = 'FAC-' . date('Y') . '-' . str_pad((string) (Factura::count() + 1), 6, '0', STR_PAD_LEFT);
                $factura->subtotal       = $subtotal;
                $factura->impuestos      = $impuestos;
                $factura->total          = $total;
                $factura->fecha_emision  = now();

                return $factura->save()
                    ? redirect()->route('facturas.index')->with('success', 'Factura generada. IVA 13% aplicado automaticamente.')
                    : redirect()->back()->withInput()->with('error', 'Error al generar la factura.');
            });
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al generar la factura: ' . $ex->getMessage());
        }
    }

    public function regenerar(string $id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $factura = Factura::findOrFail($id);
                $reserva = Reserva::with(['detalleReservas', 'servicios'])->findOrFail($factura->reserva_id);

                $subtotal  = $reserva->subtotal_habitaciones + $reserva->subtotal_servicios;
                $impuestos = round($subtotal * self::IVA, 2);
                $total     = $subtotal + $impuestos;

                $factura->subtotal  = $subtotal;
                $factura->impuestos = $impuestos;
                $factura->total     = $total;
                $factura->save();

                return redirect()->route('facturas.index')->with('success', 'Factura #' . $factura->numero_factura . ' regenerada exitosamente con los valores actualizados de la reserva.');
            });
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al regenerar la factura: ' . $ex->getMessage());
        }
    }
}
