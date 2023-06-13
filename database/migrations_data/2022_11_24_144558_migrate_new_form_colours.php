<?php

use Illuminate\Database\Migrations\Migration;

class MigrateNewFormColours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $colours = [
            '#FC58AF' => '#DC529B', // pink
            '#695DB1' => '#695DB1', // purple
            '#0066FF' => '#2467CC', // blue
            '#E0514B' => '#CB4F4F', // red
            '#18A586' => '#2B957E', // green
            '#E59145' => '#D28845', // orange
        ];

        if (in_array(sys_get('org_primary_color'), array_keys($colours), true)) {
            sys_set('org_primary_color', $colours[sys_get('org_primary_color')]);
        }

        foreach ($colours as $oldColour => $newColour) {
            DB::table('metadata')
                ->where('key', 'donation_forms_branding_colour')
                ->where('value', $oldColour)
                ->update(['value' => $newColour]);
        }
    }
}
