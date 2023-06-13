<?php

use Ds\Models\ProductCustomField;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultValueColumnToProductfields extends Migration
{
    public const MAX_NUMBER_OF_RECORD_TO_GO_THROUGH_AT_ONCE = 200;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productfields', function (Blueprint $table) {
            $table->string('default_value')->nullable()->after('options');
        });

        $this->setDefaultOptionValueForExistingOptions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productfields', function (Blueprint $table) {
            $table->dropColumn('default_value');
        });
    }

    // Set default_value to current first option value.
    private function setDefaultOptionValueForExistingOptions(): void
    {
        ProductCustomField::where('options', '<>', '')
            ->chunk(self::MAX_NUMBER_OF_RECORD_TO_GO_THROUGH_AT_ONCE, function ($productCustomFields) {
                $productCustomFields->each(function ($field) {
                    if (empty($field->options)) {
                        return;
                    }

                    // Set the default_value to be the first option
                    // to keep the previous behaviour.
                    $field->default_value = $field->choices[0]->value;
                    $field->save();
                });
            });
    }
}
