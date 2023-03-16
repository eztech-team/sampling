<?php

use App\Models\Aggregate;
use App\Models\BalanceItem;
use App\Models\NatureControl;
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
        Schema::create('balance_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BalanceItem::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(NatureControl::class)->constrained();
            $table->foreignIdFor(Aggregate::class)->constrained(); // excel file
            $table->string('name');
            $table->json('array_table');
            $table->string('effectiveness');
            $table->string('deviation');
            $table->integer('first_size');
            $table->integer('second_size')->nullable();
            $table->boolean('method')->default(0);
            $table->integer('status')->default(2);
            $table->softDeletes();
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
        Schema::dropIfExists('balance_tests');
    }
};
