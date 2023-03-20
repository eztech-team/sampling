<?php

use App\Models\BalanceTest;
use App\Models\IncomeTest;
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
        Schema::create('result_tocs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BalanceTest::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(IncomeTest::class)->nullable()->constrained()->onDelete('cascade');
            $table->string('first_comment')->nullable();
            $table->longText('first_link')->nullable();
            $table->string('second_comment')->nullable();
            $table->longText('second_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('result_tocs');
    }
};
