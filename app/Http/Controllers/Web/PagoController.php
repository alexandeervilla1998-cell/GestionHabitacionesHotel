<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Pago;
use App\Services\ReservaService;
use Exception;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    protected $reservaService;

    public function __construct(ReservaService $reservaService)
    {
        $this->reservaService = $reservaService;
    }

    public function index()
    {
        $pagos = Pago::with('factura')->orderBy('creado_en', 'desc')->get();
        return view('pagos.index', compact('pagos'));
    }

    public function create()
    {
        $facturas = Factura::with('reserva')->orderBy('id', 'desc')->get();
        return view('pagos.create', compact('facturas'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'factura_id' => 'required|exists:facturas,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,tarjeta_credito,tarjeta_debito,transferencia',
            'estado_pago' => 'required|in:pendiente,completado,fallido,reembolsado',
        ]);

        try {
            $factura = Factura::findOrFail($validatedData['factura_id']);

            if ($validatedData['monto'] > $factura->saldo_pendiente) {
                return redirect()->back()->withInput()->with('error', 'El monto excede el saldo pendiente.');
            }

            $data = new Pago();
            $data->factura_id = $factura->id;
            $data->monto = $validatedData['monto'];
            $data->metodo_pago = $this->normalizarMetodoPago($validatedData['metodo_pago']);
            $data->estado_pago = $validatedData['estado_pago'];
            $data->creado_en = now();

            return $data->save()
                ? redirect()->route('pagos.index')->with('success', 'Pago registrado exitosamente.')
                : redirect()->back()->withInput()->with('error', 'Error al registrar el pago.');
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al registrar el pago: ' . $ex->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $pago = Pago::find($id);
            $facturas = Factura::with('reserva')->orderBy('id', 'desc')->get();

            if ($pago == null) {
                return redirect()->route('pagos.index')->with('error', 'Registro no encontrado.');
            }

            return view('pagos.edit', compact('pago', 'facturas'));
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al buscar el registro.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $pago = Pago::with('factura')->find($id);

            if ($pago == null) {
                return redirect()->route('pagos.index')->with('error', 'Registro no encontrado.');
            }

            $validatedData = $request->validate([
                'monto' => 'required|numeric|min:0.01',
                'metodo_pago' => 'required|in:efectivo,tarjeta_credito,tarjeta_debito,transferencia',
                'estado_pago' => 'required|in:pendiente,completado,fallido,reembolsado',
            ]);

            $saldoDisponible = $pago->factura->saldo_pendiente + ($pago->estado_pago === 'completado' ? (float) $pago->monto : 0);

            if ($validatedData['monto'] > $saldoDisponible) {
                return redirect()->back()->withInput()->with('error', 'El monto excede el saldo pendiente.');
            }

            $pago->monto = $validatedData['monto'];
            $pago->metodo_pago = $this->normalizarMetodoPago($validatedData['metodo_pago']);
            $pago->estado_pago = $validatedData['estado_pago'];

            return $pago->save()
                ? redirect()->route('pagos.index')->with('success', 'Registro actualizado exitosamente.')
                : redirect()->back()->withInput()->with('error', 'Error al actualizar el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el registro: ' . $ex->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $pago = Pago::find($id);

            if ($pago == null) {
                return redirect()->route('pagos.index')->with('error', 'Registro no encontrado.');
            }

            if ($pago->estado_pago === 'completado') {
                return redirect()->back()->with('error', 'No se puede eliminar un pago completado.');
            }

            return $pago->delete()
                ? redirect()->route('pagos.index')->with('success', 'Registro eliminado exitosamente.')
                : redirect()->back()->with('error', 'Error al eliminar el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al eliminar el registro.');
        }
    }

    public function procesar(string $id)
    {
        try {
            $pago = Pago::with('factura.reserva')->find($id);

            if ($pago == null) {
                return redirect()->route('pagos.index')->with('error', 'Registro no encontrado.');
            }

            $pago->estado_pago = 'completado';
            $pago->save();

            // Auto-confirm reservation if invoice is fully paid
            if ($pago->factura && $pago->factura->estaPagada()) {
                $this->reservaService->verificarPagoYConfirmarReserva($pago->factura->reserva);
            }

            return redirect()->route('pagos.index')->with('success', 'Pago procesado exitosamente.');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al procesar el pago.');
        }
    }

    public function cancelar(string $id)
    {
        return $this->cambiarEstado($id, 'fallido', 'Pago cancelado exitosamente.');
    }

    public function reembolsar(string $id)
    {
        return $this->cambiarEstado($id, 'reembolsado', 'Pago reembolsado exitosamente.');
    }

    private function cambiarEstado(string $id, string $estado, string $message)
    {
        try {
            $pago = Pago::find($id);

            if ($pago == null) {
                return redirect()->route('pagos.index')->with('error', 'Registro no encontrado.');
            }

            $pago->estado_pago = $estado;
            $pago->save();

            return redirect()->route('pagos.index')->with('success', $message);
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al procesar el pago.');
        }
    }

    private function normalizarMetodoPago(string $metodoPago): string
    {
        return match ($metodoPago) {
            'tarjeta_credito' => 'tarjeta_crédito',
            'tarjeta_debito' => 'tarjeta_débito',
            default => $metodoPago,
        };
    }

    public function pagarReserva(string $reservaId)
    {
        try {
            $reserva = \App\Models\Reserva::with('factura')->findOrFail($reservaId);

            if ($reserva->estado !== 'pendiente') {
                return redirect()->back()->with('error', 'Solo se pueden pagar reservas en estado pendiente.');
            }

            // Generate invoice if it doesn't exist
            if (!$reserva->factura) {
                $factura = $this->reservaService->generarFactura($reserva);
            } else {
                $factura = $reserva->factura;
            }

            // Create payment for the full amount
            $pago = Pago::create([
                'factura_id' => $factura->id,
                'monto' => $factura->total,
                'metodo_pago' => 'efectivo',
                'estado_pago' => 'completado',
                'creado_en' => now(),
            ]);

            // Auto-confirm reservation
            $this->reservaService->verificarPagoYConfirmarReserva($reserva);

            return redirect()->route('reservas.index')->with('success', 'Reserva pagada y confirmada exitosamente.');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al procesar el pago: ' . $ex->getMessage());
        }
    }
}
