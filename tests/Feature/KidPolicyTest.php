<?php

namespace Tests\Feature;

use App\Exceptions\PermissionsException;
use App\Models\Kid;
use App\Models\KidParent;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;

class KidPolicyTest extends TestCase
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
            'organization_id' => 1,
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [Kid::class, $data]);
    }

    /**
     * fail on create when missing key field
     *
     * @return void
     */
    public function testExceptionOnMissingOrganizationId()
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
        $user->can('create', [Kid::class, $data]);
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
        $data = [
            'last_name' => 'Test',
            'name' => 'Test',
            'patronymic' => 'Test',
            'organization_id' => $organization->id,
        ];
        $this->actingAs($user)->postJson(uri:'/api/v1/kids', data:$data)->assertStatus(201);
    }

    /**
     * fail on copy when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnCopyPolicy()
    {
        $user = User::factory()->create();
        $kid = Kid::get()->random();
        $this->expectException(PermissionsException::class);
        $user->can('copy', $kid);
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
        $data = [
            'last_name' => 'Test',
            'name' => 'Test',
            'patronymic' => 'Test',
        ];
        $this->actingAs($user)->postJson(uri:"/api/v1/kids/$kid->id", data:$data)->assertStatus(201);
    }

    /**
     * fail on update when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnUpdatePolicy()
    {
        $user = User::factory()->create();
        $kid = Kid::get()->random();
        // without create permissions
        $data = [
            'last_name' => 'Test',
        ];
        $this->expectException(PermissionsException::class);
        $user->can('update', $kid);
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
        $data = [
            'last_name' => 'Test',
        ];
        $this->actingAs($user)->patchJson(uri:"/api/v1/kids/$kid->id", data:$data)->assertStatus(200);
    }

    /**
     * success when parent update the kid profile
     *
     * @return void
     */
    public function testReturnOkOnUpdateByParent()
    {
        $parent = KidParent::whereNotNull('user_id')->get()->random();
        $kid = $parent->kid;
        $user = User::find($parent->user_id);
        $data = [
            'last_name' => 'Test',
        ];
        $this->actingAs($user)->patchJson(uri:"/api/v1/kids/$kid->id", data:$data)->assertStatus(200);
    }

    /**
     * fail on delete when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnDeletePolicy()
    {
        $user = User::factory()->create();
        $kid = Kid::get()->random();
        $this->expectException(PermissionsException::class);
        $user->can('delete', $kid);
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
        $this->actingAs($user)->deleteJson(uri:"/api/v1/kids/$kid->id")->assertStatus(204);
    }

    /**
     * test deny forceDelete any row for everyone
     *
     * @return void
     */
    public function testReturnFalseOnForceDelete()
    {
        $user = User::factory()->create();
        $parent = Kid::get()->random();
        $this->actingAs($user)->assertFalse($user->can('forceDelete', $parent));
    }

}
