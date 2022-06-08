<?php

namespace Tests\Feature;

use App\Models\Kid;
use App\Models\KidParent;
use App\Models\Organization;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Testing\TestResponse;

class KidsTest extends TestCase
{
    /**
     * show only kids granted by organizations
     *
     * @return void
     */
    public function test_show_only_available_kids():array
    {

        $user = User::find(Organization::get()->random()->admin_id);
        $organizations = Organization::where('admin_id',$user->id)->orWhereHas('parents',function($parents) use($user){
            $parents->where('user_id',$user->id);
        })->pluck('id')->values()->all();

        $allKids = Kid::whereIn('organization_id',$organizations)->get();
        $kids = $allKids->pluck('id')->sort()->values()->all();

        $url = "kids?limit=0";

        $response = $this->request($url, $user);
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent())->data;
        $responseKids = collect($responseData)->pluck('id')->sort()->values()->all();
        $this->assertSame($responseKids, $kids);
        $this->assertSame(count($responseData), count($kids));
        return ['organizations'=>$organizations, 'user'=>$user];
    }

    /**
     * deny to not granted records
     *
     * @depends test_show_only_available_kids
     * @return void
     */
    public function test_deny_access_to_not_alloyed_row($data)
    {
        $kid = Kid::whereNotIn('organization_id', $data['organizations'])->get()->random();
        $url = "kids/$kid->id";

        $response = $this->request($url, $data['user']);
        $response->assertStatus(404);
    }


    /**
     * test Organization model relation
     *
     * @return void
     */
    public function test_organization_relation()
    {
        $kid = Kid::get()->random();
        $organization = Organization::find($kid->organization_id);
        $this->assertTrue($kid->organization instanceof Organization);
        $this->assertSame($kid->organization->toArray(), $organization->toArray());
    }

    /**
     * test if user is admin of organization belogs kid
     *
     * @return void
     */
    public function test_isAdmin_from_organization() {
        $kid = Kid::get()->random();
        $user = User::find($kid->organization->admin_id);
        $kid->organization->parents()->update(['is_admin'=>false]);
        $this->actingAs($user)->assertTrue($kid->isAdmin());
    }

    /**
     * test if user is parent = admin of organization belogs kid
     *
     * @return int [userId, kidId]
     */
    public function test_parent_isAdmin():array
    {
        $parent = KidParent::get()->random();
        $kid = $parent->kid;
        $organization = $kid->organization;
        $organization->admin_id = 0;
        $organization->save;
        $organization->parents()->update(['is_admin'=>false]);
        $user = User::get()->random();
        $parent->is_admin = true;
        $parent->user_id = $user->id;
        $parent->save();
        $this->actingAs($user)->assertTrue($kid->isAdmin());
        return ['userId'=>$user->id, 'kidId'=>$kid->id];
    }

    /**
     * test_is_not_admin
     *
     * @depends test_parent_isAdmin
     * @return void
     */
    public function test_is_not_admin($data)
    {
        $user = User::where('id', '<>', $data['userId'])->get()->random();
        $kid = Kid::find($data['kidId']);
        $this->actingAs($user)->assertSame($kid->isAdmin(), false);
    }

    /**
     * check isAdmin for new Kid
     *
     * @return void
     */
    public function test_isAdmin_for_empty_organization()
    {
        $user = User::get()->random();
        $kid = new Kid();
        $this->actingAs($user)->assertSame($kid->isAdmin(), false);
    }

    /**
     * request get
     *
     * @param  string $url
     * @return TestResponse
     */
    protected function request($url, $user):TestResponse
    {
        return $this->actingAs($user)->getJson("/api/v1/$url");
    }
}
