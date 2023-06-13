<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateEmailsWithContributionLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $orderReceivedSupporter = DB::table('emails')->where('name', 'Order Received: To Customer')->first();
        if ($orderReceivedSupporter) {
            $orderReceivedSupporter->body_template = Str::replace('Order #', 'Contribution #', $orderReceivedSupporter->body_template);
            $orderReceivedSupporter->body_template = Str::replace('status of your order', 'status of your contribution', $orderReceivedSupporter->body_template);
            $orderReceivedSupporter->body_template = Str::replace('Track My Order', 'Track My Contribution', $orderReceivedSupporter->body_template);

            DB::table('emails')->where('id', $orderReceivedSupporter->id)->update([
                'name' => 'Contribution Received: To Supporter',
                'body_template' => $orderReceivedSupporter->body_template,
                'hint' => 'Sent to all supporters who process a contribution through your site confirming their contribution was successfully processed.',
            ]);
        }

        $orderReceivedAdmin = DB::table('emails')->where('name', 'Order Received: To Admin')->first();
        if ($orderReceivedAdmin) {
            $orderReceivedAdmin->body_template = Str::replace('Order #', 'Contribution #', $orderReceivedAdmin->body_template);
            $orderReceivedAdmin->body_template = Str::replace('order confirmation', 'contribution confirmation', $orderReceivedAdmin->body_template);
            $orderReceivedAdmin->body_template = Str::replace('View Order Details', 'View Contribution Details', $orderReceivedAdmin->body_template);

            DB::table('emails')->where('id', $orderReceivedAdmin->id)->update([
                'name' => 'Contribution Received: To Admin',
                'body_template' => $orderReceivedAdmin->body_template,
                'hint' => "Sent to your organization's team when a contribution is received.",
            ]);
        }

        DB::table('emails')->where('name', 'Order: eDownload Links')->update([
            'name' => 'Contribution: eDownload Links',
        ]);

        DB::table('emails')->where('subject', 'Thanks for your order!')->update([
            'subject' => 'Thanks for your contribution!',
        ]);

        DB::table('emails')->where('subject', 'Order Received (#[[order_number]])')->update([
            'subject' => 'Contribution Received (#[[order_number]])',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
