<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGlToTaxReceiptLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tax_receipt_line_items', function (Blueprint $table) {
            $table->string('gl_code')->nullable()->after('donated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax_receipt_line_items', function (Blueprint $table) {
            $table->dropColumn('gl_code');
        });
    }
}
