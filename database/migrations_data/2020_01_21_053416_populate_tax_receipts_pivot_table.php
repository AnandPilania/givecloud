<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PopulateTaxReceiptsPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $taxReceiptId = DB::table('tax_receipt_templates')->insertGetId([
            'is_default'    => 1,
            'template_type' => 'template',
            'name'          => 'Default tax receipt template',
            'body'          => sys_get('tax_receipt_template'),
            'created_at'    => sys_get()->modified('tax_receipt_template'),
            'updated_at'    => sys_get()->modified('tax_receipt_template'),
        ]);

        $revisionId = DB::table('tax_receipt_templates')->insertGetId([
            'template_type' => 'revision',
            'parent_id'     => $taxReceiptId,
            'name'          => 'Default tax receipt template',
            'body'          => sys_get('tax_receipt_template'),
            'created_at'    => sys_get()->modified('tax_receipt_template'),
            'updated_at'    => sys_get()->modified('tax_receipt_template'),
        ]);

        DB::table('tax_receipt_templates')
            ->where('id', $taxReceiptId)
            ->update([
                'latest_revision_id' => $revisionId,
            ]);

        DB::table('tax_receipts')
            ->update([
                'tax_receipt_template_id' => $revisionId,
            ]);

        DB::table('tax_receipts as r')
            ->leftJoin('productorder as o', 'o.id', '=', 'r.order_id')
            ->leftJoin('transactions as t', 't.id', '=', 'r.transaction_id')
            ->leftJoin('recurring_payment_profiles as rpp', 'rpp.id', '=', 't.recurring_payment_profile_id')
            ->update([
                'r.account_id'    => DB::raw('ifnull(o.member_id, rpp.member_id)'),
                'r.currency_code' => DB::raw('ifnull(o.currency_code, rpp.currency_code)'),
            ]);

        DB::table('tax_receipts as r')
            ->whereNotNull('r.deleted_at')
            ->update([
                'r.status' => 'void',
                'r.voided_at' => DB::raw('r.deleted_at'),
                'r.voided_by' => DB::raw('r.deleted_by'),
                'r.deleted_at' => null,
                'r.deleted_by' => null,
            ]);

        DB::statement("
            INSERT INTO tax_receipt_line_items (tax_receipt_id, order_id, description, amount, currency_code, donated_at)
            SELECT id, order_id, '', amount, currency_code, issued_at FROM tax_receipts WHERE order_id IS NOT NULL
        ");

        DB::statement("
            INSERT INTO tax_receipt_line_items (tax_receipt_id, transaction_id, description, amount, currency_code, donated_at)
            SELECT id, transaction_id, '', amount, currency_code, issued_at FROM tax_receipts WHERE transaction_id IS NOT NULL
        ");

        DB::table('tax_receipt_line_items as i')
            ->join('productorder as o', 'o.id', '=', 'i.order_id')
            ->update([
                'i.description' => DB::raw("CONCAT('Order #', o.client_uuid)"),
            ]);

        DB::table('tax_receipt_line_items as i')
            ->join('transactions as t', 't.id', '=', 'i.transaction_id')
            ->join('recurring_payment_profiles as rpp', 'rpp.id', '=', 't.recurring_payment_profile_id')
            ->update([
                'i.description' => DB::raw("CONCAT('Recurring Payment #', rpp.profile_id, '-', t.id)"),
            ]);
    }
}
