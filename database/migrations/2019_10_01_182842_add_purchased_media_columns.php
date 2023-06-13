<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchasedMediaColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productinventoryfiles', function (Blueprint $table) {
            $table->integer('fileid')->nullable()->change();
            $table->string('external_resource_uri')->nullable();
        });

        Schema::table('productorderitemfiles', function (Blueprint $table) {
            $table->integer('fileid')->nullable()->change();
            $table->string('external_resource_uri')->nullable();
            $table->string('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productinventoryfiles', function (Blueprint $table) {
            $table->integer('fileid')->change();
            $table->dropColumn(['external_resource_uri']);
        });

        Schema::table('productorderitemfiles', function (Blueprint $table) {
            $table->integer('fileid')->change();
            $table->dropColumn(['external_resource_uri', 'description']);
        });
    }
}
