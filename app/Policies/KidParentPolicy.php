<?php

namespace App\Policies;

use App\Models\KidParent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Exceptions\PermissionsException;
use App\Models\Kid;

class KidParentPolicy
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
     * @param  \App\Models\KidParent  $kidParent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, KidParent $kidParent)
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
        if (isset($requestData['kid_id'])) {
            $kid = Kid::find($requestData['kid_id']);
            if ($kid) {
                $res = $kid->isAdmin();
            }
        }
        if (!$res) {
            throw new PermissionsException('Создавать профили родителей могут только администраторы организации', 403);
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
    public function copy(User $user, KidParent $kidParent)
    {
        if ($this->isAdmin(kidParent:$kidParent)) {
            return true;
        } else {
            throw new PermissionsException('Чтобы скопировать профиль родителя нужны администраторские права', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\KidParent  $kidParent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, KidParent $kidParent)
    {
        if ($user->id == $kidParent->user_id || $this->isAdmin(kidParent:$kidParent)) {
            return true;
        } else {
            throw new PermissionsException('Обновить профиль может только администратор или родитель', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\KidParent  $kidParent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, KidParent $kidParent)
    {
        if ($this->isAdmin(kidParent:$kidParent)) {
            return true;
        } else {
            throw new PermissionsException('Удалить профиль может только администратор', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\KidParent  $kidParent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, KidParent $kidParent)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\KidParent  $kidParent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, KidParent $kidParent)
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
    protected function isAdmin(KidParent $kidParent):bool
    {
        return $kidParent->kid->isAdmin();
    }

}
