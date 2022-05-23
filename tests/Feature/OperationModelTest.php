<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Operation;
use App\Models\Plan;

class OperationModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * seed before testing
     *
     * @var bool
     */
    protected $seed = true;

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

}
