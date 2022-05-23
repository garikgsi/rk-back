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
        Schema::create('kid_parents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->string('last_name')->index('first_name');
            $table->string('name')->index('name');
            $table->string('patronymic')->index('patronyminic')->nullable();
            $table->string('phone',30)->nullable()->index('phone');
            $table->foreignId('user_id')->nullable();
            $table->foreignId('kid_id')->nullable();
            $table->boolean('is_admin')->default(false)->index('is_admin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parents');
    }
};
