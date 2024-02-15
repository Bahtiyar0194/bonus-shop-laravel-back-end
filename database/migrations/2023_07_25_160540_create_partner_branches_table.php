<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_branches', function (Blueprint $table) {
            $table->increments('branch_id');     
            $table->integer('partner_id')->unsigned();
            $table->foreign('partner_id')->references('partner_id')->on('partners');
            $table->integer('city_id')->unsigned();
            $table->foreign('city_id')->references('city_id')->on('cities');
            $table->string('street');
            $table->string('house');
            $table->integer('floor')->nullable();
            $table->integer('flat')->nullable();
            $table->string('branch_phone');
            $table->string('branch_phone_additional');
            $table->string('latitude');
            $table->string('longitude');
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
        Schema::dropIfExists('partner_branches');
    }
}
