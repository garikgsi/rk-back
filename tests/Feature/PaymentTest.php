<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Payment;
use App\Models\Kid;
use App\Models\Organization;
use App\Models\Period;
use App\Models\User;
use Illuminate\Testing\TestResponse;

class PaymentTest extends TestCase
{
    // use RefreshDatabase;

    // /**
    //  * seed before testing
    //  *
    //  * @var bool
    //  */
    // protected $seed = true;

    /**
     * test Kid Model relation
     *
     * @return void
     */
    public function testKidRelation()
    {
        $payment = Payment::whereNotNull('kid_id')->get()->random();
        $this->assertTrue($payment->kid instanceof Kid);
        $this->assertSame($payment->kid->toArray(), Kid::find($payment->kid_id)->toArray());
    }

    /**
     * test Period Model relation
     *
     * @return void
     */
    public function testPeriodRelation()
    {
        $plan = Payment::whereNotNull('period_id')->get()->random();
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
        $payments = Payment::whereIn('period_id',$periods)->get()->pluck('id')->sort()->values()->all();

        $url = "payments?limit=0";

        $response = $this->request($url, $user);
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent())->data;
        $responsePayments = collect($responseData)->pluck('id')->sort()->values()->all();
        $this->assertSame($responsePayments, $payments);
        $this->assertSame(count($responseData), count($payments));
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
        $notAllowedRow = Payment::whereNotIn('period_id', $data['periods'])->get()->random();
        $url = "payments/$notAllowedRow->id";

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
