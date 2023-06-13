<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpamFraudToProductorderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->boolean('is_spam')->default(0)->after('is_processed');
            $table->dateTime('marked_as_spam_at')->nullable()->after('refunded_by');
            $table->unsignedBigInteger('marked_as_spam_by')->nullable()->after('marked_as_spam_at');
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
            $table->dropColumn([
                'is_spam',
                'marked_as_spam_at',
                'marked_as_spam_by',
            ]);
        });
    }
}
