<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Operation;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Period;
use Illuminate\Testing\TestResponse;
use App\Models\User;


class OperationTest extends TestCase
{
    // use RefreshDatabase;

    // /**
    //  * seed before testing
    //  *
    //  * @var bool
    //  */
    // protected $seed = true;

    /**
     * test Plan model relation
     *
     * @return void
     */
    public function testPlanRelation()
    {
        $operation = Operation::whereNotNull('plan_id')->get()->random();
        $this->assertTrue($operation->plan instanceof Plan);
        $this->assertSame($operation->plan->toArray(), Plan::find($operation->plan_id)->toArray());
    }

    /**
     * test Period Model relation
     *
     * @return void
     */
    public function testPeriodRelation()
    {
        $plan = Operation::whereNotNull('period_id')->get()->random();
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
        $operations = Operation::whereIn('period_id',$periods)->get()->pluck('id')->sort()->values()->all();

        $url = "operations?limit=0";

        $response = $this->request($url, $user);
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent())->data;
        $responseOperations = collect($responseData)->pluck('id')->sort()->values()->all();
        $this->assertSame($responseOperations, $operations);
        $this->assertSame(count($responseData), count($operations));
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
        $notAllowedRow = Operation::whereNotIn('period_id', $data['periods'])->get()->random();
        $url = "operations/$notAllowedRow->id";

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
