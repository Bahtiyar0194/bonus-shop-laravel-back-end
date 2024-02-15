<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->increments('bonus_id');
            $table->integer('operation_id')->unsigned();
            $table->foreign('operation_id')->references('operation_id')->on('operations')->onDelete('cascade');
            $table->integer('recipient_id')->unsigned();
            $table->foreign('recipient_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->integer('amount');
            $table->integer('level_id')->unsigned();
            $table->foreign('level_id')->references('level_id')->on('bonus_levels');
            $table->integer('bonus_type_id')->unsigned();
            $table->foreign('bonus_type_id')->references('bonus_type_id')->on('types_of_bonuses');
            $table->integer('status_type_id')->default(8)->unsigned();
            $table->foreign('status_type_id')->references('status_type_id')->on('types_of_status');
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
        Schema::dropIfExists('bonuses');
    }
}
