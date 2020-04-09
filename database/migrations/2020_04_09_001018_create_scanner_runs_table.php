<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScannerRunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scanner_runs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('chain_id');
            $table->foreign('chain_id')->references('id')->on('chains');

            $table->string('hostname')->nullable();

            $table->enum('status', ['ENQUEUED', 'STARTED', 'SUCCEEDED', 'FAILED']);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('stores_scanned')->nullable();
            $table->unsignedInteger('timeslots_found')->nullable();
            $table->text('error_message')->nullable();

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
        Schema::dropIfExists('scanner_runs');
    }
}
