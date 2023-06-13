<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsolidatedTaxReceiptChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->enum('tax_receipt_type', ['single', 'none', 'consolidated'])->default('single')->after('tax_country');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('tax_receipt_type', ['single', 'none', 'consolidated'])->default('single')->after('transaction_type');
        });

        Schema::table('tax_receipts', function (Blueprint $table) {
            $table->enum('status', ['draft', 'issued', 'void'])->default('issued')->after('transaction_id');
            $table->enum('receipt_type', ['single', 'consolidated'])->default('single')->after('transaction_id');
            $table->char('currency_code', 3)->nullable()->after('amount');
            $table->integer('account_id')->nullable()->after('currency_code');
            $table->unsignedInteger('tax_receipt_template_id')->nullable()->after('phone');
            $table->dateTime('voided_at')->nullable()->after('created_at');
            $table->integer('voided_by')->unsigned()->nullable()->after('voided_at');

            $table->foreign('account_id')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('tax_receipt_template_id')->references('id')->on('tax_receipt_templates')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::create('tax_receipt_line_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tax_receipt_id')->unsigned();
            $table->integer('order_id')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->string('description');
            $table->decimal('amount', 19, 4)->unsigned();
            $table->char('currency_code', 3);
            $table->dateTime('donated_at');

            $table->foreign('tax_receipt_id')->references('id')->on('tax_receipts')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('order_id')->references('id')->on('productorder')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->dropColumn('tax_receipt_type');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('tax_receipt_type');
        });

        Schema::table('tax_receipts', function (Blueprint $table) {
            $table->dropForeign('tax_receipts_account_id_foreign');
            $table->dropForeign('tax_receipts_tax_receipt_template_id_foreign');
            $table->dropColumn([
                'status',
                'receipt_type',
                'currency_code',
                'account_id',
                'tax_receipt_template_id',
                'voided_at',
                'voided_by',
            ]);
        });

        Schema::dropIfExists('tax_receipt_line_items');
    }
}
