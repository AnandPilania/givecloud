<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Http\Controllers\Controller;
use Ds\Models\Variant;
use Illuminate\Support\Facades\DB;

class CheckInController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('reports.check_ins');

        $__menu = 'reports.checkins';

        $title = 'Check-Ins';

        pageSetup($title, 'jpanel');

        $check_ins = DB::select('SELECT p.id AS product_id,
                p.code AS product_code,
                p.name AS product_name,
                v.variantname AS variant_name,
                v.id AS variant_id,
                IFNULL(x.check_ins,0) AS unique_check_ins,
                x.first_check_in,
                x.last_check_in,
                IFNULL(x2.ticket_count,0) AS ticket_count,
                IFNULL(x2.checked_in_count,0) AS checked_in_count,
                IFNULL(x2.multi_checked_in_count,0) AS multi_checked_in_count
            FROM product p
            INNER JOIN productinventory v ON v.productid = p.id
            LEFT JOIN (SELECT i.productinventoryid,
                        COUNT(*) AS check_ins,
                        MIN(check_in_at) AS first_check_in,
                        MAX(check_in_at) AS last_check_in
                    FROM ticket_check_in ch
                    INNER JOIN productorderitem i ON i.id = ch.order_item_id
                    INNER JOIN productorder o ON o.id = i.productorderid
                    WHERE o.is_processed = 1 and o.deleted_at is null
                    GROUP BY i.productinventoryid) x
                ON x.productinventoryid = v.id
            LEFT JOIN (SELECT i.productinventoryid,
                        COUNT(*) AS ticket_count,
                        SUM(CASE WHEN IFNULL(x3.check_ins,0) > 0 THEN 1 ELSE 0 END) AS checked_in_count,
                        SUM(CASE WHEN IFNULL(x3.check_ins,0) > 1 THEN 1 ELSE 0 END) AS multi_checked_in_count
                    FROM productorderitem i
                    INNER JOIN productorder o ON o.id = i.productorderid
                    LEFT JOIN (SELECT ch.order_item_id, COUNT(*) AS check_ins
                                FROM ticket_check_in ch
                                GROUP BY ch.order_item_id) x3
                        ON x3.order_item_id = i.id
                    WHERE o.is_processed = 1 and o.deleted_at is null
                    GROUP BY i.productinventoryid) x2
                ON x2.productinventoryid = v.id
            WHERE p.allow_check_in = 1
                AND p.deleted_at IS NULL
                AND (v.is_deleted = 0 OR x2.ticket_count > 0)
                AND p.type IS NULL
            ORDER BY p.publish_start_date');

        return $this->getView('reports/check_ins/all', compact('__menu', 'title', 'check_ins'));
    }

    public function export()
    {
        user()->canOrRedirect('reports.check_ins');

        $check_ins = DB::select('SELECT p.id AS product_id,
                p.code AS product_code,
                p.name AS product_name,
                v.variantname AS variant_name,
                v.id AS variant_id,
                IFNULL(x.check_ins,0) AS unique_check_ins,
                x.first_check_in,
                x.last_check_in,
                IFNULL(x2.ticket_count,0) AS ticket_count,
                IFNULL(x2.checked_in_count,0) AS checked_in_count,
                IFNULL(x2.multi_checked_in_count,0) AS multi_checked_in_count
            FROM product p
            INNER JOIN productinventory v ON v.productid = p.id
            LEFT JOIN (SELECT i.productinventoryid,
                        COUNT(*) AS check_ins,
                        MIN(check_in_at) AS first_check_in,
                        MAX(check_in_at) AS last_check_in
                    FROM ticket_check_in ch
                    INNER JOIN productorderitem i ON i.id = ch.order_item_id
                    INNER JOIN productorder o ON o.id = i.productorderid
                    WHERE o.is_processed = 1 and o.deleted_at is null
                    GROUP BY i.productinventoryid) x
                ON x.productinventoryid = v.id
            LEFT JOIN (SELECT i.productinventoryid,
                        COUNT(*) AS ticket_count,
                        SUM(CASE WHEN IFNULL(x3.check_ins,0) > 0 THEN 1 ELSE 0 END) AS checked_in_count,
                        SUM(CASE WHEN IFNULL(x3.check_ins,0) > 1 THEN 1 ELSE 0 END) AS multi_checked_in_count
                    FROM productorderitem i
                    INNER JOIN productorder o ON o.id = i.productorderid
                    LEFT JOIN (SELECT ch.order_item_id, COUNT(*) AS check_ins
                                FROM ticket_check_in ch
                                GROUP BY ch.order_item_id) x3
                        ON x3.order_item_id = i.id
                    WHERE o.is_processed = 1 and o.deleted_at is null
                    GROUP BY i.productinventoryid) x2
                ON x2.productinventoryid = v.id
            WHERE p.allow_check_in = 1
                AND p.deleted_at IS NULL
            ORDER BY p.publish_start_date');

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=check_ins.csv');
        header('Expires: 0');
        header('Pragma: public');
        $out_file = fopen('php://output', 'w');
        fputcsv($out_file, ['Code', 'Product', 'Variant', 'Ticket Count', 'Checked-in(Once)', 'Checked-in(Multiple)', 'Checked-in(Not Yet)', 'First Check-In', 'Last Check-In']);
        foreach ($check_ins as $row) {
            fputcsv($out_file, [
                $row->product_code,
                $row->product_name,
                $row->variant_name,
                $row->ticket_count,
                $row->checked_in_count,
                $row->multi_checked_in_count,
                $row->ticket_count - $row->checked_in_count,
                toLocalFormat($row->first_check_in, 'csv'),
                toLocalFormat($row->last_check_in, 'csv'),
            ]);
        }
        fclose($out_file);
        exit;
    }

    public function audit()
    {
        user()->canOrRedirect('reports.check_ins');

        $__menu = 'reports.checkins';

        $variant_id = request('i');

        $inventory = Variant::findOrFail($variant_id);

        $title = 'Check-Ins: ' . $inventory->product->name . ' (' . $inventory->variantname . ')';

        pageSetup($title, 'jpanel');

        $check_ins = DB::select(
            'SELECT o.id AS order_id,
                i.id AS order_item_id,
                o.confirmationdatetime AS ordered_at,
                o.invoicenumber AS order_number,
                o.billing_first_name,
                o.billing_last_name,
                o.shipping_first_name,
                o.shipping_last_name,
                IFNULL(x2.check_ins,0) AS check_ins,
                x2.first_check_in,
                x2.last_check_in
            FROM productorderitem i
            INNER JOIN productorder o ON o.id = i.productorderid
            INNER JOIN productinventory iv ON iv.id = i.productinventoryid
            INNER JOIN product p ON p.id = iv.productid
            LEFT JOIN (SELECT ch.order_item_id,
                        COUNT(*) AS check_ins,
                        MIN(check_in_at) AS first_check_in,
                        MAX(check_in_at) AS last_check_in
                    FROM ticket_check_in ch
                    GROUP BY ch.order_item_id) x2
                ON x2.order_item_id = i.id
            WHERE p.allow_check_in = 1
                AND o.is_processed = 1
                AND o.deleted_at is null
                AND iv.id = ?
                AND p.deleted_at IS NULL
            ORDER BY o.billing_last_name, o.billing_first_name',
            [(int) $variant_id]
        );

        return $this->getView('reports/check_ins/audit', compact('__menu', 'variant_id', 'inventory', 'title', 'check_ins'));
    }

    public function audit_export()
    {
        user()->canOrRedirect('reports.check_ins');

        $variant_id = request('i');

        $inventory = Variant::findOrFail($variant_id);

        $filename = $inventory->product->name;
        if ($inventory->variantname) {
            $filename = $filename . $inventory->variantname;
        }

        $check_ins = DB::select(
            'SELECT o.id AS order_id,
                i.id AS order_item_id,
                o.confirmationdatetime AS ordered_at,
                o.invoicenumber AS order_number,
                o.billing_first_name,
                o.billing_last_name,
                o.shipping_first_name,
                o.shipping_last_name,
                IFNULL(x2.check_ins,0) AS check_ins,
                x2.first_check_in,
                x2.last_check_in
            FROM productorderitem i
            INNER JOIN productorder o ON o.id = i.productorderid
            INNER JOIN productinventory iv ON iv.id = i.productinventoryid
            INNER JOIN product p ON p.id = iv.productid
            LEFT JOIN (SELECT ch.order_item_id,
                        COUNT(*) AS check_ins,
                        MIN(check_in_at) AS first_check_in,
                        MAX(check_in_at) AS last_check_in
                    FROM ticket_check_in ch
                    GROUP BY ch.order_item_id) x2
                ON x2.order_item_id = i.id
            WHERE p.allow_check_in = 1
                AND o.is_processed = 1
                AND o.deleted_at is null
                AND iv.id = ?
                AND p.deleted_at IS NULL
            ORDER BY o.billing_last_name, o.billing_first_name',
            [(int) $variant_id]
        );

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=check_ins-' . sanitize_filename($filename) . '.csv');
        header('Expires: 0');
        header('Pragma: public');
        $out_file = fopen('php://output', 'w');
        fputcsv($out_file, ['Contribution', 'Bill-To', 'Ship-To', 'Checked-In', 'Check-In Count', 'Last Check-In']);
        foreach ($check_ins as $row) {
            fputcsv($out_file, [
                $row->order_number,
                $row->billing_first_name . ' ' . $row->billing_last_name,
                $row->shipping_first_name . ' ' . $row->shipping_last_name,
                ($row->check_ins > 0) ? 'Yes' : '',
                $row->check_ins,
                toLocalFormat($row->last_check_in, 'csv'),
            ]);
        }
        fclose($out_file);
        exit;
    }
}
