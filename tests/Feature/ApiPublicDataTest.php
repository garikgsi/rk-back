<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use App\Models\User;
use App\Models\Period;
use App\Models\Payment;
use App\Models\Plan;

class ApiPublicDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * seed before testing
     *
     * @var bool
     */
    protected $seed = true;

    /**
     * test get public info by default
     *
     * @return void
     */
    public function test_get_public_info_without_period()
    {
        $response = $this->getJson("/api/v1/report/public");
        $periods = Period::get();

        $defaultPeriod = Period::get()->last();
        if ($defaultPeriod) {
            $operations = $defaultPeriod->operations()->get();
            $plans = $defaultPeriod->plans()->get();

            $response->assertStatus(200)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->where('is_error', false)
                        ->where('error', null)
                        ->where('data.current_period.id',$defaultPeriod->id)
                        ->has('data.operations', $operations->count(), fn ($json) =>
                            $json->where('id', $operations->first()->id)
                                ->etc()
                        )
                        ->has('data.plans', $plans->count(), fn ($json) =>
                            $json->where('id', $plans->first()->id)
                                ->etc()
                        )
                        ->has('data.periods', $periods->count(), fn ($json) =>
                            $json->where('id', $periods->first()->id)
                                ->etc()
                        )
                        ->has('data.totals.operations')
                        ->has('data.totals.plans')
                        ->has('data.totals.payments')
                        ->has('data.totals.startDebt')
                        ->has('data.totals.startSaldo')
                        ->etc()
                );
        }
    }

    /**
     * test get public info for period
     *
     * @return void
     */
    public function test_get_public_info_with_period()
    {
        $period = Period::get()->random();
        $response = $this->getJson("/api/v1/report/public/$period->id");

        $operations = $period->operations()->get();
        $plans = $period->plans()->get();

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('is_error', false)
                    ->where('error', null)
                    ->where('data.current_period.id',$period->id)
                    ->has('data.operations', $operations->count(), fn ($json) =>
                        $json->where('id', $operations->first()->id)
                            ->etc()
                        )
                        ->has('data.plans', $plans->count(), fn ($json) =>
                            $json->where('id', $plans->first()->id)
                                ->etc()
                        )
                        ->has('data.periods')
                    ->has('data.totals.operations')
                    ->has('data.totals.plans')
                    ->has('data.totals.payments')
                    ->has('data.totals.startDebt')
                    ->has('data.totals.startSaldo')
                    ->etc()
        );
    }

    /**
     * test get public info when empty tables
     *
     * @return void
     */
    public function test_get_public_info_with_empty_period_table()
    {
        Period::whereNotNull('id')->delete();
        $response = $this->getJson("/api/v1/report/public/");

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('is_error', true)
                ->where('error', 'Похоже, данные еще не были внесены')
        );
        Period::withTrashed()->restore();
    }

}
