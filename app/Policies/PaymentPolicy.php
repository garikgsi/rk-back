<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Exceptions\PermissionsException;
use App\Models\Period;

class PaymentPolicy
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
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Payment $payment)
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
        if (isset($requestData['period_id'])) {
            $period = Period::find($requestData['period_id']);
            if ($period) {
                $res = $period->isAdmin();
            }
        }
        if (!$res) {
            throw new PermissionsException('Создавать поступления могут только администраторы организации', 403);
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
    public function copy(User $user, Payment $payment)
    {
        if ($this->isAdmin(payment:$payment)) {
            return true;
        } else {
            throw new PermissionsException('Чтобы скопировать поступление нужны администраторские права', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Payment $payment)
    {
        if ($this->isAdmin(payment:$payment)) {
            return true;
        } else {
            throw new PermissionsException('Обновить поступление может только администратор организации', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Payment $payment)
    {
        if ($this->isAdmin(payment:$payment)) {
            return true;
        } else {
            throw new PermissionsException('Удалить поступление может только администратор организации', 403);
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Payment $payment)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Payment $payment)
    {
        return false;
    }

    /**
     * return if user is admin of organization belongs period this plan
     *
     * @param  User $user
     * @param  Organization $organization
     * @return bool
     */
    protected function isAdmin(Payment $payment):bool
    {
        return $payment->period->isAdmin();
    }
}
