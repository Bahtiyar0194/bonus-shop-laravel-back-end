<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->increments('partner_id');
            $table->string('partner_name');
            $table->string('partner_org_name');
            $table->string('partner_bin');
            $table->string('partner_email');
            $table->string('partner_phone');
            $table->integer('city_id')->unsigned();
            $table->foreign('city_id')->references('city_id')->on('cities');
            $table->float('bonus')->default(10);
            $table->string('logo')->nullable();
            $table->integer('applicant_id')->unsigned();
            $table->foreign('applicant_id')->references('user_id')->on('users');
            $table->integer('operator_id')->unsigned();
            $table->foreign('operator_id')->references('user_id')->on('users');
            $table->integer('manager_id')->unsigned()->nullable();
            $table->foreign('manager_id')->references('user_id')->on('users');
            $table->integer('status_type_id')->default(12)->unsigned();
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
        Schema::dropIfExists('partners');
    }
}