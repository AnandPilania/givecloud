
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?= e($title) ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Customer</th>
                        <th>Donor ID</th>
                        <th>Last Contribution</th>
                        <th style="text-align:center;">Total Contributions</th>
                        <th style="text-align:right;">Total Amount</th>
                        <th style="text-align:center;">Total Products</th>
                        <th style="text-align:center;">Total Quantity</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    while ($i = db_fetch_assoc($qList)) {
                        echo '<tr>';
                            echo '<td><a href="' . route('backend.orders.index', ['d' => $i['alt_contact_id']]) . '"><i class="fa fa-search"></i></a></td>';
                            echo '<td>'.$i['billingname'].'</td>';
                            if (is_numeric($i['alt_contact_id']))
                                echo '<td><a href="javascript:void(0);" onclick="j.dpdonor.show({id:'.$i['alt_contact_id'].'});"><i class="fa fa-user"></i> '.$i['alt_contact_id'].'</td>';
                            else
                                echo '<td></td>';
                            echo '<td data-order="'.toLocalFormat($i['lastorderdate'],'U').'">'.toLocalFormat($i['lastorderdate'], 'fdatetime').'</td>';
                            echo '<td style="text-align:center;">'.$i['ordercount'].'</td>';
                            echo '<td style="text-align:right;">'.number_format($i['totalamount'],2).'</td>';
                            echo '<td style="text-align:center;">'.$i['totalproducts'].'</td>';
                            echo '<td style="text-align:center;">'.$i['totalquantity'].'</td>';
                        echo '</tr>';
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
