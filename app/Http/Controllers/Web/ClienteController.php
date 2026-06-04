<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado', 'activo');

        $query = Cliente::orderBy('creado_en', 'desc');

        if ($estado === 'inactivo') {
            $query->where('activo', false);
        } else {
            $query->where('activo', true);
        }

        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('correo', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%")
                  ->orWhere('identificacion', 'like', "%{$buscar}%");
            });
        }

        $clientes = $query->get();

        return view('clientes.index', compact('clientes', 'estado', 'buscar'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100',
            'correo' => 'required|email|max:150|unique:clientes,correo',
            'telefono' => 'nullable|string|max:20',
            'identificacion' => 'nullable|string|max:50|unique:clientes,identificacion',
            'activo' => 'nullable|boolean',
        ]);

        try {
            $data = new Cliente();
            $data->nombre = $validatedData['nombre'];
            $data->correo = $validatedData['correo'];
            $data->telefono = $validatedData['telefono'] ?? null;
            $data->identificacion = $validatedData['identificacion'] ?? null;
            $data->activo = $request->boolean('activo', true);

            return $data->save()
                ? redirect()->route('clientes.index')->with('success', 'Registro creado exitosamente.')
                : redirect()->back()->withInput()->with('error', 'Error al crear el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al crear el registro: ' . $ex->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $cliente = Cliente::find($id);

            if ($cliente == null) {
                return redirect()->route('clientes.index')->with('error', 'Registro no encontrado.');
            }

            return view('clientes.edit', compact('cliente'));
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al buscar el registro.');
        }
    }

    public function update(Request $request, string $id)
    {
        $cliente = Cliente::find($id);

        if ($cliente == null) {
            return redirect()->route('clientes.index')->with('error', 'Registro no encontrado.');
        }

        $rules = [
            'nombre' => 'required|string|max:100',
            'correo' => ['required', 'email', 'max:150', Rule::unique('clientes', 'correo')->ignore($cliente->id)],
            'telefono' => 'nullable|string|max:20',
            'identificacion' => ['nullable', 'string', 'max:50', Rule::unique('clientes', 'identificacion')->ignore($cliente->id)],
            'activo' => 'nullable|boolean',
        ];

        $validatedData = $request->validate($rules);

        try {
            $cliente->nombre = $validatedData['nombre'];
            $cliente->correo = $validatedData['correo'];
            $cliente->telefono = $validatedData['telefono'] ?? null;
            $cliente->identificacion = $validatedData['identificacion'] ?? null;
            $cliente->activo = $request->boolean('activo');

            return $cliente->save()
                ? redirect()->route('clientes.index')->with('success', 'Registro actualizado exitosamente.')
                : redirect()->back()->withInput()->with('error', 'Error al actualizar el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el registro: ' . $ex->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $cliente = Cliente::find($id);

            if ($cliente == null) {
                return redirect()->route('clientes.index')->with('error', 'Registro no encontrado.');
            }

            if ($cliente->reservas()->where('estado', 'confirmada')->exists()) {
                return redirect()->back()->with('error', 'No se puede eliminar un cliente con reservas confirmadas.');
            }

            $cliente->activo = false;

            return $cliente->save()
                ? redirect()->route('clientes.index')->with('success', 'Registro desactivado exitosamente.')
                : redirect()->back()->with('error', 'Error al desactivar el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al desactivar el registro.');
        }
    }
}
