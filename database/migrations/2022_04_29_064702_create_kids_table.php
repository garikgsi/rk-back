<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kids', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->timestamps();

            $table->string('last_name')->index('first_name');
            $table->string('name')->index('name');
            $table->string('patronymic')->index()->nullable();
            $table->date('birthday')->nullable()->index('birthday');
            $table->date('start_study')->nullable()->index('start_study');
            $table->date('end_study')->nullable()->index('end_study');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kids');
    }
};
