<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Exception;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado', 'activo');

        $query = Servicio::orderBy('nombre');

        if ($estado === 'inactivo') {
            $query->where('activo', false);
        } else {
            $query->where('activo', true);
        }

        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('precio', 'like', "%{$buscar}%");
            });
        }

        $servicios = $query->get();

        return view('servicios.index', compact('servicios', 'estado', 'buscar'));
    }

    public function create()
    {
        return view('servicios.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0',
            'activo' => 'nullable|boolean',
        ]);

        try {
            $data = new Servicio();
            $data->nombre = $validatedData['nombre'];
            $data->precio = $validatedData['precio'];
            $data->activo = $request->boolean('activo', true);

            return $data->save()
                ? redirect()->route('servicios.index')->with('success', 'Registro creado exitosamente.')
                : redirect()->back()->withInput()->with('error', 'Error al crear el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al crear el registro: ' . $ex->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $servicio = Servicio::find($id);

            if ($servicio == null) {
                return redirect()->route('servicios.index')->with('error', 'Registro no encontrado.');
            }

            return view('servicios.edit', compact('servicio'));
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al buscar el registro.');
        }
    }

    public function update(Request $request, string $id)
    {
        $servicio = Servicio::find($id);

        if ($servicio == null) {
            return redirect()->route('servicios.index')->with('error', 'Registro no encontrado.');
        }

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0',
            'activo' => 'nullable|boolean',
        ]);

        try {
            $servicio->nombre = $validatedData['nombre'];
            $servicio->precio = $validatedData['precio'];
            $servicio->activo = $request->boolean('activo');

            return $servicio->save()
                ? redirect()->route('servicios.index')->with('success', 'Registro actualizado exitosamente.')
                : redirect()->back()->withInput()->with('error', 'Error al actualizar el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el registro: ' . $ex->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $servicio = Servicio::find($id);

            if ($servicio == null) {
                return redirect()->route('servicios.index')->with('error', 'Registro no encontrado.');
            }

            $servicio->activo = false;

            return $servicio->save()
                ? redirect()->route('servicios.index')->with('success', 'Registro desactivado exitosamente.')
                : redirect()->back()->with('error', 'Error al desactivar el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al desactivar el registro.');
        }
    }
}
