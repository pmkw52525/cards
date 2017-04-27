<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');

            $table->string('loginId')->nullable();
            $table->string('empId')->nullable();
            $table->string('empNo')->nullable();
            $table->integer('groupId')->nullable();

            $table->string('name')->nullable();
            $table->string('ename')->nullable();
            $table->string('email')->nullable();

            $table->string('status')->default('N');
            $table->text('logger');

            $table->rememberToken();
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
