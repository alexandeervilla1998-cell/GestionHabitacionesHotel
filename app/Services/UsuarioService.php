<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class UsuarioService
{
    /**
     * Get users with filters and pagination
     */
    public function getUsers(array $filters = []): LengthAwarePaginator
    {
        $query = Usuario::query();

        // Apply filters
        if (isset($filters['rol'])) {
            $query->where('rol', $filters['rol']);
        }

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%");
            });
        }

        return $query->withCount('reservas')->paginate(10);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): Usuario
    {
        $data['password'] = Hash::make($data['password']);
        $data['activo'] = $data['activo'] ?? true;
        
        return Usuario::create($data);
    }

    /**
     * Update user
     */
    public function updateUser(Usuario $usuario, array $data): Usuario
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $usuario->update($data);
        return $usuario;
    }

    /**
     * Delete user with validation
     */
    public function deleteUser(Usuario $usuario): bool
    {
        // Check if user has active reservations
        if ($usuario->reservas()->where('estado', 'confirmada')->exists()) {
            throw new \Exception('El usuario tiene reservas confirmadas');
        }

        return $usuario->delete();
    }

    /**
     * Find user by ID with relationships
     */
    public function findUserById(int $id): ?Usuario
    {
        return Usuario::withCount('reservas')->find($id);
    }

    /**
     * Check if user email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = Usuario::where('correo', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
}
