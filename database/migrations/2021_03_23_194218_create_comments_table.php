<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            $table->longText('body');
            $table->morphs('commentable');

            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');

            $table->foreign('created_by')->references('id')->on('user');
            $table->foreign('updated_by')->references('id')->on('user');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
