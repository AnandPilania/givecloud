<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLedgerEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();

            $table->string('type')->default('Line Item')->nullable();

            $table->dateTime('captured_at');
            $table->decimal('amount', 19, 4)->default(0);
            $table->integer('qty')->default(0);
            $table->decimal('discount', 19, 4)->default(0);
            $table->decimal('original_amount', 19, 4)->default(0);

            $table->string('gl_account')->nullable();

            $table->morphs('ledgerable');

            $table->foreignId('order_id')->nullable()->constrained('productorder');
            $table->foreignId('item_id')->nullable()->constrained('productorderitem');
            $table->foreignId('sponsorship_id')->nullable()->constrained('sponsorship');
            $table->foreignId('supporter_id')->nullable()->constrained('member');
            $table->foreignId('fundraising_page_id')->nullable()->constrained('fundraising_pages');

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
        Schema::dropIfExists('ledger_entries');
    }
}
