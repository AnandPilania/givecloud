<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecurringBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recurring_batches', function (Blueprint $table) {
            $table->id();
            $table->date('batched_on');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('elapsed_time')->nullable();
            $table->unsignedTinyInteger('max_simultaneous');
            $table->unsignedInteger('accounts_count')->default(0);
            $table->unsignedInteger('accounts_processed')->default(0);
            $table->unsignedInteger('transactions_approved')->default(0);
            $table->unsignedInteger('transactions_declined')->default(0);
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
        Schema::dropIfExists('recurring_batches');
    }
}
