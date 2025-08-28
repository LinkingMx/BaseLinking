<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ApprovalState;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApprovalStatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_approval::state');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApprovalState $approvalState): bool
    {
        return $user->can('view_approval::state');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_approval::state');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApprovalState $approvalState): bool
    {
        return $user->can('update_approval::state');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApprovalState $approvalState): bool
    {
        return $user->can('delete_approval::state');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_approval::state');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ApprovalState $approvalState): bool
    {
        return $user->can('force_delete_approval::state');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_approval::state');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ApprovalState $approvalState): bool
    {
        return $user->can('restore_approval::state');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_approval::state');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, ApprovalState $approvalState): bool
    {
        return $user->can('replicate_approval::state');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_approval::state');
    }
}
