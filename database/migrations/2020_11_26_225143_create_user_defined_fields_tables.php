<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDefinedFieldsTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_defined_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('entity')->index();
            $table->string('field_type')->index();
            $table->json('field_attributes');
            $table->timestamps();
        });

        Schema::create('user_defined_fieldables', function (Blueprint $table) {
            $table->id();
            $table->longText('value');
            $table->foreignId('user_defined_field_id')->constrained('user_defined_fields');
            $table->morphs('user_defined_fieldable', 'user_defined_fieldable_index');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_defined_fieldables');
        Schema::dropIfExists('user_defined_fields');
    }
}
