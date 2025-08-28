<?php

namespace App\Policies;

use App\Models\Documentation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentationPolicy
{
    /**
     * Determinar si el usuario puede ver cualquier documento
     */
    public function viewAny(User $user): bool
    {
        // Super admin ve todo
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Otros usuarios pueden ver la lista (filtrada por Resource)
        return $user->hasAnyRole(['User', 'IT_Boss']);
    }

    /**
     * Determinar si el usuario puede ver un documento específico
     */
    public function view(User $user, Documentation $documentation): bool
    {
        // Super admin ve todo
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Creador ve sus propios documentos
        if ($documentation->created_by === $user->id) {
            return true;
        }
        
        // IT_Boss puede ver documentos donde puede/pudo actuar
        if ($user->hasRole('IT_Boss')) {
            // Documentos pendientes de su aprobación
            if ($documentation->status === 'pending_supervisor') {
                return true;
            }
            
            // Documentos que ya aprobó/rechazó
            if ($documentation->approved_by === $user->id) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determinar si el usuario puede crear documentos
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['User', 'IT_Boss', 'super_admin']);
    }

    /**
     * Determinar si el usuario puede actualizar un documento
     */
    public function update(User $user, Documentation $documentation): bool
    {
        // Super admin puede todo
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // 🚨 RESTRICCIÓN: Documentos aprobados son INMUTABLES
        if ($documentation->status === 'approved') {
            return Response::deny('Los documentos aprobados no se pueden modificar.');
        }
        
        // 🚨 RESTRICCIÓN: Documentos en revisión son INMUTABLES para el creador
        if ($documentation->status === 'pending_supervisor') {
            return Response::deny('Los documentos en revisión no se pueden modificar hasta que sean aprobados o rechazados.');
        }
        
        // 🚨 RESTRICCIÓN: Documentos rechazados solo pueden ser editados por el creador
        if ($documentation->status === 'rejected') {
            return $documentation->created_by === $user->id;
        }
        
        // Solo el creador puede editar sus documentos en borrador
        return $documentation->status === 'draft' && $documentation->created_by === $user->id;
    }

    /**
     * Determinar si el usuario puede eliminar un documento
     */
    public function delete(User $user, Documentation $documentation): bool
    {
        // Super admin puede todo
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // 🚨 RESTRICCIÓN: Solo documentos en borrador se pueden eliminar
        if ($documentation->status !== 'draft') {
            return Response::deny('Solo se pueden eliminar documentos en estado borrador.');
        }
        
        // Solo el creador puede eliminar
        return $documentation->created_by === $user->id;
    }

    /**
     * Determinar si el usuario puede aprobar un documento
     */
    public function approve(User $user, Documentation $documentation): bool
    {
        return $user->hasRole('IT_Boss') && 
               $documentation->status === 'pending_supervisor';
    }

    /**
     * Determinar si el usuario puede rechazar un documento
     */
    public function reject(User $user, Documentation $documentation): bool
    {
        return $user->hasRole('IT_Boss') && 
               $documentation->status === 'pending_supervisor';
    }
}
