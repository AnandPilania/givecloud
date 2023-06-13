
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this sponsorship?');
        if (f) {
            document.sponsorship.action = '/jpanel/sponsorship/destroy';
            document.sponsorship.submit();
        }
    }
    function onRestore () {
        var f = confirm('Are you sure you want to restore (un-delete) this sponsorship?');
        if (f) {
            document.sponsorship.action = '/jpanel/sponsorship/restore';
            document.sponsorship.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <span class="page-header-text"><?= e($sponsorship->display_name) ?></span>
            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <?php if($sponsorship->userCan('edit') && $sponsorship->is_deleted != '1'): ?>
                    <a href="/sponsorship/<?= e($sponsorship->id) ?>" class="btn btn-info" target="_blank"><i class="fa fa-external-link fa-fw"></i><span class="hidden-sm hidden-xs"> View</span></a>
                    <a onclick="$('#sponsorship-form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-sm hidden-xs"> Save</span></a>
                    <?php if ($sponsorship->exists): ?>
                        <a onclick="<?= dangerouslyUseHTML($sponsorship->sponsor_count > 0 ? "$.alert('There are <strong>" .  $sponsorship->sponsor_count . " active sponsor(s)</strong> associated with this sponsorship. Please end all active sponsors before deleting this sponsorship.', 'danger', 'fa-times')" : "onDelete()") ?>;" class="btn btn-danger"><i class="fa fa-trash fa-fw"></i></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<?php if($sponsorship->is_deleted): ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> This <?= e(sys_get('syn_sponsorship_child')) ?> has been deleted. <button onclick="onRestore();" class="btn btn-xs btn-danger"><i class="fa fa-refresh"></i> Restore</button></div>
<?php endif; ?>

<form name="sponsorship" id="sponsorship-form" method="post" action="/jpanel/sponsorship/save" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($sponsorship->id) ?>" />

    <!-- Nav tabs -->
    <ul class="nav nav-tabs">
        <li class="active"><a href="#general" data-toggle="tab"><i class="fa fa-pencil fa-fw"></i> General</a></li>
        <li><a href="#bio" data-toggle="tab"><i class="fa fa-pencil-square-o fa-fw"></i> Biography</a></li>
        <li><a href="#timeline" data-toggle="tab"><i class="fa fa-history fa-fw"></i> Timeline</a></li>
        <?php if(dpo_is_enabled() && user()->can('admin.dpo')): ?>
            <li><a href="#dpo" data-toggle="tab"><img src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline"> DPO Integration</a></li>
        <?php endif; ?>
    </ul>

    <br />

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane fade in active" id="general">

            <div class="row">

                <div class="col-sm-7 col-lg-8">

                    <div class="panel panel-default">
                        <div class="panel-body">

                            <div class="bottom-gutter">
                                <div class="panel-sub-title"><i class="fa fa-user"></i> General</div>
                            </div>

                            <div class="form-horizontal">

                                <div class="form-group">
                                    <label for="reference_number" class="col-sm-5 col-md-4 col-lg-3 control-label">Reference #</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" name="reference_number" id="reference_number" value="<?= e($sponsorship->reference_number) ?>" maxlength="45" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="first_name" class="col-sm-5 col-md-4 col-lg-3 control-label">First Name</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="first_name" id="first_name" value="<?= e($sponsorship->first_name) ?>" maxlength="45" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="last_name" class="col-sm-5 col-md-4 col-lg-3 control-label">Last Name</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control" name="last_name" id="last_name" value="<?= e($sponsorship->last_name) ?>" maxlength="45" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="gender" class="col-sm-5 col-md-4 col-lg-3 control-label">Gender</label>
                                    <div class="col-sm-7">
                                        <label class="block">
                                            <input type="radio" name="gender" id="" value="M" <?= e(($sponsorship->gender == 'M') ? 'checked' : '') ?>> <i class="fa fa-fw fa-male text-info"></i> Male
                                        </label>
                                        <label class="block">
                                            <input type="radio" name="gender" id="" value="F" <?= e(($sponsorship->gender == 'F') ? 'checked' : '') ?>> <i class="fa fa-fw fa-female text-pink"></i> Female
                                        </label>
                                        <label class="block">
                                            <input type="radio" name="gender" id="" value="" <?= e(($sponsorship->gender == '') ? 'checked' : '') ?>> Unspecified
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="birth_date" class="col-sm-5 col-md-4 col-lg-3 control-label">Birth Date</label>
                                    <div class="col-sm-7">
                                        <div class="input-group input-group-transparent">
                                            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                            <input type="text" style="width:125px;" class="datePretty form-control" name="birth_date" id="birth_date" value="<?= e(fromUtcFormat($sponsorship->birth_date, 'M j, Y')) ?>" maxlength="20" />
                                        </div>
                                        <small><?php if($sponsorship->birth_date): ?><?= e($sponsorship->age) ?> yrs old <?php if($sponsorship->age >= sys_get('sponsorship_maturity_age')): ?><span class="label label-warning">Matured</span><?php endif; ?><?php endif; ?></small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="biography" class="col-sm-5 col-md-4 col-lg-3 control-label">Photo</label>
                                    <div class="col-sm-7">
                                        <div class="form-control-static">
                                            <?php if($sponsorship->featuredImage): ?>
                                                <img src="<?= e($sponsorship->featuredImage->thumbnail_url) ?>" width="80" style="float:left; margin-right:10px;" />
                                            <?php endif; ?>
                                            <?php if(!sys_get('sponsorship_database_name')): ?>
                                                <table>
                                                    <tr>
                                                        <td>Currently: </td>
                                                        <td><?php if ($sponsorship->featuredImage): echo '<a href="'.$sponsorship->featuredImage->public_url.'" target="_blank">'.$sponsorship->featuredImage->filename.'</a>'; else: echo 'No file uploaded'; endif; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Upload: </td>
                                                        <td><input type="file" name="image" style="width:140px;" /></td>
                                                    </tr>
                                                </table>
                                                <small>Note: Only JPEG, GIF and PNG upload is supported.</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="enrollment_date" class="col-sm-5 col-md-4 col-lg-3 control-label">Enrollment Date</label>
                                    <div class="col-sm-7">
                                        <div class="input-group input-group-transparent">
                                            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                            <input type="text" style="width:125px;" class="datePretty form-control" name="enrollment_date" id="enrollment_date" value="<?= e(fromUtcFormat($sponsorship->enrollment_date, 'M j, Y')) ?>" maxlength="20" />
                                        </div>
                                        <small><?php if($sponsorship->enrollment_date): ?>Waiting <?= e($sponsorship->months_waiting) ?> month(s).<br><?php endif; ?>The date this record entered your program. This is also the date since which this record has been waiting sponsorship.</small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-body">

                            <div class="bottom-gutter">
                                <div class="panel-sub-title"><i class="fa fa-unlock"></i> Public Fields</div>
                            </div>

                            <div class="form-horizontal">

                                <?php foreach($public_segments as $segment): ?>
                                    <div class="form-group">
                                        <label for="segment_<?= e($segment->id) ?>" class="col-sm-5 col-md-4 col-lg-3 control-label"><?= e($segment->name) ?></label>
                                        <div class="col-sm-7 col-md-8 col-lg-9">
                                            <?php if($segment->type === 'date'): ?>
                                                <div class="input-group input-group-transparent">
                                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                                    <input type="text" style="width:125px;" class="datePretty form-control" name="segments[<?= e($segment->id) ?>]" value="<?= e(fromDateFormat($sponsorship->segmentValue($segment), 'M j, Y')) ?>" maxlength="20" />
                                                </div>
                                            <?php elseif($segment->type === 'multi-select' || $segment->type === 'advanced-multi-select'): ?>
                                                <select class="form-control" name="segments[<?= e($segment->id) ?>][]" id="segment_<?= e($segment->id) ?>">
                                                    <option value=""></option>
                                                    <?php foreach($segment->items as $option): ?>
                                                        <option value="<?= e($option->id) ?>" <?= dangerouslyUseHTML(($sponsorship->hasSegmentItem($option)) ? 'selected="selected"' : '') ?>><?= e($option->name) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php if($segment->description): ?><small class="text-muted"><?= e($segment->description) ?></small><?php endif; ?>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="segments[<?= e($segment->id) ?>]" id="segment_<?= e($segment->id) ?>" value="<?= e($sponsorship->segmentValue($segment)) ?>" maxlength="450" />
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            </div>
                        </div>
                    </div>

                    <?php if ($sponsorship->privateSegments): ?>
                        <div class="panel panel-default">
                            <div class="panel-body">

                                <div class="bottom-gutter">
                                    <div class="panel-sub-title"><i class="fa fa-lock"></i> Private Fields</div>
                                </div>

                                <div class="form-horizontal">

                                    <?php foreach($private_segments as $segment): ?>
                                        <div class="form-group">
                                            <label for="segment_<?= e($segment->id) ?>" class="col-sm-5 col-md-4 col-lg-3 control-label"><?= e($segment->name) ?></label>
                                            <div class="col-sm-7 col-md-8 col-lg-9">
                                                <?php if($segment->type === 'date'): ?>
                                                    <div class="input-group input-group-transparent">
                                                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                                        <input type="text" style="width:125px;" class="datePretty form-control" name="segments[<?= e($segment->id) ?>]" value="<?= e(fromDateFormat($sponsorship->segmentValue($segment), 'M j, Y')) ?>" maxlength="20" />
                                                    </div>
                                                <?php elseif($segment->type === 'multi-select' || $segment->type === 'advanced-multi-select'): ?>
                                                    <select class="form-control" name="segments[<?= e($segment->id) ?>][]" id="segment_<?= e($segment->id) ?>">
                                                        <option value=""></option>
                                                        <?php foreach($segment->items as $option): ?>
                                                            <option value="<?= e($option->id) ?>" <?= dangerouslyUseHTML(($sponsorship->hasSegmentItem($option)) ? 'selected="selected"' : '') ?>><?= e($option->name) ?></option>
                                                        <?php endforeach ?>
                                                    </select>
                                                    <?php if($segment->description): ?><small class="text-muted"><?= e($segment->description) ?></small><?php endif; ?>
                                                <?php else: ?>
                                                    <input type="text" class="form-control" name="segments[<?= e($segment->id) ?>]" id="segment_<?= e($segment->id) ?>" value="<?= e($sponsorship->segmentValue($segment)) ?>" maxlength="450" />
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="col-sm-5 col-lg-4">

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Status
                        </div>
                        <div class="panel-body">
                            <div class="form-horizontal">

                                <div class="form-group">
                                    <label for="sponsored_status" class="col-sm-5 control-label">Sponsored</label>
                                    <div class="col-sm-7">
                                        <select class="form-control" name="sponsored_status" id="sponsored_status">
                                            <option value="2">Auto <?php if($sponsorship->is_sponsored_auto): ?> (<?= e(($sponsorship->is_sponsored) ? 'Yes' : 'No') ?>)<?php endif; ?></option>
                                            <option value="1" <?= dangerouslyUseHTML((!$sponsorship->is_sponsored_auto && $sponsorship->is_sponsored) ? 'selected="selected"' : '') ?> >Yes</option>
                                            <option value="0" <?= dangerouslyUseHTML((!$sponsorship->is_sponsored_auto && !$sponsorship->is_sponsored) ? 'selected="selected"' : '') ?> >No</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="payment_option_group_id" class="col-sm-5 control-label">Pay Options</label>
                                    <div class="col-sm-7">
                                        <select class="form-control" name="payment_option_group_id" id="payment_option_group_id">
                                            <?php foreach($payment_options as $option): ?>
                                                <option value="<?= e($option->id) ?>" <?= dangerouslyUseHTML(($sponsorship->payment_option_group && $option->id == $sponsorship->payment_option_group->id) ? 'selected="selected"' : '') ?> ><?= e($option->name) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="payment_option_group_id" class="col-sm-5 control-label">On Web</label>
                                    <div class="col-sm-7">
                                        <input type="checkbox" class="switch" value="1" name="is_enabled" <?= e(($sponsorship->is_enabled) ? 'checked' : '') ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if(user()->can('sponsor.view')): ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?php $active_sponsors = $sponsorship->sponsors->filter(function($itm){ return !isset($itm->ended_at); }); ?>
                                <?php $past_sponsors = $sponsorship->sponsors->filter(function($itm){ return isset($itm->ended_at); }); ?>
                                Sponsors <?php if($sponsorship->sponsor_count > 0): ?><span class="badge"><?= e($sponsorship->sponsor_count) ?></span><?php endif; ?>

                                <?php if(user()->can('sponsor.add')): ?>
                                    <a href="#" class="btn btn-info btn-xs ds-sponsor pull-right" data-sponsorship-id="<?= e($sponsorship->id) ?>"><i class="fa fa-user-plus"></i> Add Sponsor</a>
                                <?php endif; ?>
                            </div>
                            <div class="panel-body">
                                <div class="form-horizontal">

                                    <div style="max-height:180px; overflow-y:auto;">
                                        <?php if($sponsorship->sponsor_count > 0): ?>

                                            <?php if($active_sponsors->count() > 0): ?>
                                                <?php $i = 0; foreach($active_sponsors as $sponsor): ?>
                                                    <?php if (empty($sponsor->member)) continue; ?>

                                                    <div class="child-list-item">
                                                        <div class="bumper"><?= e($sponsor->recurringPaymentProfile->payment_string ?? $sponsor->orderItem->payment_string ?? null) ?></div>
                                                        <div class="headline">
                                                            <a
                                                                href="<?= e($sponsor->member->id ? route('backend.member.edit', $sponsor->member->getKey()) : '#') ?>"
                                                                class="ds-sponsor"
                                                                data-sponsor-id="<?= e($sponsor->id) ?>">
                                                                <i class="fa fa-user<?= e($sponsor->is_ended ? '-times' : '') ?>"></i>
                                                                <?= e($sponsor->member->display_name) ?>
                                                            </a>
                                                        </div>
                                                        <div class="meta1">
                                                            <?php if ($sponsor->is_ended): ?>
                                                                Ended <?= e($sponsor->ended_at) ?> (<?= e($sponsor->started_at->diffForHumans($sponsor->ended_at, true)) ?> long)
                                                            <?php else: ?>
                                                                Started <?= e($sponsor->started_at) ?>
                                                            <?php endif; ?>

                                                            <?php if($sponsor->orderItem): ?>
                                                                (Contribution <a href="<?= e(route('backend.orders.edit', $sponsor->orderItem->order)) ?>">#<?= e($sponsor->orderItem->order->invoicenumber) ?></a>)
                                                            <?php elseif($sponsor->source): ?>
                                                                (via <?= e($sponsor->source) ?>)
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                <?php $i++; endforeach; ?>
                                            <?php else: ?>
                                                <div class="text-muted text-center"><i class="fa fa-exclamation-triangle"></i> No local sponsors.</div>
                                            <?php endif; ?>

                                            <?php if (sys_get('sponsorship_database_name') && $active_sponsors->count() < $sponsorship->sponsor_count): ?>
                                                <?php $other_sponsor_count = $sponsorship->sponsor_count - $active_sponsors->count(); ?>
                                                <hr>
                                                <div class="text-info text-center"><i class="fa fa-info-circle"></i> There <?= e(($other_sponsor_count != 1)?'are':'is') ?> <strong><?= e($other_sponsor_count) ?></strong> active sponsor<?= e(($other_sponsor_count != 1)?'s in other databases':' in another database') ?>.</div>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <div class="text-muted text-center"><i class="fa fa-exclamation-triangle"></i> No sponsors.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <?php if($past_sponsors->count() > 0): ?>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Past Sponsors
                                </div>
                                <div class="panel-body">
                                    <div class="form-horizontal">

                                        <div style="max-height:180px; overflow-y:auto;">

                                            <?php $i = 0; foreach($past_sponsors as $sponsor): ?>

                                                <div class="child-list-item">
                                                    <div class="bumper"><?= e($sponsor->recurringPaymentProfile->payment_string ?? $sponsor->orderItem->payment_string ?? null) ?></div>
                                                    <div class="headline">
                                                        <?php if ($sponsor->member): ?>
                                                        <a
                                                            href="<?= e(route('backend.member.edit', $sponsor->member->getKey())) ?>"
                                                            class="ds-sponsor"
                                                            data-sponsor-id="<?= e($sponsor->getKey()) ?>">
                                                            <i class="fa fa-user"></i>
                                                            <?= e($sponsor->member->display_name) ?>
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="meta1">
                                                        <?php if ($sponsor->is_ended): ?>
                                                            Ended <?= e($sponsor->ended_at) ?> (<?= e($sponsor->started_at->diffForHumans($sponsor->ended_at, true)) ?> long)
                                                        <?php else: ?>
                                                            Started <?= e($sponsor->started_at) ?>
                                                        <?php endif; ?>

                                                        <?php if($sponsor->orderItem): ?>
                                                            (Contribution <a href="<?= e(route('backend.orders.edit', $sponsor->orderItem->order)) ?>">#<?= e($sponsor->orderItem->order->invoicenumber) ?></a>)
                                                        <?php elseif($sponsor->source): ?>
                                                            (via <?= e($sponsor->source) ?>)
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                            <?php $i++; endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Location
                            <div class="pull-right">
                                <?php if($sponsorship->userCan('edit')): ?>
                                    <a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$(this).toggle(); $('#google_search_form').toggle(); $('#location_search').focus(); return false;"><i class="fa fa-search"></i> Search a Location</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="panel-body" style="padding:0px;">

                            <fieldset class="gllpLatlonPicker" id="custom_id">

                                <div class="form-group" id="google_search_form" style="padding:20px; display:none;">
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-search"></i></div>
                                        <input type="text" id="location_search" class="form-control gllpSearchField">
                                        <div class="input-group-addon btn gllpSearchButton">Find</div>
                                    </div>
                                </div>

                                <div class="gllpMap">Google Maps</div>
                                <input type="hidden" class="gllpLatitude" name="latitude" value="<?= e(($sponsorship === false) ? 38 : $sponsorship->latitude) ?>"/>
                                <input type="hidden" class="gllpLongitude" name="longitude" value="<?= e(($sponsorship === false) ? -100 : $sponsorship->longitude) ?>"/>
                                <input type="hidden" class="gllpZoom" value="2"/>
                                <div style="font-size:10px; color:#999;">Lat: <span class="gllpLatitude_html"><?= e(($sponsorship === false) ? 38 : $sponsorship->latitude) ?></span>; Long: <span class="gllpLongitude_html"><?= e(($sponsorship === false) ? -100 : $sponsorship->longitude) ?></span>;<br /></div>
                            </fieldset>

                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Private Notes
                        </div>
                        <div class="panel-body">

                            <textarea style="height:170px;" class="form-control" name="private_notes" id="private_notes"><?= dangerouslyUseHTML($sponsorship->private_notes) ?></textarea>

                        </div>
                    </div>

                </div>

            </div>

            <?php if ($sponsorship->exists) { ?>
                <hr />
                <small>
                    Created by <?= e($sponsorship->created_by_name) ?> on <?= e($sponsorship->created_at) ?> EST.<br />
                    Last modified by <?= e($sponsorship->updated_by_name) ?> on <?= e($sponsorship->updated_at) ?> EST.
                </small>
            <?php } ?>

        </div>

        <div class="tab-pane fade" id="bio">

            <textarea class="form-control html max-height" style="width:100%; height:500px;" name="biography" id="biography" ><?= e($sponsorship->biography) ?></textarea>

        </div>
        <div class="tab-pane fade" id="timeline">

            <?php if($sponsorship->exists): ?>
                <div class="timelinify" data-timeline-type="sponsorship" data-timeline-id="<?= e($sponsorship->id) ?>"></div>
            <?php else: ?>
                <p class="text-muted text-center"><br><br><i class="fa fa-exclamation-circle fa-3x"></i><br>You must save this record before you can add timeline posts.</p>
            <?php endif; ?>

        </div>

        <?php if(user()->can('admin.dpo')): ?>

            <div class="tab-pane fade" id="dpo">

                <div class="panel panel-info ">
                    <div class="panel-heading">
                        <img src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline"> DonorPerfect Integration

                        <a href="#" class="dpo-codes-refresh btn btn-info btn-xs pull-right"><i class="fa fa-refresh fa-fw"></i> Refresh DonorPerfect Codes</a>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meta1">General Ledger</label>
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="GL_CODE" name="meta1" id="meta1" value="<?= e($sponsorship->meta1) ?>" maxlength="200" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meta2">Campaign</label>
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="CAMPAIGN" name="meta2" id="meta2" value="<?= e($sponsorship->meta2) ?>" maxlength="200" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meta3">Solicitation</label>
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="SOLICIT_CODE" name="meta3" id="meta3" value="<?= e($sponsorship->meta3) ?>" maxlength="200" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meta4">Sub Solicitation</label>
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="SUB_SOLICIT_CODE" name="meta4" id="meta4" value="<?= e($sponsorship->meta4) ?>" maxlength="200" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meta5">Type of Gift</label>
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="GIFT_TYPE" name="meta5" id="meta5" value="<?= e($sponsorship->meta5) ?>" maxlength="200" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meta7">TY Letter Code</label>
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="TY_LETTER_NO" name="meta7" id="meta7" value="<?= e($sponsorship->meta7) ?>" maxlength="200" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meta6">Fair Mkt. Value</label>
                                <input type="text" class="form-control" name="meta6" id="meta6" value="<?= e($sponsorship->meta6) ?>" maxlength="200" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meta8">Gift Memo</label>
                                <input type="text" class="form-control" name="meta8" id="meta8" value="<?= e($sponsorship->meta8) ?>" maxlength="200" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meta23">Acknowledge Preference</label>
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="ACKNOWLEDGEPREF" name="meta23" id="meta23" value="<?= e($sponsorship->meta23) ?>" maxlength="200" />
                            </div>
                        </div>

                        </div>
                    </div>
                </div>

                <div class="panel panel-info">
                    <div class="panel-heading">
                        <img src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline"> Custom Integration <a href="https://help.givecloud.com/en/articles/4555453-donorperfect-syncing-user-defined-fields-custom-fields" target="_blank"><i class="fa fa-question-circle"></i></a>
                    </div>
                    <div class="panel-body">

                        <div class="row">

                            <?php $has_custom_fields = false ?>
                            <?php foreach(array('meta9','meta10','meta11','meta12','meta13','meta14','meta15','meta16','meta17','meta18','meta19','meta20','meta21','meta22') as $field): ?>
                                <?php if (sys_get('dp_'.$field.'_field') !== null && sys_get('dp_'.$field.'_field') !== ''): ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="<?= e($field) ?>"><?= e(sys_get('dp_'.$field.'_label')) ?></label>
                                        <input type="text" <?php if(sys_get('dp_'.$field.'_autocomplete') == 1): ?>class="form-control dpo-codes" data-code="<?= e(sys_get('dp_'.$field.'_field')) ?>"<?php else: ?>class="form-control"<?php endif; ?> name="<?= e($field) ?>" id="<?= e($field) ?>" value="<?= e((! $sponsorship->exists)?sys_get('dp_'.$field.'_default'):$sponsorship->$field) ?>" maxlength="200" />
                                    </div>
                                </div>
                                <?php $has_custom_fields = true ?>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php if(!$has_custom_fields): ?>
                                <div class="text-center text-muted">
                                    <i class="fa fa-frown-o fa-4x"></i><br />
                                    No custom fields have been configured.<br />
                                    Want to add some?  (its free)<br>
                                    <a href="https://help.givecloud.com/en/articles/4555453-donorperfect-syncing-user-defined-fields-custom-fields" target="_blank" class="btn btn-info btn-sm" rel="noreferrer">Learn More</a>
                                </div>
                            <?php endif ?>

                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>
</form>

<script>
window.timelinifyModalTmplData = <?= dangerouslyUseHTML(json_encode([
    'timeline_updated_email_enabled' => \Ds\Models\Email::where('name','Sponsorship: Timeline Updated')->where('is_active',1)->exists(),
    'timeline_tags' => \Ds\Models\Timeline::tags(),
])) ?>;
</script>
