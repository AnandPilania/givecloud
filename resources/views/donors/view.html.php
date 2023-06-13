
<form>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs">
        <li class="active"><a href="#general" data-toggle="tab"><i class="fa fa-user fa-fw"></i> General</a></li>
        <?php if ($donor): ?>
            <li><a href="#gifts" data-toggle="tab"><i class="fa fa-gift fa-fw"></i> Recent Gifts <span class="badge"><?= e(count($gifts)) ?></span></a></li>
            <li><a href="#pledges" data-toggle="tab"><i class="fa fa-refresh fa-fw"></i> Pledges <span class="badge"><?= e(count($pledges)) ?></span></a></li>
        <?php endif; ?>
        <li class="donor-selection-tab hide"><a href="#donor-search" data-toggle="tab"><i class="fa fa-search fa-fw"></i> <?= e(($donor)?'Choose Another':'Find a Donor') ?>... <span class="label label-warning label-sm">NEW</span></a></li>
    </ul>

    <br />

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane fade in active" id="general">

            <div class="row">

                <?php if ($donor): ?>

                    <div class="form-group col-sm-2">
                        <label>ID</label>
                        <div class="form-control"><?= e($donor->donor_id) ?></div>
                    </div>

                    <div class="form-group col-sm-3">
                        <label>First Name</label>
                        <div class="form-control"><?= e($donor->first_name) ?></div>
                    </div>

                    <div class="form-group col-sm-4">
                        <label>Last Name</label>
                        <div class="form-control"><?= e($donor->last_name) ?></div>
                    </div>

                    <div class="form-group col-sm-3">
                        <label>Salutation</label>
                        <div class="form-control"><?= e($donor->salutation) ?></div>
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Email</label>
                        <div class="form-control"><?= e($donor->email) ?></div>
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Home Phone</label>
                        <div class="form-control"><?= e($donor->home_phone) ?></div>
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Business Phone</label>
                        <div class="form-control"><?= e($donor->business_phone) ?></div>
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Address</label>
                        <div class="form-control"><?= e($donor->address) ?></div>
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Address 2</label>
                        <div class="form-control"><?= e($donor->address2) ?></div>
                    </div>

                    <div class="form-group col-sm-6">
                        <label>City</label>
                        <div class="form-control"><?= e($donor->city) ?></div>
                    </div>

                    <div class="form-group col-sm-6">
                        <label>State</label>
                        <div class="form-control"><?= e($donor->state) ?></div>
                    </div>

                    <div class="form-group col-sm-6">
                        <label>ZIP</label>
                        <div class="form-control"><?= e($donor->zip) ?></div>
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Country</label>
                        <div class="form-control"><?= e($donor->country) ?></div>
                    </div>

                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="fa fa-4x fa-frown-o"></i><br />
                        This donor does not exist in DonorPerfect.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="gifts">

            <?php if(count($gifts) === 0): ?>
                <div class="text-center text-muted">
                    <i class="fa fa-4x fa-frown-o"></i><br />
                    This donor has no gifts recorded in DonorPerfect.
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-info text-center">
                            <i class="fa fa-question-circle"></i> These are the 20 most recent gifts as recorded in DonorPerfect.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <th class="text-muted">ID</th>
                                    <th>Date</th>
                                    <th>GL</th>
                                    <th>Solicitation</th>
                                    <th>Gift Type</th>
                                    <th>Reference</th>
                                    <th class="text-right">Amount</th>
                                </thead>
                                <tbody>
                                <?php foreach($gifts as $gift): ?>
                                    <tr>
                                        <td class="text-muted"><?= e($gift->gift_id) ?></td>
                                        <td><?= e($gift->gift_date) ?></td>
                                        <td><?= e($gift->gl_code) ?></td>
                                        <td><?= e($gift->solicit_code) ?></td>
                                        <td><?= e($gift->gift_type) ?></td>
                                        <td><?= e($gift->reference) ?></td>
                                        <td class="text-right"><strong><?= e(money($gift->amount, $gift->currency)) ?></strong></td>
                                    </tr>
                                <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <div class="tab-pane fade" id="pledges">

            <?php if(count($pledges) === 0): ?>
                <div class="text-center text-muted">
                    <i class="fa fa-4x fa-frown-o"></i><br />
                    This donor has no pledges recorded in DonorPerfect.
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <th class="text-muted">ID</th>
                                    <th>Start Date</th>
                                    <th>Frequency</th>
                                    <th>GL</th>
                                    <th>Solicitation</th>
                                    <th>Gift Type</th>
                                    <th>Reference</th>
                                    <th class="text-right">Amount</th>
                                </thead>
                                <tbody>
                                <?php foreach($pledges as $pledge): ?>
                                    <tr>
                                        <td class="text-muted"><?= e($pledge->gift_id) ?></td>
                                        <td><?= e($pledge->start_date) ?></td>
                                        <td><?= e($pledge->frequency) ?></td>
                                        <td><?= e($pledge->gl_code) ?></td>
                                        <td><?= e($pledge->solicit_code) ?></td>
                                        <td><?= e($pledge->gift_type) ?></td>
                                        <td><?= e($pledge->reference) ?></td>
                                        <td class="text-right"><strong><?= e(money($pledge->bill, $pledge->currency)) ?></strong></td>
                                    </tr>
                                <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="donor-search">

            <?php if(empty($donor_matches)): ?>
                <div class="text-center text-muted">
                    <i class="fa fa-4x fa-frown-o"></i><br />
                    No other closely matching donors in DonorPerfect.<br><small>Searched by first name, last name or email.</small>
                </div>
            <?php else: ?>
                <div class="row">

                    <div class="col-lg-12">
                        <h3 style="margin-top:5px; margin-bottom:20px;"><?= e(count($donor_matches)) ?> Close Matches <small>Closely matching first name, last name or email.</small></h3>
                    </div>

                    <?php foreach($donor_matches as $donor): ?>
                        <div class="col-sm-4 col-xs-6" style="height:140px; overflow:hidden;">
                            <i class="fa fa-user"></i> <?= e($donor->first_name) ?> <?= e($donor->last_name) ?> (<?= e($donor->donor_id) ?>)
                            <?php $address_formatted = address_format($donor->address, null, $donor->city, $donor->state, $donor->zip, $donor->country); ?>
                            <?php if(trim($address_formatted) != ''): ?>
                                <br><small class="text-muted"><?= e(str_replace(chr(10),', ',$address_formatted)) ?></small>
                            <?php endif; ?>
                            <?php if(trim($donor->email) != ''): ?>
                                <br><small class="text-muted"><i class="fa fa-envelope"></i> <?= e($donor->email) ?></small>
                            <?php endif; ?>
                            <br><small class="text-info">
                                <?php if($donor->gift_count > 0): ?>
                                    <?= e(number_format($donor->gift_count)) ?> gift<?= e(($donor->gift_count != 1)?'s':'') ?> totalling <?= e(money($donor->gift_total)) ?>
                                <?php else: ?>
                                    No gifts
                                <?php endif; ?>
                            </small>
                            <br><button class="btn btn-info btn-outline btn-xs donor-chooser" data-donor-id="<?= e($donor->donor_id) ?>"><i class="fa fa-check"></i> Choose Donor <?= e($donor->donor_id) ?></button>
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endif; ?>

        </div>

    </div>

</form>

<hr />
<small>
    <i class="fa fa-external-link-square"></i> Polled from DonorPerfect on <?= e(toLocalFormat('now', 'l, F j, Y \a\t g:ia T')) ?>.
</small>
