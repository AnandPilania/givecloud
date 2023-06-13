<?php

use Illuminate\Database\Migrations\Migration;

class EnableFundraiserProfanityFilter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * This is so that existing sites with fundraisers have to manually enable the filter.
         */
        if (sys_get('bool:fundraising_pages_enabled')) {
            sys_set('fundraising_pages_profanity_filter', false);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        sys_set('fundraising_pages_profanity_filter', true);
    }
}
