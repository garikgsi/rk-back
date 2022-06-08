<?php

namespace Tests\Feature;

use App\Models\Organization;
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
        $organization = Organization::get()->random();
        $response = $this->getJson("/api/v1/report/public/$organization->slug");
        $periods = $organization->periods();

        $defaultPeriod = $organization->periods()->orderBy('end_date', 'desc')->orderBy('id','desc')->get()->first();
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
        $organization = Organization::get()->random();
        $period = $organization->periods->random();
        $response = $this->getJson("/api/v1/report/public/$organization->slug/$period->id");

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
        $organization = Organization::get()->random();
        $organization->periods()->delete();
        $response = $this->getJson("/api/v1/report/public/$organization->slug");

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('is_error', true)
                ->where('error', 'Похоже, данные еще не были внесены')
        );
        Period::withTrashed()->restore();
    }

    /**
     * test get public info with wrong slug
     *
     * @return void
     */
    public function test_get_public_info_with_wrong_slug()
    {
        Period::whereNotNull('id')->delete();
        $response = $this->getJson("/api/v1/report/public/_wrong_slug_");

        $response->assertStatus(404)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('is_error', true)
                ->where('error', 'Невозможно прочитать отчет')
        );
        Period::withTrashed()->restore();
    }

}
