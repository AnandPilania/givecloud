
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
                        <th>Donor</th>
                        <th>Gifts</th>
                        <th style="text-align:right;">Total Given</th>
                        <th style="text-align:right;">Max Given</th>
                        <th>First Gift Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($xqryGifts as $i => $gift) {
                            $first_name = $gift->first_name;
                            $last_name = $gift->last_name;
                            $donor_id = $gift->donor_id;
                            $giftcount = $gift->giftcount;
                            $totalamount = $gift->totalamount;
                            $maxamount = $gift->maxamount;
                            $firstgiftdate = $gift->firstgiftdate;
                            echo '<tr>';
                                echo '<td><a href="javascript:void(0);" onclick="j.dpdonor.show({id:'.$donor_id.'});"><i class="fa fa-search"></i></a></td>';
                                echo '<td>'.$first_name.' '.$last_name.' ('.$donor_id.')</td>';
                                echo '<td>'.number_format($giftcount).'</td>';
                                echo '<td style="text-align:right;">'.number_format($totalamount,2).'</td>';
                                echo '<td style="text-align:right;">'.number_format($maxamount,2).'</td>';
                                echo '<td data-order="'.toLocalFormat($firstgiftdate,'U').'">'.toLocalFormat($firstgiftdate, 'fdate').'</td>';
                            echo '</tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
