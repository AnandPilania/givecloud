
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?= e($title) ?></h1>
    </div>
</div>

<div class="alert alert-info">
    <i class="fa fa-exclamation-circle"></i> This data was polled directly from DonorPerfect on <?= e(toLocalFormat('now', 'fdatetime')) ?>.
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Gift</th>
                        <th>Ref#</th>
                        <th>Donor</th>
                        <th>GL</th>
                        <th>Solicit</th>
                        <th>Sub-Solicit</th>
                        <th>Campaign</th>
                        <th style="text-align:right;">Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($xqryGifts as $i => $gift) {
                            $first_name = $gift->first_name;
                            $last_name = $gift->last_name;
                            $donor_id = $gift->donor_id;
                            $gift_date = $gift->gift_date;
                            $gift_id = $gift->gift_id;
                            $amount = $gift->amount;
                            $solicit_code = $gift->solicit_code;
                            $sub_solicit_code = $gift->sub_solicit_code;
                            $gl_code = $gift->gl_code;
                            $campaign = $gift->campaign;
                            $reference = $gift->reference;
                            echo '<tr>';
                                echo '<td><a href="javascript:void(0);" onclick="j.dpgift.show({id:'.$gift_id.'});"><i class="fa fa-search"></i></a></td>';
                                echo '<td><a href="' . route('backend.orders.edit_without_id', ['gift' => $gift_id]) . '">'.$gift_id.'</a></td>';
                                echo '<td>'.$reference.'</td>';
                                echo '<td>'.$first_name.' '.$last_name.' ('.$donor_id.')</td>';
                                echo '<td>'.$gl_code.'</td>';
                                echo '<td>'.$solicit_code.'</td>';
                                echo '<td>'.$sub_solicit_code.'</td>';
                                echo '<td>'.$campaign.'</td>';
                                echo '<td style="text-align:right;">'.number_format($amount,2).'</td>';
                                echo '<td data-order="'.toLocalFormat($gift_date,'U').'">'.fdate($gift_date).'</td>';
                            echo '</tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
