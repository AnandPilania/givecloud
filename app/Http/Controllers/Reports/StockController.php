<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Http\Controllers\Controller;

class StockController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('reports.stock_levels');

        $__menu = 'reports.stock';

        $title = 'Product Stock Levels';

        pageSetup($title, 'jpanel');

        $query = 'SELECT iv.id,
                iv.productid,
                p.name,
                p.code,
                iv.variantname,
                sa.occurred_at AS quantitylastreportedat,
                sa.quantity AS quantitylastreported,
                (sa.quantity - iv.quantity) AS quantitypurchased,
                IFNULL(iv.quantityrestock,0) AS quantityrestock,
                IFNULL(iv.quantity,0) AS quantityremaining,
                (CASE WHEN iv.quantity <= IFNULL(iv.quantityrestock,0) THEN 1 ELSE 0 END) AS restockflag
            FROM productinventory iv
            INNER JOIN stock_adjustments sa ON sa.id = iv.last_physical_count_id
            INNER JOIN product p ON p.id = iv.productid
            WHERE p.deleted_at IS NULL AND iv.is_deleted = 0';

        // filters
        if (request('fc')) {
            $query .= sprintf(' AND p.id IN (SELECT productid FROM productcategorylink WHERE categoryid = %d)', db_real_escape_string(request('fc')));
        }

        if (request('fa')) {
            $query .= sprintf(" AND p.author = '%s'", db_real_escape_string(request('fa')));
        }

        // ordering
        $query .= ' ORDER BY (CASE WHEN iv.quantity <= IFNULL(iv.quantityrestock,0) THEN 1 ELSE 0 END) DESC, p.name';

        $qList = db_query($query);

        if (! $qList) {
            $qList_len = 0;
        } else {
            $qList_len = db_num_rows($qList);
        } // store the length

        return $this->getView('reports/stock', compact('__menu', 'title', 'query', 'qList', 'qList_len'));
    }

    public function export()
    {
        user()->canOrRedirect('reports.stock_levels');

        $__menu = 'reports.stock';

        $title = 'Product Stock Levels';

        pageSetup($title, 'jpanel');

        $query = 'SELECT iv.id,
                iv.productid,
                p.name,
                p.code,
                p.summary,
                iv.variantname,
                iv.price,
                iv.cost,
                iv.saleprice,
                sa.occurred_at AS quantitylastreportedat,
                sa.quantity AS quantitylastreported,
                (sa.quantity - iv.quantity) AS quantitypurchased,
                IFNULL(iv.quantityrestock,0) AS quantityrestock,
                IFNULL(iv.quantity,0) AS quantityremaining,
                (CASE WHEN iv.quantity <= IFNULL(iv.quantityrestock,0) THEN 1 ELSE 0 END) AS restockflag
            FROM productinventory iv
            INNER JOIN stock_adjustments sa ON sa.id = iv.last_physical_count_id
            INNER JOIN product p ON p.id = iv.productid
            WHERE p.deleted_at IS NULL AND iv.is_deleted = 0';

        // filters
        if (request('fc')) {
            $query .= sprintf(' AND p.id IN (SELECT productid FROM productcategorylink WHERE categoryid = %d)', db_real_escape_string(request('fc')));
        }

        if (request('fa')) {
            $query .= sprintf(" AND p.author = '%s'", db_real_escape_string(request('fa')));
        }

        // ordering
        $query .= ' ORDER BY (CASE WHEN iv.quantity <= IFNULL(iv.quantityrestock,0) THEN 1 ELSE 0 END) DESC, p.name';

        $qList = db_query($query);

        if (! $qList) {
            $qList_len = 0;
        } else {
            $qList_len = db_num_rows($qList);
        } // store the length

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=stock.csv');
        header('Expires: 0');
        header('Pragma: public');
        $out_file = fopen('php://output', 'w');
        fputcsv($out_file, ['Name', 'Code', 'Qty', 'Last Updated', 'Purchased', 'Remaining', 'Restock At', 'Price', 'Cost', 'Sale Price', 'Summary']);
        while ($row = db_fetch_object($qList)) {
            fputcsv($out_file, [
                $row->name . (($row->variantname) ? ' (' . $row->variantname . ')' : ''),
                $row->code,
                $row->quantitylastreported,
                toLocalFormat($row->quantitylastreportedat, 'csv'),
                $row->quantitypurchased,
                max(0, $row->quantityremaining),
                $row->quantityrestock,
                $row->price,
                $row->cost,
                $row->saleprice,
                $row->summary,
            ]);
        }
        fclose($out_file);
        exit;
    }
}
