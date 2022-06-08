<?php

namespace App\Policies;

use App\Exceptions\PermissionsException;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Organization $organization)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, array $requestData)
    {
        if ($user->id != $requestData['admin_id']) {
            throw new PermissionsException('Создавать организацию можно только в своем аккаунте', 403);
        } else {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Organization $organization)
    {
        if ($this->isAdmin(user:$user, organization:$organization)) {
            return true;
        } else {
            throw new PermissionsException('Чтобы изменять учреждения нужно быть его администратором', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can copy the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function copy(User $user, Organization $organization)
    {
        if ($this->isAdmin(user:$user, organization:$organization)) {
            return true;
        } else {
            throw new PermissionsException('Чтобы копировать это учреждение нужно быть его администратором', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Organization $organization)
    {
        if ($organization->admin_id == $user->id) {
            return true;
        } else {
            throw new PermissionsException('Удалить организацию может только создатель', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Organization $organization)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Organization $organization)
    {
        return false;
    }

    /**
     * return if user is admin of organization
     *
     * @param  User $user
     * @param  Organization $organization
     * @return bool
     */
    protected function isAdmin(User $user, Organization $organization):bool
    {
        return $organization->admin_id == $user->id || $organization->parents->where('user_id',$user->id)->where('is_admin',true)->count()>0;
    }
}
