<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Devuelve todos los registros de la tabla usuarios
     *
     * @return response - JSON - los datos de la tabla
     */
    function obtenerTodos() {
        try {
            // Obtiene todos los usuarios
            $list = Usuario::all();
            return response()->json($list);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al cargar los datos: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function obtenerPorId(int $id) {
        try {
            // Busca usuario por ID
            $data = Usuario::find($id);
            return response()->json($data);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al obtener el usuario: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function eliminarPorId(int $id) {
        try {
            $data = Usuario::find($id);
            if (!$data) {
                $message = ["message" => "Usuario no encontrado", "status" => false];
                return response()->json($message, 404);
            }
            
            // Verificar si tiene reservas activas
            if ($data->reservas()->where('estado', 'confirmada')->exists()) {
                $message = ["message" => "No se puede eliminar el usuario. Tiene reservas activas.", "status" => false];
                return response()->json($message, 422);
            }
            
            // Eliminar usuario
            $data->delete();
            $message = ["message" => "Dato eliminado", "status" => true];
            return response()->json($message);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al eliminar el usuario: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }
    
    function actualizarPorId(Request $request) {
        try {
            // Obtener ID del usuario a actualizar
            $id = $request->id;

            $data = Usuario::find($id);
            if (!$data) {
                $message = ["message" => "Usuario no encontrado", "status" => false];
                return response()->json($message, 404);
            }

            $data->nombre = $request->nombre ?? $data->nombre;
            $data->correo = $request->correo ?? $data->correo;
            $data->rol = $request->rol ?? $data->rol;
            $data->activo = $request->activo ?? $data->activo;

            if ($request->has('password')) {
                $data->password = Hash::make($request->password);
            }

            // Actualizar datos del usuario
            $isOK = $data->save();

            $message = [];

            if ($isOK) {
                $message = ["message" => "Dato actualizado", "status" => true];
            } else {
                $message = ["message" => "Dato no actualizado", "status" => false];
            }

            return response()->json($message);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al actualizar el usuario: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function crear(Request $request) {
        try {
            // Crear nuevo usuario
            $data = new Usuario();
            $data->nombre = $request->nombre;
            $data->correo = $request->correo;
            $data->password = Hash::make($request->password);
            $data->rol = $request->rol;
            $data->activo = $request->activo ?? true;

            // Guardar nuevo usuario
            $isOK = $data->save();

            $message = [];

            if ($isOK) {
                $message = ["message" => "Dato insertado", "status" => true];
            } else {
                $message = ["message" => "Dato no insertado", "status" => false];
            }

            return response()->json($message);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al crear el usuario: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }
}
