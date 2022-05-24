<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Payment;
use App\Models\Kid;
use App\Models\Period;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * seed before testing
     *
     * @var bool
     */
    protected $seed = true;

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

}
