<div class="modal fade modal-info" id="taxes-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h3 style="margin-top:10px; margin-bottom:20px; margin-left:5px; color:#666; font-weight:light; ">
                    Taxes
                    @if ($order->is_pos)
                    <small class="text-sm text-muted">({{ $order->taxable_address }})</small>
                    @endif
                </h3>
                <div class="table-responsive">
                    <table class="table" style="margin-bottom:10px;">
                        <thead>
                        <tr>
                            <th width="80">Code</th>
                            <th>Products</th>
                            <th width="80" style="text-align:right;">Cost ($)</th>
                            <th width="80" style="text-align:right;">Rate (%)</th>
                            <th width="80" style="text-align:right;">Tax ({{ $order->currency->unique_symbol }})</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                        // taxes
                        $qT = db_query(sprintf("SELECT p.id AS productid, iv.id AS productinventoryid, it.taxid, it.amount AS taxamount, t.code, p.code AS productsku, p.name AS productname, (i.price*i.qty) AS amount, t.rate
                                        FROM productorderitemtax it
                                        INNER JOIN productorderitem i ON i.id = it.orderitemid
                                        INNER JOIN productinventory iv ON iv.id = i.productinventoryid
                                        INNER JOIN producttax t ON t.id = it.taxid
                                        INNER JOIN product p ON p.id = iv.productid
                                        WHERE i.productorderid = %d",
                            db_real_escape_string($order->id)
                        ));

                        // aggregate query
                        $taxQry = array();
                        while ($t = db_fetch_assoc($qT)) {
                            if (!isset($taxQry[$t['taxid']])) {
                                $taxQry[$t['taxid']] = array(
                                    'id' => 0,
                                    'code' => '',
                                    'product' => array(),
                                    'totalItems' => 0,
                                    'totalPaid' => 0
                                );
                            }
                            $arr = &$taxQry[$t['taxid']];

                            $arr['id'] = $t['taxid'];
                            $arr['code'] = $t['code'];
                            $arr['rate'] = $t['rate'];
                            array_push($arr['product'],'<a href="/jpanel/products/edit?i='.$t['productid'].'" title="'.$t['productname'].' ('.$t['productsku'].')">'.$t['productname'].'</a> ('.money($t['amount'], $order->currency).')');
                            $arr['totalItems'] += floatval($t['amount']);
                            $arr['totalPaid'] += floatval($t['taxamount']);
                        }

                        $totalTaxPaid_onItems = 0;
                        foreach ($taxQry as $tax) {
                            echo '<tr>';
                            echo '<td valign="top">'.$tax['code'].'</td>';
                            echo '<td valign="top">'.implode('<br />',$tax['product']).'</td>';
                            echo '<td valign="top" style="text-align:right;">'.money($tax['totalItems'], $order->currency).'</td>';
                            echo '<td valign="top" style="text-align:right;">'.$tax['rate'].'%'.'</td>';
                            echo '<td valign="top" style="text-align:right;">'.money($tax['totalPaid'], $order->currency).'</td>';
                            echo '</tr>';
                            $totalTaxPaid_onItems += $tax['totalPaid'];
                        }
                        @endphp

                        @if($order->shippable_items > 0)
                        <tr>
                            <td></td>
                            <td>Shipping</td>
                            <td style="text-align:right;">{{ money($order->shipping_amount, $order->currency) }}</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;">{{ money(bcsub($order->taxtotal, $totalTaxPaid_onItems, 2), $order->currency) }}</td>
                        </tr>
                        @endif
                        </tbody>
                        <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align:right;"{{ money($order->taxtotal, $order->currency) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
