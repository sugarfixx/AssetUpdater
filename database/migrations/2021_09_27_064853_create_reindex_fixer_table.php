<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReindexFixerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reindex_fixer', function (Blueprint $table) {
            $table->id();
            $table->integer('queue_id');
            $table->bigInteger('asset_id');
            $table->json('metadata');
            $table->json('item');
            $table->boolean('done');
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
        Schema::dropIfExists('reindex_fixer');
    }
}
