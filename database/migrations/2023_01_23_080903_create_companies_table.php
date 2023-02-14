<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->string('bank_name'); // Банк
            $table->string('name')->unique(); // Название компании
            $table->string('bin')->unique(); // БИН
            $table->string('bik')->unique(); // БИК
            $table->string('iik')->unique(); // ИИК
            $table->string('phone_number'); // Контакный номер
            $table->string('full_name'); // ФИО руководителя
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
        Schema::dropIfExists('companies');
    }
};
