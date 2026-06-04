<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    /**
     * Devuelve todos los registros
     * de la tabla servicios
     *
     * @return response - JSON - los datos de la tabla
     */
    function obtenerTodos() {
        try {
            // all() Equivale a:
            // SELECT * FROM servicios
            $list = Servicio::all();
            return response()->json($list);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al cargar los datos: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function obtenerPorId(int $id) {
        try {
            // SELECT * FROM servicios WHERE id = ?
            $data = Servicio::find($id);
            return response()->json($data);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al obtener el servicio: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function eliminarPorId(int $id) {
        try {
            $data = Servicio::find($id);
            if (!$data) {
                $message = ["message" => "Servicio no encontrado", "status" => false];
                return response()->json($message, 404);
            }
            
            // Verificar si tiene reservas asociadas
            if ($data->reservaServicios()->exists()) {
                $message = ["message" => "No se puede eliminar el servicio. Tiene reservas asociadas.", "status" => false];
                return response()->json($message, 422);
            }
            
            // Eliminar servicio
            $data->delete();
            $message = ["message" => "Dato eliminado", "status" => true];
            return response()->json($message);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al eliminar el servicio: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }
    
    function actualizarPorId(Request $request) {
        try {
            // Obtener ID del servicio a actualizar
            $id = $request->id;

            $data = Servicio::find($id);
            if (!$data) {
                $message = ["message" => "Servicio no encontrado", "status" => false];
                return response()->json($message, 404);
            }

            $data->nombre = $request->nombre ?? $data->nombre;
            $data->precio = $request->precio ?? $data->precio;
            $data->activo = $request->activo ?? $data->activo;

            // Actualizar datos del servicio
            $isOK = $data->save();

            $message = [];

            if ($isOK) {
                $message = ["message" => "Dato actualizado", "status" => true];
            } else {
                $message = ["message" => "Dato no actualizado", "status" => false];
            }

            return response()->json($message);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al actualizar el servicio: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function crear(Request $request) {
        try {
            // Crear nuevo servicio
            $data = new Servicio();
            $data->nombre = $request->nombre;
            $data->precio = $request->precio;
            $data->activo = $request->activo ?? true;

            // Guardar nuevo servicio
            $isOK = $data->save();

            $message = [];

            if ($isOK) {
                $message = ["message" => "Dato insertado", "status" => true];
            } else {
                $message = ["message" => "Dato no insertado", "status" => false];
            }

            return response()->json($message);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al crear el servicio: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function obtenerActivos() {
        try {
            // SELECT * FROM servicios WHERE activo = 1
            $list = Servicio::where('activo', true)->get();
            return response()->json($list);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al cargar los servicios activos: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }
}
