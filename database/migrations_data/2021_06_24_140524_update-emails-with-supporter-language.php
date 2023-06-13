<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateEmailsWithSupporterLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('emails')->where('category', 'Accounts')->update([
            'category' => 'Supporters',
        ]);
        DB::table('emails')->where('category', 'Donors & Customers')->update([
            'category' => 'Contributions',
        ]);
        DB::table('emails')->where('category', 'Orders & Donations')->update([
            'category' => 'Contributions',
        ]);

        DB::table('emails')->where('type', 'member_welcome')->update([
            'name' => 'Supporter: Welcome',
            'hint' => 'Sent to your supporters when they first create a profile.',
        ]);
        DB::table('emails')->where('type', 'member_profile_update')->update([
            'name' => 'Supporter: Updated',
            'hint' => 'Sent to your supporters when they update their profile.',
        ]);
        DB::table('emails')->where('type', 'member_password_reset')->update([
            'name' => 'Supporter: Password Reset',
            'hint' => 'Sent to your supporters when they need to reset their password.',
        ]);

        DB::table('emails')->where('type', 'customer_pledge_received')->update([
            'name' => 'Pledge Received: To Supporter',
            'hint' => 'Sent to all supporters who make a pledge through your site confirming their pledge was successfully processed.',
        ]);
        DB::table('emails')->where('type', 'customer_order_received')->update([
            'hint' => 'Sent to all supporters who process a contribution through your site confirming their contribution was successfully processed.',
        ]);
        DB::table('emails')->where('type', 'customer_downloads')->update([
            'hint' => 'Sent to supporters who have purchased digital downloads. This email contains the secured download URLS.',
        ]);

        DB::table('emails')->where('type', 'customer_recurring_payment_failure')->update([
            'hint' => 'Sent to supporters whose recurring payment failed.',
        ]);
        DB::table('emails')->where('type', 'customer_recurring_payment_success')->update([
            'hint' => 'Sent to supporters whose recurring payment succeeded.',
        ]);
        DB::table('emails')->where('type', 'customer_payment_method_expiring')->update([
            'hint' => 'Sent to supporters whose recurring payment method is expiring soon.',
        ]);
        DB::table('emails')->where('type', 'customer_payment_method_expired')->update([
            'hint' => 'Sent to supporters whose recurring payment method has expired.',
        ]);
        DB::table('emails')->where('type', 'customer_tax_receipt')->update([
            'hint' => 'Sent to supporters after donating against a product that has tax receipts enabled. This email contains an attached tax receipt PDF.',
        ]);
        DB::table('emails')->where('type', 'customer_recurring_payment_reminder')->update([
            'hint' => 'Sent to supporters three days before a recurring payment is processed.',
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
