<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoscanToChains extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chains', function (Blueprint $table) {
            $table->boolean('autoscan')
                  ->default(true)
                  ->after('enabled')
                  ->comment('Whether this chain should automatically be scanned through the scheduler');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chains', function (Blueprint $table) {
            $table->dropColumn('autocan');
        });
    }
}
