<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->text('photo')->nullable();
            $table->string('lat')->nullable();
            $table->string('lang')->nullable();
            $table->integer('type')->comment('1-admin | 2 - customer | 3 - staff')->nullable();
            $table->boolean('is_notification')->comment('1 - enable | 2 - disable')->default(1);
            $table->boolean('status')->comment('1 - active | 2 - deactive')->default(1);
            $table->string('device_type')->nullable();
            $table->text('device_token')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
