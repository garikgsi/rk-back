<?php

namespace Tests\Feature;

use App\Exceptions\PermissionsException;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Str;

class OrganizationPolicyTest extends TestCase
{
    /**
     * fail when user try to create organization in different account
     *
     * @return void
     */
    public function testExceptionOnCreatePolicy()
    {
        $user = User::factory()->create();
        $data = [
            'title' => 'test',
            'slug' => Str::random(18),
            'admin_id' => 1
        ];
        $this->expectException(PermissionsException::class);
        $user->can('create', [Organization::class, $data]);
    }

    /**
     * success create when user granted store new record
     *
     * @return void
     */
    public function testReturnOkOnCreate()
    {
        $user = User::get()->random();
        $data = [
            'title' => 'test',
            'slug' => Str::random(18),
            'admin_id' => $user->id
        ];
        $this->actingAs($user)->postJson(uri:'/api/v1/organizations', data:$data)->assertStatus(201);
    }

    /**
     * fail on copy when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnCopyPolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::get()->random();
        // without create permissions
        $data = [
            'title' => 'Test',
        ];
        $this->expectException(PermissionsException::class);
        $user->can('copy', $organization);
    }

    /**
     * success copy when user granted copy the record
     *
     * @return void
     */
    public function testReturnOkOnCopy()
    {
        $organization = Organization::whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $data = [
            'slug' => Str::random(),
        ];
        $this->actingAs($user)->postJson(uri:"/api/v1/organizations/$organization->id", data:$data)->assertStatus(201);
    }

    /**
     * fail on update when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnUpdatePolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::get()->random();
        $this->expectException(PermissionsException::class);
        $user->can('update', $organization);
    }

    /**
     * success update when user granted update the record
     *
     * @return void
     */
    public function testReturnOkOnUpdate()
    {
        $organization = Organization::whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $data = [
            'title' => 'Test',
        ];
        $this->actingAs($user)->patchJson(uri:"/api/v1/organizations/$organization->id", data:$data)->assertStatus(200);
    }

    /**
     * fail on delete when user hasn't permissions
     *
     * @return void
     */
    public function testExceptionOnDeletePolicy()
    {
        $user = User::factory()->create();
        $organization = Organization::get()->random();
        $this->expectException(PermissionsException::class);
        $user->can('delete', $organization);
    }

    /**
     * success remove when user granted delete the record
     *
     * @return void
     */
    public function testReturnOkOnDelete()
    {
        $organization = Organization::whereNotNull('admin_id')->get()->random();
        $user = User::find($organization->admin_id);
        $data = [
            'title' => 'Test',
        ];
        $this->actingAs($user)->deleteJson(uri:"/api/v1/organizations/$organization->id", data:$data)->assertStatus(204);
    }

    /**
     * test deny forceDelete any row for everyone
     *
     * @return void
     */
    public function testReturnFalseOnForceDelete()
    {
        $user = User::get()->random();
        $organization = Organization::get()->random();
        $this->actingAs($user)->assertFalse($user->can('forceDelete', $organization));
    }

}
