<?php

namespace App\Services\Repositories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TableRepositoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrganizationRepository extends TableRepositoryService {

    /**
     * current user
     */
    protected User $user;
    /**
     * current request
     */
    protected Request $request;

    // protected Builder $repository;

    /**
     * use personal constructor with specified fill repository data
     *
     * @return void
     */
    public function __construct()
    {
        $this->user = Auth::user();
        $this->use('organizations');
        $this->fillRepository($this->user
            ?
            Organization::where(function($query) {
                $query->where('admin_id',$this->user->id)
                    ->orWhereHas('parents', function($parents){
                        $parents->where('user_id',$this->user->id);
                    });
                })
            :
            Organization::where('id',null));
    }

    // /**
    //  * return only organizations where user is admin
    //  *
    //  * @return void
    //  */
    // public function adminOrganizations($onlyIds = true)
    // {
    //     $adminOrganizations = Organization::where(function($query) {
    //         $query->where('admin_id',$this->user->id)
    //             ->orWhereHas('parents', function($parents){
    //                 $parents->where('user_id',$this->user->id)->where('is_admin',true);
    //             });
    //         }
    //     )->get();
    //     return $onlyIds ? $adminOrganizations->pluck('id')->values()->all(): $adminOrganizations;
    // }

}

