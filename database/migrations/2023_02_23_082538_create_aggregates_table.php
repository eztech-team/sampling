<?php

use App\Models\Project;
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

    // EXCEL FILE
    public function up()
    {
        Schema::create('aggregates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('path');
            $table->integer('amount_column')->nullable();
            $table->boolean('title')->default(0); // 0 -> without title 1 -> with title
            $table->foreignIdFor(Project::class)->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('aggregates');
    }
};
