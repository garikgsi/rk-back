<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

use App\Models\User;
use App\Models\Organization;
use App\Models\Period;
use App\Models\Kid;
use App\Models\KidParent;
use App\Models\Operation;
use App\Models\Plan;
use App\Models\Payment;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    // public function runRandom()
    // {
    //     \App\Models\User::factory(100)->create();
    //     \App\Models\Message::factory(30)->create();
    //     \App\Models\Organization::factory(7)
    //         ->has(\App\Models\Period::factory(5)
    //             ->has(\App\Models\Plan::factory()
    //                 ->count(rand(15,25))
    //                 ->has(\App\Models\Operation::factory()
    //                     ->count(rand(3,5))
    //                 )
    //             )
    //         )
    //         ->has(\App\Models\Kid::factory(30)
    //             ->has(\App\Models\KidParent::factory(2),'parents')
    //             ->has(\App\Models\Payment::factory(rand(10,15)),'payments'))
    //         ->create();
    // }

    public function run()
    {
        $demoUser = User::factory()->create([
            "name" => "Флешкин Билл",
            "email" => "demo@example.com"
        ]);
        $demoOrgSchool = Organization::factory()
        ->create([
            'title' => 'Школа №587',
            'slug' => 'demo-school-587-for-example',
            'admin_id' => $demoUser,
        ]);

        $demoOrgKg = Organization::factory()
        ->create([
            'title' => 'Детский сад "Дракула"',
            'slug' => 'demo-drakula-kindergarden-for-example',
            'admin_id' => 0,
        ]);

        $organizations = [
            $demoOrgSchool, $demoOrgKg
        ];
        $periods = [
            [
                'name' => '2021-22 уч.год',
                'start_date' => '2021-09-01',
                'end_date' => '2022-05-31'
            ],
            [
                'name' => '2022-23 уч.год',
                'start_date' => '2022-09-01',
                'end_date' => '2023-05-31'
            ],
        ];


        foreach($organizations as $org) {
            Kid::factory()->count(rand(25,35))->for($org)
                ->has(KidParent::factory()->male(),'parents')
                ->has(KidParent::factory()->female(),'parents')
                ->create();
            foreach($periods as $period) {
                $createdPeriod = Period::factory()
                    ->has(Plan::factory()->count(rand(3,5))
                        ->has(Operation::factory())
                    ,'plans')
                    ->create(array_merge($period,['organization_id'=>$org]));
                // payments
                $kidsCount = $org->kids->count();
                $planSum = $createdPeriod->plans->sum('amount');
                $planPerKid = round($planSum/$kidsCount,0);
                $org->kids->each(function($kid) use ($planPerKid, $createdPeriod) {
                    $ra = rand(0,10);
                    if ($ra>2) {
                        Payment::factory()->create([
                            'amount' => $planPerKid,
                            'period_id' => $createdPeriod,
                            'kid_id' => $kid
                        ]);
                    } elseif($ra==2) {
                        Payment::factory()->count(1,3)->create([
                            'amount' => round(rand(25,50)*$planPerKid/100),
                            'period_id' => $createdPeriod,
                            'kid_id' => $kid
                        ]);
                    }
                });
            }
        }


        // set demo user parent in kindergarden
        $parent = $demoOrgKg->kids()->get()->random()->parents()->get()->random();
        $parent->user_id = $demoUser->id;
        $parent->save();




        \App\Models\Message::factory(30)->create();
    }
}
