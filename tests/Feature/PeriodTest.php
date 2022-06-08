<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Period;
use App\Models\Plan;
use App\Models\Operation;
use App\Models\Organization;
use Illuminate\Testing\TestResponse;
use App\Models\User;



class PeriodTest extends TestCase
{
    // use RefreshDatabase;

    // /**
    //  * seed before testing
    //  *
    //  * @var bool
    //  */
    // protected $seed = true;

    /**
     * test Plan Model relation
     *
     * @return void
     */
    public function testPlanRelation()
    {
        $plan = Plan::whereNotNull('period_id')->get()->random();
        $period = Period::find($plan->period_id);
        $this->assertTrue($period->plans->first() instanceof Plan);
        $this->assertSame($period->plans->find($plan->id)->toArray(), $plan->toArray());
    }

    /**
     * test Organization model relation
     *
     * @return void
     */
    public function test_organization_relation()
    {
        $organization = Organization::whereHas('periods')->get()->random();
        $period = $organization->periods->random();
        $this->assertTrue($period->organization instanceof Organization);
        $this->assertSame($period->organization->id, $organization->id);
    }

    /**
     * show only kids granted by organizations
     *
     * @return void
     */
    public function test_show_only_available_periods():array
    {

        $user = User::find(Organization::get()->random()->admin_id);
        $organizations = Organization::where('admin_id',$user->id)->orWhereHas('parents',function($parents) use($user){
            $parents->where('user_id',$user->id);
        })->pluck('id')->values()->all();

        $allPeriods = Period::whereIn('organization_id',$organizations)->get();
        $periods = $allPeriods->pluck('id')->sort()->values()->all();

        $url = "periods?limit=0";

        $response = $this->request($url, $user);
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent())->data;
        $responsePeriods = collect($responseData)->pluck('id')->sort()->values()->all();
        $this->assertSame($responsePeriods, $periods);
        $this->assertSame(count($responseData), count($periods));
        return ['organizations'=>$organizations, 'user'=>$user];
    }

    /**
     * deny to not granted records
     *
     * @depends test_show_only_available_periods
     * @return void
     */
    public function test_deny_access_to_not_alloyed_row($data)
    {
        $period = Period::whereNotIn('organization_id', $data['organizations'])->get()->random();
        $url = "periods/$period->id";

        $response = $this->request($url, $data['user']);
        $response->assertStatus(404);
    }

    /**
     * check isAdmin for new Kid
     *
     * @return void
     */
    public function test_isAdmin_for_empty_organization()
    {
        $user = User::get()->random();
        $period = new Period();
        $this->actingAs($user)->assertSame($period->isAdmin(), false);
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
