<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Adjustment;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdjustmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Adjustment');
    }

    public function view(AuthUser $authUser, Adjustment $adjustment): bool
    {
        return $authUser->can('View:Adjustment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Adjustment');
    }

    public function update(AuthUser $authUser, Adjustment $adjustment): bool
    {
        return $authUser->can('Update:Adjustment');
    }

    public function delete(AuthUser $authUser, Adjustment $adjustment): bool
    {
        return $authUser->can('Delete:Adjustment');
    }

    public function restore(AuthUser $authUser, Adjustment $adjustment): bool
    {
        return $authUser->can('Restore:Adjustment');
    }

    public function forceDelete(AuthUser $authUser, Adjustment $adjustment): bool
    {
        return $authUser->can('ForceDelete:Adjustment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Adjustment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Adjustment');
    }

    public function replicate(AuthUser $authUser, Adjustment $adjustment): bool
    {
        return $authUser->can('Replicate:Adjustment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Adjustment');
    }

}