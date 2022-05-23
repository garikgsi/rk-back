<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Plan;
use App\Models\Period;
use App\Models\Operation;

class PlanModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * seed before testing
     *
     * @var bool
     */
    protected $seed = true;

    /**
     * test Operation Model relation
     *
     * @return void
     */
    public function testoperationRelation()
    {
        $operation = Operation::whereNotNull('plan_id')->get()->random();
        $plan = Plan::find($operation->plan_id);
        $this->assertTrue($plan->operations->first() instanceof Operation);
        $this->assertSame($plan->operations->find($operation->id)->toArray(), $operation->toArray());
    }

    /**
     * test Period Model relation
     *
     * @return void
     */
    public function testPeriodRelation()
    {
        $plan = Plan::whereNotNull('period_id')->get()->random();
        $this->assertTrue($plan->period instanceof Period);
        $this->assertSame($plan->period->toArray(), Period::find($plan->period_id)->toArray());
    }
}
