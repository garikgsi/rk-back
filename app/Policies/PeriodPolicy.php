<?php

namespace App\Policies;

use App\Models\Period;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Exceptions\PermissionsException;
use App\Models\Organization;

class PeriodPolicy
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
     * @param  \App\Models\Period  $period
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Period $period)
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
            throw new PermissionsException('Создавать периоды могут только администраторы организации', 403);
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
    public function copy(User $user, Period $period)
    {
        if ($this->isAdmin(period:$period)) {
            return true;
        } else {
            throw new PermissionsException('Чтобы копировать этот период нужно быть его администратором', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Period  $period
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Period $period)
    {
        if ($this->isAdmin(period:$period)) {
            return true;
        } else {
            throw new PermissionsException('Изменять периоды могут только администраторы', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Period  $period
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Period $period)
    {
        if ($this->isAdmin(period:$period)) {
            return true;
        } else {
            throw new PermissionsException('Удалять периоды могут только администраторы', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Period  $period
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Period $period)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Period  $period
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Period $period)
    {
        return false;
    }

    /**
     * return if user is admin of organization belongs period
     *
     * @param  User $user
     * @param  Organization $organization
     * @return bool
     */
    protected function isAdmin(Period $period):bool
    {
        return $period->isAdmin();
    }

}
