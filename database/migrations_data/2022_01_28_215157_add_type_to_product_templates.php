<?php

use Ds\Enums\ProductType;
use Ds\Models\Product;
use Illuminate\Database\Migrations\Migration;

class AddTypeToProductTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Product::query()
            ->whereNotNull('template_name')
            ->update(['type' => ProductType::TEMPLATE]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Product::query()
            ->whereNotNull('template_name')
            ->update(['type' => null]);
    }
}
