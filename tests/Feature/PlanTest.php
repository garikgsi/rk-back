<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Plan;
use App\Models\Period;
use App\Models\Operation;
use App\Models\Organization;
use Illuminate\Testing\TestResponse;
use App\Models\User;

class PlanTest extends TestCase
{
    // use RefreshDatabase;

    // /**
    //  * seed before testing
    //  *
    //  * @var bool
    //  */
    // protected $seed = true;

    /**
     * test Operation Model relation
     *
     * @return void
     */
    public function testOperationRelation()
    {
        $plan = Plan::whereHas('operations')->get()->random();
        $operation = $plan->operations->random();
        // $operation = Operation::whereNotNull('plan_id')->get()->random();
        // $plan = Plan::find($operation->plan_id);
        $this->assertTrue($operation instanceof Operation);
        $this->assertSame($plan->operations->find($operation->id)->toArray(), $operation->toArray());
    }

    /**
     * test Period Model relation
     *
     * @return void
     */
    public function testPeriodRelation()
    {
        $period = Period::whereHas('plans')->get()->random();
        $plan = $period->plans->random();
        // $plan = Plan::whereNotNull('period_id')->get()->random();
        $this->assertTrue($plan->period instanceof Period);
        $this->assertSame($plan->period->toArray(), Period::find($plan->period_id)->toArray());
    }

        /**
     * show only kids granted by kids
     *
     * @return void
     */
    public function test_show_only_available_rows():array
    {

        $user = User::find(Organization::get()->random()->admin_id);
        $organizations = Organization::where('admin_id',$user->id)->orWhereHas('parents',function($parents) use($user){
            $parents->where('user_id',$user->id);
        })->pluck('id')->values()->all();

        $allPeriods = Period::whereIn('organization_id',$organizations)->get();
        $periods = $allPeriods->pluck('id')->sort()->values()->all();
        $plans = Plan::whereIn('period_id',$periods)->get()->pluck('id')->sort()->values()->all();

        $url = "plans?limit=0";

        $response = $this->request($url, $user);
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent())->data;
        $responsePlans = collect($responseData)->pluck('id')->sort()->values()->all();
        $this->assertSame($responsePlans, $plans);
        $this->assertSame(count($responseData), count($plans));
        return ['periods'=>$periods, 'user'=>$user];
    }

    /**
     * deny to not granted records
     *
     * @depends test_show_only_available_rows
     * @return void
     */
    public function test_deny_access_to_not_alloyed_row($data)
    {
        $notAllowedRow = Plan::whereNotIn('period_id', $data['periods'])->get()->random();
        $url = "plans/$notAllowedRow->id";

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
