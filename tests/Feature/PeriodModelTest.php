<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Period;
use App\Models\Plan;
use App\Models\Operation;

class PeriodModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * seed before testing
     *
     * @var bool
     */
    protected $seed = true;

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

}
