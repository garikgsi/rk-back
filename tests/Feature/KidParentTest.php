<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Organization;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use App\Models\KidParent;
use App\Models\Kid;
use App\Models\User;

class KidParentTest extends TestCase
{
    // use RefreshDatabase;

    // /**
    //  * seed before testing
    //  *
    //  * @var bool
    //  */
    // protected $seed = true;


    /**
     * test relation with Kid Model
     *
     * @return void
     */
    public function testKidRelation()
    {
        $kidParent = KidParent::whereNotNull('kid_id')->get()->random();
        $kid = $kidParent->kid;
        $this->assertTrue($kid instanceof Kid);
        $this->assertSame($kid->toArray(), Kid::find($kidParent->kid_id)->toArray());
    }

    /**
     * test relation with User Model
     *
     * @return void
     */
    public function testUserRelation()
    {
        $kidParent = KidParent::whereNotNull('user_id')->get()->random();
        $user = $kidParent->user;
        $this->assertTrue($user instanceof User);
        $this->assertSame($user->toArray(), User::find($kidParent->user_id)->toArray());
    }

    /**
     * show only kids granted by kids
     *
     * @return void
     */
    public function test_show_only_available_parents():array
    {
        $user = User::factory()->create();
        $parent = KidParent::get()->random();
        $organization1 = $parent->kid->organization;
        $parent->user_id = $user->id;
        $parent->save();
        $organization2 = Organization::where('id','<>',$organization1)->get()->random();
        $organization2->admin_id = $user->id;
        $organization2->save();

        $allKids = Kid::whereIn('organization_id',[$organization1->id, $organization2->id])->get();
        $kids = $allKids->pluck('id')->values()->all();
        $parents = $organization1->parents->merge($organization2->parents)->pluck('id')->unique()->sort()->values()->all();
        $url = "kid_parents?limit=0";

        $response = $this->request($url, $user);
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent())->data;
        $responseParents = collect($responseData)->pluck('id')->sort()->values()->all();
        // dd($user->id, $responseParents);
        $this->assertSame($responseParents, $parents);
        $this->assertSame(count($responseData), count($parents));
        return ['kids'=>$kids, 'user'=>$user];
    }

    /**
     * deny to not granted records
     *
     * @depends test_show_only_available_parents
     * @return void
     */
    public function test_deny_access_to_not_alloyed_row($data)
    {
        $parent = KidParent::whereNotIn('kid_id', $data['kids'])->get()->random();
        $url = "kid_parents/$parent->id";

        $response = $this->request($url, $data['user']);
        $response->assertStatus(404);
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
