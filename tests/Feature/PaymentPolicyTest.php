<?php

namespace Tests\Feature;

use App\Exceptions\PermissionsException;
use App\Models\Organization;
use App\Models\Period;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use App\Models\Kid;
use App\Models\KidParent;

class PaymentPolicyTest extends TestCase
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
        $kid = $organization->kids->random();
        $data = [
            'date_payment' => date('Y-m-d'),
            'comment' => 'test',
            'amount' => '100',
            'kid_id' => $kid->id,
            'period_id' => $period->id
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [Payment::class, $data]);
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
        $kid = $organization->kids->random();
        $data = [
            'date_payment' => date('Y-m-d'),
            'comment' => 'test',
            'amount' => '100',
            'kid_id' => $kid->id,
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [Payment::class, $data]);
    }

    /**
     * success create when user granted store new record
     *
     * @return void
     */
    public function testReturnOkOnCreate()
    {
        $organization = Organization::whereHas('periods')->whereHas('kids')->whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods->random();
        $kid = $organization->kids->random();
        $data = [
            'date_payment' => date('Y-m-d'),
            'comment' => 'test',
            'amount' => '100',
            'kid_id' => $kid->id,
            'period_id' => $period->id
        ];
        $this->actingAs($user)->postJson(uri:'/api/v1/payments', data:$data)->assertStatus(201);
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
            $periods->whereHas('payments');
        })->whereHas('kids')->whereNotNull('admin_id')->get()->random();
        $period = $organization->periods()->whereHas('payments')->get()->random();
        $payment = $period->payments->random();
        $this->expectException(PermissionsException::class);
        $user->can('copy', $payment);
    }

    /**
     * success copy when user granted copy the record
     *
     * @return void
     */
    public function testReturnOkOnCopy()
    {
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('payments');
        })->whereHas('kids')->whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods()->whereHas('payments')->get()->random();
        $payment = $period->payments->random();
        $data = [
            'comment' => 'test',
        ];
        $this->actingAs($user)->postJson(uri:"/api/v1/payments/$payment->id", data:$data)->assertStatus(201);
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
            $periods->whereHas('payments');
        })->whereHas('kids')->whereNotNull('admin_id')->get()->random();
        $period = $organization->periods()->whereHas('payments')->get()->random();
        $payment = $period->payments->random();
        $this->expectException(PermissionsException::class);
        $user->can('update', $payment);
    }

    /**
     * success update when user granted update the record
     *
     * @return void
     */
    public function testReturnOkOnUpdate()
    {
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('payments');
        })->whereHas('kids')->whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods()->whereHas('payments')->get()->random();
        $payment = $period->payments->random();
        $data = [
            'comment' => 'test',
        ];
        $this->actingAs($user)->patchJson(uri:"/api/v1/payments/$payment->id", data:$data)->assertStatus(200);
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
            $periods->whereHas('payments');
        })->whereHas('kids')->whereNotNull('admin_id')->get()->random();
        $period = $organization->periods()->whereHas('payments')->get()->random();
        $payment = $period->payments->random();
        $this->expectException(PermissionsException::class);
        $user->can('delete', $payment);
    }

    /**
     * success remove when user granted delete the record
     *
     * @return void
     */
    public function testReturnOkOnDelete()
    {
        $organization = Organization::whereHas('periods',function($periods){
            $periods->whereHas('payments');
        })->whereHas('kids')->whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $period = $organization->periods()->whereHas('payments')->get()->random();
        $payment = $period->payments->random();
        $data = [
            'comment' => 'test',
        ];
        $this->actingAs($user)->deleteJson(uri:"/api/v1/payments/$payment->id", data:$data)->assertStatus(204);
    }

    /**
     * test deny forceDelete any row for everyone
     *
     * @return void
     */
    public function testReturnFalseOnForceDelete()
    {
        $user = User::get()->random();
        $payment = Payment::get()->random();
        $this->actingAs($user)->assertFalse($user->can('forceDelete', $payment));
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
        $payment = Payment::factory()->create([
            'period_id' => $period->id
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
        $this->actingAs($user)->getJson(uri:"/api/v1/payments/$payment->id")->assertStatus(200);
        return ['userId'=>$user->id, 'paymentId'=>$payment->id, 'kidId'=>$kid->id];
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
        $payment = Payment::find($data['paymentId']);
        $user = User::find($data['userId']);

        $kid->start_study = '2022-04-02';
        $kid->end_study = '2022-12-31';
        $kid->save();

        $this->actingAs($user)->getJson(uri:"/api/v1/payments/$payment->id")->assertStatus(403);
        $this->expectException(PermissionsException::class);
        $user->can('view', $payment);

    }


}
