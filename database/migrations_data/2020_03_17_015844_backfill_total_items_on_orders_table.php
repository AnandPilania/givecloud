<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillTotalItemsOnOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('productorder as o')
            ->joinSub(
                DB::table('productorderitem as i')
                    ->selectRaw('i.productorderid as order_id, count(i.id) as item_count')
                    ->groupBy('i.productorderid'),
                'agg_items',
                function ($join) {
                    $join->on('o.id', 'agg_items.order_id');
                }
            )->update([
                'total_items' => DB::raw('agg_items.item_count'),
            ]);
    }
}
