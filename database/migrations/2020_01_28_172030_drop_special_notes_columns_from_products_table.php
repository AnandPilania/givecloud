<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropSpecialNotesColumnsFromProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn([
                'imgfull',
                'allow_public_message',
                'show_orders',
                '_drop_isdonation',
                '_drop_isrecurring',
                '_drop_recurring_type',
                '_drop_recurring_initial_charge',
                '_drop_recurringinterval',
                '_drop_min_price',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->boolean('show_orders')->default(0)->after('notes');
            $table->boolean('allow_public_message')->default(0)->after('show_orders');
        });
    }
}
