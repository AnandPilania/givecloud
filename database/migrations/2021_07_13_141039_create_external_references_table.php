<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExternalReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('external_references', function (Blueprint $table) {
            $table->id();

            $table->morphs('referenceable');
            $table->string('type');
            $table->string('service');

            $table->string('reference')->index();

            $table->timestamps();

            $table->unique(['referenceable_type', 'referenceable_id', 'type', 'service'], 'external_reference_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('external_references');
    }
}
