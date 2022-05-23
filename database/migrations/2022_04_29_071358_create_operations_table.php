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
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->timestamps();

            $table->date('date_operation')->index('date_opertaion');
            $table->string('comment')->index('comment');
            $table->double('price',12,4)->default(0);
            $table->double('quantity',12,4)->default(1);
            $table->double('amount',12,4)->default(0);
            $table->string('image')->nullable();
            $table->foreignId('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operations');
    }
};
