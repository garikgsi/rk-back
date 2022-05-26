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
        Schema::table('users', function (Blueprint $table) {
            $table->string('code', 32)->nullable()->default(null);
            $table->datetime('code_expired')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('users', 'code_expired')) {
                $table->dropColumn('code_expired');
            }
        });
    }
};
