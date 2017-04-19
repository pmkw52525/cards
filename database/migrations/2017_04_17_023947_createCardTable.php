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
    	if ( Schema::hasTable('cards') ) Schema::drop('cards');

		Schema::create('cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('activityId');
            $table->string('code');
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
        // Schema::drop('cards');
    }
}
