<?php

namespace Tests\Feature;

use App\Exceptions\PermissionsException;
use App\Models\Organization;
use App\Models\Period;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;

class PlanPolicyTest extends TestCase
{
    /**
     * fail on create when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnCreatePolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::whereHas('periods')->whereHas('kids')->get()->random();
        $period = $organization->periods->random();
        $data = [
            'title' => 'test',
            'price' => 100,
            'quantity' => 10,
            'amount' => 1000,
            'period_id' => $period->id
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [Plan::class, $data]);
    }

    /**
     * fail on create when missing key field
     *
     * @return void
     */
    public function testExceptionOnMissingPeriodId()
    {
        $user = User::factory()->create();
        $organization = Organization::whereHas('periods')->whereHas('kids')->get()->random();
        $data = [
            'title' => 'test',
            'price' => 100,
            'quantity' => 10,
            'amount' => 1000,
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [Plan::class, $data]);
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
        $period = $organization->periods()->whereHas('plans')->get()->random();
        $data = [
            'title' => 'test',
            'price' => 100,
            'quantity' => 10,
            'amount' => 1000,
            'period_id' => $period->id
        ];
        $this->actingAs($user)->postJson(uri:"/api/v1/plans", data:$data)->assertStatus(201);
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
        $period = $organization->periods()->whereHas('plans')->get()->random();
        $plan = $period->plans->random();
        $data = [
            'title' => 'test',
        ];
        $this->expectException(PermissionsException::class);
        $user->can('copy', [$plan, $data]);
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
        $period = $organization->periods()->whereHas('plans')->get()->random();
        $plan = $period->plans->random();
        $data = [
            'title' => 'test',
        ];
        $this->actingAs($user)->postJson(uri:"/api/v1/plans/$plan->id", data:$data)->assertStatus(201);
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
        $period = $organization->periods()->whereHas('plans')->get()->random();
        $plan = $period->plans->random();
        $data = [
            'title' => 'test',
        ];
        $this->expectException(PermissionsException::class);
        $user->can('update', [$plan, $data]);
    }

    /**
     * success update when user granted update the record
     *
     * @return void
     */
    public function testReturnOkOnUpdate()
    {
        $organization = Organization::whereHas('periods')->whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods()->whereHas('plans')->get()->random();
        $plan = $period->plans->random();
        $data = [
            'title' => 'test',
        ];
        $this->actingAs($user)->patchJson(uri:"/api/v1/plans/$plan->id", data:$data)->assertStatus(200);
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
        $period = $organization->periods()->whereHas('plans')->get()->random();
        $plan = $period->plans->random();
        $this->expectException(PermissionsException::class);
        $user->can('delete', $plan);
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
        $period = $organization->periods()->whereHas('plans')->get()->random();
        $plan = $period->plans->random();
        $this->actingAs($user)->deleteJson(uri:"/api/v1/plans/$plan->id")->assertStatus(204);
    }

    /**
     * test deny forceDelete any row for everyone
     *
     * @return void
     */
    public function testReturnFalseOnForceDelete()
    {
        $user = User::factory()->create();
        $plan = Plan::get()->random();
        $this->actingAs($user)->assertFalse($user->can('forceDelete', $plan));
    }

}
