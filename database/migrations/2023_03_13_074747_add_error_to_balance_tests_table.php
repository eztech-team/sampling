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
        Schema::table('balance_tests', function (Blueprint $table) {
            $table->string('first_error')->nullable();
            $table->string('second_error')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('balance_tests', function (Blueprint $table) {
            $table->dropColumn('first_error');
            $table->dropColumn('second_error');
        });
    }
};
