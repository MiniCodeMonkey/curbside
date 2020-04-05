<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['ACTIVE', 'PAUSED', 'INACTIVE'])->default('ACTIVE');
            $table->point('location')->spatialIndex();
            $table->string('phone', 15)->unique();
            $table->integer('radius')->nullable();
            $table->enum('criteria', ['ANYTIME', 'SOON', 'TODAY'])->default('ANYTIME');
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
        Schema::dropIfExists('subscribers');
    }
}
