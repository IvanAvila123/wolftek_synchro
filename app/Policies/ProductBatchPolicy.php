<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProductBatch;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductBatchPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductBatch');
    }

    public function view(AuthUser $authUser, ProductBatch $productBatch): bool
    {
        return $authUser->can('View:ProductBatch');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductBatch');
    }

    public function update(AuthUser $authUser, ProductBatch $productBatch): bool
    {
        return $authUser->can('Update:ProductBatch');
    }

    public function delete(AuthUser $authUser, ProductBatch $productBatch): bool
    {
        return $authUser->can('Delete:ProductBatch');
    }

    public function restore(AuthUser $authUser, ProductBatch $productBatch): bool
    {
        return $authUser->can('Restore:ProductBatch');
    }

    public function forceDelete(AuthUser $authUser, ProductBatch $productBatch): bool
    {
        return $authUser->can('ForceDelete:ProductBatch');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductBatch');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductBatch');
    }

    public function replicate(AuthUser $authUser, ProductBatch $productBatch): bool
    {
        return $authUser->can('Replicate:ProductBatch');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductBatch');
    }

}