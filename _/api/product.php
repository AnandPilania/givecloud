<?php

function product_get_hidden_reasons($product)
{
    $reasons = [];

    if ($product->isenabled == 0) {
        $reasons[] = 'This item is set to NOT display on the web. Check the \'Options\' panel.';
    }

    if ($product->deleted_at !== null) {
        $reasons[] = 'This item has been deleted.';
    }

    if (count($product->categories) === 0 && in_array((string) $product->template_suffix, ['', 'product'], true)) {
        $reasons[] = 'This item does not belong to any categories.';
    }

    if ($product->publish_start_date !== null && $product->publish_start_date->gt(fromLocal('now'))) {
        $reasons[] = 'The active start date is set to ' . $product->publish_start_date->toDateString() . '.';
    }

    if ($product->publish_end_date !== null && $product->publish_end_date->lt(fromLocal('now'))) {
        $reasons[] = 'The active end date is set to ' . $product->publish_end_date->toDateString() . '.';
    }

    return $reasons;
}

function product_total_purchases($product_id)
{
    $qPurchases = db_query(sprintf(
        'SELECT p.id,
                p.code,
                p.name,
                p.goalamount,
                MIN(o.createddatetime) AS firstpurchasedatetime,
                MAX(o.createddatetime) AS lastpurchasedatetime,
                COUNT(*) AS ordercount,
                SUM(oi.qty) AS quantitypurchased,
                SUM(oi.qty*oi.price*o.functional_exchange_rate) AS total_amount
            FROM productorderitem oi
            INNER JOIN productorder o ON o.id = oi.productorderid AND o.is_processed = 1
            INNER JOIN productinventory iv ON iv.id = oi.productinventoryid
            INNER JOIN product p ON p.id = iv.productid
            WHERE p.id = %d
                AND o.is_processed = 1
                AND o.deleted_at IS NULL
            GROUP BY p.id, p.code, p.name, p.goalamount',
        db_real_escape_string($product_id)
    ));

    if ($qPurchases === false || db_num_rows($qPurchases) === 0) {
        return false;
    }

    return db_fetch_object($qPurchases);
}

function product_get_goal_progress($product, $goal = 0, $offline_donation = 0)
{
    $return_var = (object) [
        'goal_amount' => 0,
        'progress_amount' => 0,
        'progress_percent' => 0,
        'progress_count' => 0,
    ];

    // if using dpo data
    if ($product->goal_use_dpo == 1) {
        // query dpo for totals
        $gift_goal = dpo_gift_goal_progress([
            'gl_code' => $product->meta1,
            'campaign' => $product->meta2,
            'solicit_code' => $product->meta3,
            'sub_solicit_code' => $product->meta4,
        ]);

        // if no data is returned
        if (! $gift_goal) {
            return $return_var;
        }

        // compile data
        $return_var->goal_amount = (float) round($product->goalamount, 2);
        $return_var->progress_amount = (float) round($gift_goal->total_amount + $product->goal_progress_offset, 2);
        $return_var->progress_count = (int) $gift_goal->gift_count;

    // else using ds.com data
    } else {
        // find product purchase data
        $product_purchases = product_total_purchases($product->id);

        // compile data
        $return_var->goal_amount = money($product->goalamount, $product->base_currency)->toDefaultCurrency()->getAmount();
        $return_var->progress_amount = money($product->goal_progress_offset, $product->base_currency)->toDefaultCurrency()->getAmount() + ($product_purchases->total_amount ?? 0);
        $return_var->progress_count = $product_purchases->quantitypurchased ?? 0;
    }

    // add offline donations (if provided)
    if ($offline_donation && is_numeric($offline_donation) && $offline_donation > 0) {
        $return_var->progress_amount += $offline_donation;
    }

    // use offline goal (if provided)
    if ($goal && is_numeric($goal) && $goal > 0) {
        $return_var->goal_amount += $goal;
    }

    // calculate progress percent
    if ($return_var->goal_amount > 0) {
        $return_var->progress_percent = (float) round($return_var->progress_amount / $return_var->goal_amount, 2);
    }

    // don't let the progress exceed 100%
    if ($return_var->progress_percent > 1) {
        $return_var->progress_percent = 1;
    }

    // return json
    return $return_var;
}

function product_catCurs($parentid, $level = 0)
{
    $returnStr = '';
    $qNode = db_query(sprintf(
        "SELECT c.id,
                c.sequence,
                c.parent_id,
                c.name,
                LCASE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.name,')',''),'(',''),'''',''),'/',''),'&',''),'-',''),'~',''),'?',''),' ','')) AS url
            FROM productcategory c
            WHERE (c.parent_id = %d OR (0 = %d AND c.parent_id IS NULL))
            ORDER BY c.sequence",
        $parentid,
        $parentid
    ));
    while ($cat = db_fetch_object($qNode)) {
        $returnStr .= '<option value="' . $cat->id . '" ' . ((request('fc') == $cat->id) ? 'selected="selected"' : '') . '>';

        if ($level > 0) {
            $x = 0;
            while ($x < $level) {
                $returnStr .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                $x = $x + 1;
            } // add spaces
        }

        $returnStr .= e($cat->name) . '</option>';
        // recurs
        $returnStr .= product_catCurs($cat->id, $level + 1);
    }

    return $returnStr;
}
