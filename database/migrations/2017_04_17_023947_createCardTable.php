<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('cards', function (Blueprint $table) {
         	$table->increments('id');
            $table->integer('activityId')->default(0);
            $table->string('serialNo')->comment = "4碼 activityId, 6 碼依序號碼";
            $table->string('code')->comment = "檢查碼";
            $table->string('status')->comment = 'enabled, disabled, used';
            $table->datetime('useTime')->nullable();
            $table->text('ext')->nullable();
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
        Schema::drop('cards');
    }
}
