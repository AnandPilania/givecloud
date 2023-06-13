<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillDeleteByColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $models = [
            new \Ds\Domain\Sponsorship\Models\Sponsor,
            new \Ds\Models\FundraisingPage,
            new \Ds\Models\Order,
            new \Ds\Models\Pledge,
            new \Ds\Models\PledgeCampaign,
            new \Ds\Models\Product,
            new \Ds\Models\ShippingMethod,
            new \Ds\Models\Tax,
            new \Ds\Models\TaxReceipt,
            new \Ds\Models\Timeline,
            new \Ds\Models\Tribute,
            new \Ds\Models\TributeType,
            new \Ds\Models\User,
        ];

        foreach ($models as $model) {
            DB::table($model->getTable())
                ->whereNotNull($model->getDeletedAtColumn())
                ->update([$model->getDeletedByColumn() => DB::raw($model->getUpdatedByColumn())]);
        }
    }
}
