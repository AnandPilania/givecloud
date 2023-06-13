<?php

use Illuminate\Database\Migrations\Migration;

class UpdateFirstNameMergeTagsForFundraisingFormsEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('emails')->where('type', 'fundraising_page_donation_received')
            ->update([
                'body_template' => DB::raw("REPLACE(body_template, '[[first_name]]', '[[page_author_first_name]]')"),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
