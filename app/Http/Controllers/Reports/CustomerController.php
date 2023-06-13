<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('reports.orders_by_customer');

        $__menu = 'reports.customer-orders';

        $title = 'Contributions by Customer';

        pageSetup($title, 'jpanel');

        $query = "SELECT CONCAT(TRIM(o.billing_first_name),' ',TRIM(o.billing_last_name)) AS billingname,
                alt_contact_id,
                MAX(createddatetime) AS lastorderdate,
                COUNT(*) AS ordercount,
                SUM(totalamount) AS totalamount,
                SUM(totalproducts) AS totalproducts,
                SUM(totalquantity) AS totalquantity
            FROM `productorder` o
            LEFT JOIN (SELECT o2.id AS orderid,
                            COUNT(*) AS totalproducts,
                            SUM(o2i.qty) AS totalquantity
                        FROM productorder o2
                        INNER JOIN productorderitem o2i ON o2i.productorderid = o2.id
                        WHERE o2.is_processed = 1 and o2.deleted_at is null
                        GROUP BY o2.id) t1
                ON t1.orderid = o.id
            WHERE o.is_processed = 1
                AND CONCAT(TRIM(o.billing_first_name),' ',TRIM(o.billing_last_name)) != ''
            GROUP BY CONCAT(TRIM(o.billing_first_name),' ',TRIM(o.billing_last_name)), alt_contact_id";

        // ordering
        $query .= " ORDER BY CONCAT(o.billing_first_name,' ',o.billing_last_name) ASC";

        $qList = db_query($query);
        $qList_len = $qList ? db_num_rows($qList) : 0;

        return $this->getView('reports/customer', compact('__menu', 'title', 'query', 'qList', 'qList_len'));
    }
}
