<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    private const PASSWORD_RULES = [
        'required',
        'string',
        'min:8',
        'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
    ];

    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado', 'activo');

        $query = Usuario::orderBy('creado_en', 'desc');

        if ($estado === 'inactivo') {
            $query->where('activo', false);
        } else {
            $query->where('activo', true);
        }

        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('correo', 'like', "%{$buscar}%")
                  ->orWhere('rol', 'like', "%{$buscar}%");
            });
        }

        $usuarios = $query->get();

        return view('usuarios.index', compact('usuarios', 'estado', 'buscar'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100',
            'correo' => 'required|email|max:150|unique:usuarios,correo',
            'password' => self::PASSWORD_RULES,
            'rol' => ['required', Rule::in(['admin', 'cliente', 'recepcionista'])],
            'activo' => 'nullable|boolean',
        ], [
            'password.regex' => 'La contraseña debe incluir letras y números (ejemplo: abc123).',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        try {
            $data = new Usuario();
            $data->nombre = $validatedData['nombre'];
            $data->correo = $validatedData['correo'];
            $data->password = Hash::make($validatedData['password']);
            $data->rol = $validatedData['rol'];
            $data->activo = $request->boolean('activo', true);

            return $data->save()
                ? redirect()->route('usuarios.index')->with('success', 'Registro creado exitosamente.')
                : redirect()->back()->withInput()->with('error', 'Error al crear el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al crear el registro: ' . $ex->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $usuario = Usuario::find($id);

            if ($usuario == null) {
                return redirect()->route('usuarios.index')->with('error', 'Registro no encontrado.');
            }

            return view('usuarios.edit', compact('usuario'));
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al buscar el registro.');
        }
    }

    public function update(Request $request, string $id)
    {
        $usuario = Usuario::find($id);

        if ($usuario == null) {
            return redirect()->route('usuarios.index')->with('error', 'Registro no encontrado.');
        }

        $rules = [
            'nombre' => 'required|string|max:100',
            'correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($usuario->id)],
            'rol' => ['required', Rule::in(['admin', 'cliente', 'recepcionista'])],
            'activo' => 'nullable|boolean',
        ];

        if ($request->filled('password')) {
            $rules['password'] = array_merge(['nullable'], array_slice(self::PASSWORD_RULES, 1));
        }

        $validatedData = $request->validate($rules, [
            'password.regex' => 'La contraseña debe incluir letras y números (ejemplo: abc123).',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        try {
            $usuario->nombre = $validatedData['nombre'];
            $usuario->correo = $validatedData['correo'];
            $usuario->rol = $validatedData['rol'];
            $usuario->activo = $request->boolean('activo');

            if (!empty($validatedData['password'])) {
                $usuario->password = Hash::make($validatedData['password']);
            }

            return $usuario->save()
                ? redirect()->route('usuarios.index')->with('success', 'Registro actualizado exitosamente.')
                : redirect()->back()->withInput()->with('error', 'Error al actualizar el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el registro: ' . $ex->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $usuario = Usuario::find($id);

            if ($usuario == null) {
                return redirect()->route('usuarios.index')->with('error', 'Registro no encontrado.');
            }

            if ($usuario->reservas()->where('estado', 'confirmada')->exists()) {
                return redirect()->back()->with('error', 'No se puede eliminar un usuario con reservas confirmadas.');
            }

            $usuario->activo = false;

            return $usuario->save()
                ? redirect()->route('usuarios.index')->with('success', 'Registro desactivado exitosamente.')
                : redirect()->back()->with('error', 'Error al desactivar el registro.');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al desactivar el registro.');
        }
    }
}
