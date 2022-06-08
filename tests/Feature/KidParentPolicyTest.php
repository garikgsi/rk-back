<?php

namespace Tests\Feature;

use App\Exceptions\PermissionsException;
use App\Models\KidParent;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;

class KidParentPolicyTest extends TestCase
{
    /**
     * fail on create when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnCreatePolicy()
    {
        $user = User::factory()->create();
        // without create permissions
        $data = [
            'last_name' => 'Test',
            'name' => 'Test',
            'patronymic' => 'Test',
            'kid_id' => 1,
            'is_admin' => false
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [KidParent::class, $data]);
    }

    /**
     * fail on create when missing key field
     *
     * @return void
     */
    public function testExceptionOnMissingKidId()
    {
        $user = User::factory()->create();
        // without kid_id
        $data = [
            'last_name' => 'Test',
            'name' => 'Test',
            'patronymic' => 'Test',
            'is_admin' => false
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [KidParent::class, $data]);
    }

    /**
     * success create when user granted store new record
     *
     * @return void
     */
    public function testReturnOkOnCreate()
    {
        $user = User::get()->random();
        $organization = Organization::get()->random();
        $organization->admin_id = $user->id;
        $organization->save();
        $kid = $organization->kids->random();
        $data = [
            'last_name' => 'Test',
            'name' => 'Test',
            'patronymic' => 'Test',
            'kid_id' => $kid->id,
            'is_admin' => false
        ];
        $this->actingAs($user)->postJson(uri:'/api/v1/kid_parents', data:$data)->assertStatus(201);
    }

    /**
     * fail on copy when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnCopyPolicy()
    {
        $user = User::factory()->create();
        $parent = KidParent::get()->random();
        // without create permissions
        $data = [
            'last_name' => 'Test',
            'name' => 'Test',
            'patronymic' => 'Test',
        ];
        $this->expectException(PermissionsException::class);
        $user->can('copy', $parent);
    }

    /**
     * success copy when user granted copy the record
     *
     * @return void
     */
    public function testReturnOkOnCopy()
    {
        $user = User::get()->random();
        $organization = Organization::get()->random();
        $organization->admin_id = $user->id;
        $organization->save();
        $kid = $organization->kids->random();
        $parent = $kid->parents->random();
        $data = [
            'last_name' => 'Test',
            'name' => 'Test',
            'patronymic' => 'Test',
        ];
        $this->actingAs($user)->postJson(uri:"/api/v1/kid_parents/$parent->id", data:$data)->assertStatus(201);
    }

    /**
     * fail on update when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnUpdatePolicy()
    {
        $user = User::factory()->create();
        $parent = KidParent::get()->random();
        // without create permissions
        $data = [
            'last_name' => 'Test',
        ];
        $this->expectException(PermissionsException::class);
        $user->can('update', $parent);
    }

    /**
     * success update when user granted update the record
     *
     * @return void
     */
    public function testReturnOkOnUpdate()
    {
        $user = User::get()->random();
        $organization = Organization::get()->random();
        $organization->admin_id = $user->id;
        $organization->save();
        $kid = $organization->kids->random();
        $parent = $kid->parents->random();
        $data = [
            'last_name' => 'Test',
        ];
        $this->actingAs($user)->patchJson(uri:"/api/v1/kid_parents/$parent->id", data:$data)->assertStatus(200);
    }

    /**
     * fail on delete when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnDeletePolicy()
    {
        $user = User::factory()->create();
        $parent = KidParent::get()->random();
        $this->expectException(PermissionsException::class);
        $user->can('delete', $parent);
    }

    /**
     * success remove when user granted delete the record
     *
     * @return void
     */
    public function testReturnOkOnDelete()
    {
        $user = User::get()->random();
        $organization = Organization::get()->random();
        $organization->admin_id = $user->id;
        $organization->save();
        $kid = $organization->kids->random();
        $parent = $kid->parents->random();
        $this->actingAs($user)->deleteJson(uri:"/api/v1/kid_parents/$parent->id")->assertStatus(204);
    }

    /**
     * test deny forceDelete any row for everyone
     *
     * @return void
     */
    public function testReturnFalseOnForceDelete()
    {
        $user = User::factory()->create();
        $parent = KidParent::get()->random();
        $this->actingAs($user)->assertFalse($user->can('forceDelete', $parent));
    }

}
