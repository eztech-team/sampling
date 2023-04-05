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
        Schema::table('tds', function (Blueprint $table) {
            $table->integer('magnitude')->default(0);
            $table->integer('inherent_risk')->default(0);
            $table->string('auditor_confidence_level')->nullable();
            $table->string('misstatement_percentage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tds', function (Blueprint $table) {
            $table->dropColumn('magnitude');
            $table->dropColumn('inherent_risk');
            $table->dropColumn('auditor_confidence_level');
            $table->dropColumn('misstatement_percentage');
        });
    }
};
