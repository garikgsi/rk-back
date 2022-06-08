<?php

namespace Tests\Feature;

use App\Exceptions\PermissionsException;
use App\Models\Organization;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;

class PeriodPolicyTest extends TestCase
{
    /**
     * fail on create when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnCreatePolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::get()->random();
        $data = [
            'name' => 'test',
            'start_date' => date('2021-d-m'),
            'end_date' => date('2022-d-m'),
            'organization_id' => $organization->id,
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [Period::class, $data]);
    }

    /**
     * fail on create when missing key field
     *
     * @return void
     */
    public function testExceptionOnMissingOrganizationId()
    {
        $user = User::factory()->create();
        $data = [
            'name' => 'test',
            'start_date' => date('2021-d-m'),
            'end_date' => date('2022-d-m'),
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [Period::class, $data]);
    }

    /**
     * success create when user granted store new record
     *
     * @return void
     */
    public function testReturnOkOnCreate()
    {
        $organization = Organization::whereHas('periods')->whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods->random();
        $data = [
            'name' => 'test',
            'start_date' => date('2021-d-m'),
            'end_date' => date('2022-d-m'),
            'organization_id' => $organization->id,
        ];
        $this->actingAs($user)->postJson(uri:'/api/v1/periods', data:$data)->assertStatus(201);
    }

    /**
     * fail on copy when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnCopyPolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::whereHas('periods')->get()->random();
        $period = $organization->periods->random();
        $this->expectException(PermissionsException::class);
        $user->can('copy', $period);
    }

    /**
     * success copy when user granted copy the record
     *
     * @return void
     */
    public function testReturnOkOnCopy()
    {
        $organization = Organization::whereHas('periods')->whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods->random();
        $data = [
            'name' => 'test',
            'start_date' => date('2021-d-m'),
            'end_date' => date('2022-d-m'),
            'organization_id' => $organization->id,
        ];
        $this->actingAs($user)->postJson(uri:"/api/v1/periods/$period->id", data:$data)->assertStatus(201);
    }

    /**
     * fail on update when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnUpdatePolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::whereHas('periods')->get()->random();
        $period = $organization->periods->random();
        $this->expectException(PermissionsException::class);
        $user->can('update', $period);
    }

    /**
     * success update when user granted update the record
     *
     * @return void
     */
    public function testReturnOkOnUpdate()
    {
        $organization = Organization::whereNotNull('admin_id')->whereHas('periods')->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods->random();
        $data = [
            'name' => 'test',
        ];
        $this->actingAs($user)->patchJson(uri:"/api/v1/periods/$period->id", data:$data)->assertStatus(200);
    }

    /**
     * fail on delete when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnDeletePolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::whereHas('periods')->get()->random();
        $period = $organization->periods->random();
        $this->expectException(PermissionsException::class);
        $user->can('delete', $period);
    }

    /**
     * success remove when user granted delete the record
     *
     * @return void
     */
    public function testReturnOkOnDelete()
    {
        $organization = Organization::whereHas('periods')->whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods->random();
        $data = [
            'name' => 'test',
        ];
        $this->actingAs($user)->deleteJson(uri:"/api/v1/periods/$period->id", data:$data)->assertStatus(204);
    }

    /**
     * test deny forceDelete any row for everyone
     *
     * @return void
     */
    public function testReturnFalseOnForceDelete()
    {
        $user = User::factory()->create();
        $period = Period::get()->random();
        $this->actingAs($user)->assertFalse($user->can('forceDelete', $period));
    }

}
