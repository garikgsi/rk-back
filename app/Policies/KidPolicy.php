<?php

namespace App\Policies;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Exceptions\PermissionsException;
use App\Models\Organization;

class KidPolicy
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
     * @param  \App\Models\Kid  $kid
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Kid $kid)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  array $requestData
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, array $requestData)
    {
        $res = false;
        if (isset($requestData['organization_id'])) {
            $organization = Organization::find($requestData['organization_id']);
            if ($organization) {
                $res = $organization->isAdmin();
            }
        }
        if (!$res) {
            throw new PermissionsException('Создавать учеников могут только администраторы организации', 403);
        }
        return $res;
    }

    /**
     * Determine whether the user can copy the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Period  $period
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function copy(User $user, Kid $kid)
    {
        if ($this->isAdmin(kid:$kid)) {
            return true;
        } else {
            throw new PermissionsException('Чтобы скопировать этого ученика нужно администраторские права', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Kid  $kid
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Kid $kid)
    {
        // dd($kid->parents->toArray(), $user->id);
        if ($this->isAdmin(kid:$kid) || in_array($user->id, $kid->parents->pluck('user_id')->values()->all())) {
            return true;
        } else {
            throw new PermissionsException('Изменить профиль ученика может только администратор организации или его родители', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Kid  $kid
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Kid $kid)
    {
        if ($this->isAdmin(kid:$kid)) {
            return true;
        } else {
            throw new PermissionsException('Удалить профиль ученика может только администратор организации', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Kid  $kid
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Kid $kid)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Kid  $kid
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Kid $kid)
    {
        return false;
    }

    /**
     * return if user is admin of organization belong period
     *
     * @param  User $user
     * @param  Organization $organization
     * @return bool
     */
    protected function isAdmin(Kid $kid):bool
    {
        return $kid->isAdmin();
    }

}
