<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(100)->create();
        \App\Models\Message::factory(30)->create();
        \App\Models\Organization::factory(7)
            ->has(\App\Models\Period::factory(5)
                ->has(\App\Models\Plan::factory()
                    ->count(rand(15,25))
                    ->has(\App\Models\Operation::factory()
                        ->count(rand(3,5))
                    )
                )
            )
            ->has(\App\Models\Kid::factory(30)
                ->has(\App\Models\KidParent::factory(2),'parents')
                ->has(\App\Models\Payment::factory(rand(10,15)),'payments'))
            ->create();
    }
}
