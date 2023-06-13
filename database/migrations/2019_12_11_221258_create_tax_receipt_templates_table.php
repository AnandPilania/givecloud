<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxReceiptTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_receipt_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_default')->default(0);
            $table->enum('template_type', ['template', 'revision']);
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('latest_revision_id')->nullable();
            $table->string('name', 64);
            $table->longText('body');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('tax_receipt_templates')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('latest_revision_id')->references('id')->on('tax_receipt_templates')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_receipt_templates');
    }
}
