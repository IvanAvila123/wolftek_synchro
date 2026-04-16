<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CashShift;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashShiftPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CashShift');
    }

    public function view(AuthUser $authUser, CashShift $cashShift): bool
    {
        return $authUser->can('View:CashShift');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CashShift');
    }

    public function update(AuthUser $authUser, CashShift $cashShift): bool
    {
        return $authUser->can('Update:CashShift');
    }

    public function delete(AuthUser $authUser, CashShift $cashShift): bool
    {
        return $authUser->can('Delete:CashShift');
    }

    public function restore(AuthUser $authUser, CashShift $cashShift): bool
    {
        return $authUser->can('Restore:CashShift');
    }

    public function forceDelete(AuthUser $authUser, CashShift $cashShift): bool
    {
        return $authUser->can('ForceDelete:CashShift');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CashShift');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CashShift');
    }

    public function replicate(AuthUser $authUser, CashShift $cashShift): bool
    {
        return $authUser->can('Replicate:CashShift');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CashShift');
    }

}