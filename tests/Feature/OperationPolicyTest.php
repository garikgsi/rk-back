<?php

namespace Tests\Feature;

use App\Exceptions\PermissionsException;
use App\Models\Organization;
use App\Models\Period;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use App\Models\Kid;
use App\Models\KidParent;
use App\Models\Plan;

class OperationPolicyTest extends TestCase
{
    /**
     * fail on create when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnCreatePolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('plans')->whereHas('operations');
        })->get()->random();
        $period = $organization->periods()->whereHas('plans')->get()->random();
        $plan = $period->plans->random();
        $data = [
            'date_operation' => date('Y-m-d'),
            'comment' => 'test',
            'price' => 100,
            'quantity' => 10,
            'amount' => 1000,
            'plan_id' => $plan->id,
            'period_id' => $period->id
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [Operation::class, $data]);
    }

    /**
     * fail on create when missing key field
     *
     * @return void
     */
    public function testExceptionOnMissingPeriodId()
    {
        $user = User::factory()->create();
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('plans')->whereHas('operations');
        })->get()->random();
        $period = $organization->periods()->whereHas('plans')->get()->random();
        $plan = $period->plans->random();
        $data = [
            'date_operation' => date('Y-m-d'),
            'comment' => 'test',
            'price' => 100,
            'quantity' => 10,
            'amount' => 1000,
            'plan_id' => $plan->id,
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [Operation::class, $data]);
    }

    /**
     * success create when user granted store new record
     *
     * @return void
     */
    public function testReturnOkOnCreate()
    {
        $organization = Organization::whereNotNull('admin_id')->whereHas('periods',function($periods){
            $periods->whereHas('plans')->whereHas('operations');
        })->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods()->whereHas('plans')->get()->random();
        $plan = $period->plans->random();
        $data = [
            'date_operation' => date('Y-m-d'),
            'comment' => 'test',
            'price' => 100,
            'quantity' => 10,
            'amount' => 1000,
            'plan_id' => $plan->id,
            'period_id' => $period->id
        ];
        $this->actingAs($user)->postJson(uri:"/api/v1/operations", data:$data)->assertStatus(201);
    }

    /**
     * fail on copy when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnCopyPolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('plans')->whereHas('operations');
        })->get()->random();
        $period = $organization->periods()->whereHas('plans')->whereHas('operations')->get()->random();
        $plan = $period->plans->random();
        $operation = $period->operations->random();
        $data = [
            'comment' => 'test',
        ];
        $this->expectException(PermissionsException::class);
        $user->can('copy', [$operation, $data]);
    }

    /**
     * success copy when user granted copy the record
     *
     * @return void
     */
    public function testReturnOkOnCopy()
    {
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('plans')->whereHas('operations');
        })->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods()->whereHas('operations')->get()->random();
        $operation = $period->operations->random();
        $data = [
            'comment' => 'test',
        ];
        $this->actingAs($user)->postJson(uri:"/api/v1/operations/$operation->id", data:$data)->assertStatus(201);
    }

    /**
     * fail on update when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnUpdatePolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('plans')->whereHas('operations');
        })->get()->random();
        $period = $organization->periods()->whereHas('plans')->whereHas('operations')->get()->random();
        $plan = $period->plans->random();
        $operation = $period->operations->random();
        $data = [
            'comment' => 'test',
        ];
        $this->expectException(PermissionsException::class);
        $user->can('update', [$operation, $data]);
    }

    /**
     * success update when user granted update the record
     *
     * @return void
     */
    public function testReturnOkOnUpdate()
    {
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('plans');
        })->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods()->whereHas('operations')->get()->random();
        $operation = $period->operations->random();
        $data = [
            'comment' => 'test',
        ];
        $this->actingAs($user)->patchJson(uri:"/api/v1/operations/$operation->id", data:$data)->assertStatus(200);
    }

    /**
     * fail on delete when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnDeletePolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('plans');
        })->get()->random();
        $period = $organization->periods()->whereHas('plans')->whereHas('operations')->get()->random();
        $plan = $period->plans->random();
        $operation = $period->operations->random();
        $this->expectException(PermissionsException::class);
        $user->can('delete', $operation);
    }

    /**
     * success remove when user granted delete the record
     *
     * @return void
     */
    public function testReturnOkOnDelete()
    {
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('plans');
        })->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods()->whereHas('operations')->get()->random();
        $operation = $period->operations->random();
        $this->actingAs($user)->deleteJson(uri:"/api/v1/operations/$operation->id")->assertStatus(204);
    }

    /**
     * test deny forceDelete any row for everyone
     *
     * @return void
     */
    public function testReturnFalseOnForceDelete()
    {
        $user = User::get()->random();
        $operation = Operation::get()->random();
        $this->actingAs($user)->assertFalse($user->can('forceDelete', $operation));
    }

            /**
     * testDenyAccessToParentWithoutStudyPeriod
     *
     * @return array
     */
    public function testAllowAccessToParentWithinStudyPeriod():array
    {
        $user = User::factory()->create([
            'name' => 'test'
        ]);
        $organization = Organization::get()->random();
        $period = Period::factory()->create([
            'start_date' => '2022-01-01',
            'end_date' => '2022-04-01',
            'organization_id' => $organization->id,
        ]);
        $plan = Plan::factory()->create([
            'period_id' => $period->id
        ]);
        $operation = Operation::factory()->create([
            'plan_id' => $plan->id
        ]);
        $kid = Kid::factory()->create([
            'start_study' => '2022-01-01',
            'end_study' => '2022-03-01',
            'organization_id' => $organization->id
        ]);
        $parent = KidParent::factory()->create([
            'kid_id' => $kid->id,
            'user_id' => $user->id
        ]);
        $parent->user_id = $user->id;
        $parent->save();
        $this->assertTrue($user->can('view', $period));
        $this->actingAs($user)->getJson(uri:"/api/v1/operations/$operation->id")->assertStatus(200);
        return ['userId'=>$user->id, 'operationId'=>$operation->id, 'kidId'=>$kid->id];
    }

    /**
     * testDenyAccessFromController
     *
     * @depends testAllowAccessToParentWithinStudyPeriod
     * @param  mixed $data
     * @return void
     */
    public function testDenyAccessToParentWithoutStudyPeriod($data)
    {
        $kid = Kid::find($data['kidId']);
        $operation = Operation::find($data['operationId']);
        $user = User::find($data['userId']);

        $kid->start_study = '2022-04-02';
        $kid->end_study = '2022-12-31';
        $kid->save();

        $this->expectException(PermissionsException::class);
        $user->can('view', $operation);

    }
}
