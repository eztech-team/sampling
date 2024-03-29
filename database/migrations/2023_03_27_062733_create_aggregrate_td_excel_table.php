<?php

use App\Models\Aggregate;
use App\Models\Td;
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
        Schema::create('aggregate_td_excel', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Aggregate::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Td::class)->constrained()->onDelete('cascade');
            $table->json('data')->nullable();
            $table->string('amount_column');
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
        Schema::dropIfExists('aggregate_td_excel');
    }
};
