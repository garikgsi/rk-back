<?php

namespace Tests\Feature;

use App\Models\Kid;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Models\User;
use App\Models\KidParent;
use Illuminate\Testing\TestResponse;

class OrganizationTest extends TestCase
{
    /**
     * request user by id
     *
     * @return void
     */
    public function test_show_only_available_organisations()
    {
        $url = "organizations?sort=id";

        $admin = Organization::get()->random();
        $user = User::find($admin->admin_id);
        $organizations = Organization::whereHas('parents', function($parents) use ($user) {
            $parents->where('user_id',$user->id);
        })->pluck('id')->values();
        $adminOrganizations = Organization::where('admin_id',$user->id)->orWhereIn('id',$organizations)->pluck('id')->values();
        $allOrganizations = $organizations->merge($adminOrganizations)->unique()->sort()->values()->all();

        $response = $this->request($url, $user);
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent())->data;
        $responseOrganizations = collect($responseData)->pluck('id')->sort()->values()->all();
        try {
            //code...
            $this->assertSame($responseOrganizations, $allOrganizations);
        } catch (\Throwable $th) {
            //throw $th;
            dd($user->id, $allOrganizations,$responseData);
        }
        $this->assertSame(count($responseData), count($allOrganizations));
    }

    /**
     * post only uniq slug
     *
     * @return void
     */
    public function test_uniq_slug()
    {
        $org = Organization::get()->random();
        $user = User::find($org->admin_id);
        $url = "organizations";
        $data = [
            'slug' => $org->slug,
            'title' => 'test',
            'admin_id' => $user->id,
        ];
        $response = $this->request($url, $user, 'postJson', $data);
        $response->assertStatus(422)
            ->assertJson([
                'is_error' => true,
            ]);
    }

    /**
     * ignore self uniq slug testing
     *
     * @return void
     */
    public function test_self_uniq_slug()
    {
        $org = Organization::get()->random();
        $user = User::find($org->admin_id);
        $url = "organizations/$org->id";
        $data = [
            'title' => 'test',
            'slug' => $org->slug
        ];
        // dd($org->toArray(), $url, $data);
        $response = $this->request($url, $user, 'patchJson', $data);
        $response->assertStatus(200)
            ->assertJson([
                'is_error' => false,
            ]);
    }

    /**
     * test if user is parent = admin of organization
     *
     * @return void
     */
    public function test_parent_isAdmin()
    {
        $organization = Organization::whereHas('kids',function($kids){
            $kids->whereHas('parents');
        })->get()->random();
        $kid = $organization->kids->random();
        $parent = $kid->parents->random();
        // $parent = KidParent::get()->random();
        // $kid = $parent->kid;
        // $organization = $kid->organization;

        $organization->admin_id = 0;
        $organization->save;
        $organization->parents()->update(['is_admin'=>false]);
        $user = User::get()->random();
        $parent->is_admin = true;
        $parent->user_id = $user->id;
        $parent->save();
        $this->actingAs($user)->assertTrue($organization->isAdmin());
    }

    /**
     * check isAdmin for new Organization
     *
     * @return void
     */
    public function test_isAdmin_for_new_organization()
    {
        $user = User::get()->random();
        $org = new Organization();
        $this->actingAs($user)->assertSame($org->isAdmin(), false);
    }

    /**
     * request get
     *
     * @param  string $url
     * @return TestResponse
     */
    protected function request($url, $user, $method='getJson', $data=null):TestResponse
    {
        switch($method){
            case 'getJson':{
                return $this->actingAs($user)->getJson("/api/v1/$url");
            } break;
            default: {
                return $this->actingAs($user)->$method("/api/v1/$url", $data);
            }
        }
    }

}
