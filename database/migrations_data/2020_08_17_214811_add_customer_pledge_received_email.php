<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCustomerPledgeReceivedEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('emails')->where('type', 'customer_pledge_received')->count()) {
            return;
        }

        DB::table('emails')->insert([
            'type'              => 'customer_pledge_received',
            'name'              => 'Pledge Received: To Customer',
            'subject'           => 'Thanks for your pledge!',
            'body_template'     => <<<'HTML'
                <p><strong>Dear [[bill_first_name]],</strong></p>
                <p>This email confirms that your <strong>Pledge #[[pledge_number]]</strong> for <strong>[[pledge_amount]]</strong> has been successfully received.</p>
                <p>--<br />[[organization_name]]<br />[[site_url]]</p>
                HTML,
            'is_deleted'        => 0,
            'is_active'         => 0,
            'active_start_date' => '2012-01-01',
            'is_protected'      => 1,
            'created_at'        => '2020-08-17 21:40:49',
            'created_by'        => 1,
            'updated_at'        => '2020-08-17 21:47:07',
            'updated_by'        => 1,
            'day_offset'        => 0,
            'hint'              => 'Sent to all donors & customers who make a pledge through your site confirming their pledge was successfully processed.',
            'category'          => 'Donors & Customers',
        ]);
    }
}
