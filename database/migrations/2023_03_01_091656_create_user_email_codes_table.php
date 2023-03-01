<?php

use App\Models\City;
use App\Models\Country;
use App\Models\Role;
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
        Schema::create('user_email_codes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->foreignIdFor(City::class)->constrained();
            $table->foreignIdFor(Country::class)->constrained();
            $table->foreignIdFor(Role::class)->constrained();
            $table->string('email');
            $table->string('company_name');
            $table->integer('code')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('email_verification_send')->nullable();
            $table->string('password');
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
        Schema::dropIfExists('user_email_codes');
    }
};
