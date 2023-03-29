<?php

use App\Models\BalanceItem;
use App\Models\BalanceTest;
use App\Models\IncomeItem;
use App\Models\IncomeTest;
use App\Models\MaterialMisstatement;
use App\Models\TDMethod;
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
        Schema::create('tds', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BalanceItem::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(IncomeItem::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(BalanceTest::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(IncomeTest::class)->nullable()->constrained()->onDelete('set null');
            $table->integer('td_method')->default(0);
            $table->string('name');
            $table->json('array_table');
            $table->boolean('stratification')->default(false); // Имеется ли стратификация
            $table->integer('count_stratification')->default(1); // Количество стратификаций
            $table->integer('status')->default(0);
            $table->integer('material_misstatement')->default(0); // Likelihood of material misstatement
            $table->integer('control_risk')->default(0); // Control risk
            $table->string('control_risc_comment')->nullable();
            $table->integer('ratio_expected_error')->default(5); // Ratio of expected error to tolerable misstatement
            $table->string('ratio_expected_error_comment')->nullable();
            $table->integer('size')->default(1); // Размер выборки
            $table->integer('result_method')->default(0); // Результаты td Метод
            $table->integer('attempt')->default(1); // Результаты td Метод
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
        Schema::dropIfExists('tds');
    }
};
