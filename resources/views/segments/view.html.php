
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this custom field?');
        if (f) {
            document.segment.action = '/jpanel/sponsorship/segments/destroy';
            document.segment.submit();
        }
    }
    function onRestore () {
        var f = confirm('Are you sure you want to restore (un-delete) this custom field?');
        if (f) {
            document.segment.action = '/jpanel/sponsorship/segments/restore';
            document.segment.submit();
        }
    }
    function onEditOptions () {
        document.segment._edit_items.value = '1';
        document.segment.submit();
    }
    spaContentReady(function($){
        $('input[name="type"]').on('change', function() {
            $('#segment-visibility-0')[this.value === 'date' ? 'hide' : 'show']();
            $('#segment-visibility-1')[this.value === 'date' ? 'hide' : 'show']();
        });
    });
</script>

<div class="row clearfix">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <?php if(!$segment->trashed()): ?>
                    <a onclick="$('#segment-form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></a>
                    <a onclick="onDelete();" class="btn btn-danger <?= e((!$segment->exists) ? 'hidden' : '') ?>"><i class="fa fa-trash fa-fw"></i></a>
                <?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?php if($segment->trashed()): ?>
    <div class="alert alert-danger">
        <i class="fa fa-trash"></i> This custom field has been deleted. <a onclick="onRestore();" class="btn btn-success btn-xs"><i class="fa fa-refresh fa-fw"></i><span class="hidden-xs hidden-sm"> Restore</span></a>
    </div>
<?php endif; ?>

<form name="segment" id="segment-form" method="post" action="/jpanel/sponsorship/segments/save" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($segment->id) ?>" />
    <input type="hidden" name="_edit_items" value="0" />

    <div class="panel panel-default">
        <div class="panel-heading">
            General
        </div>
        <div class="panel-body">

            <div class="form-horizontal">

                <div class="form-group">
                    <label for="sequence" class="col-sm-3 col-md-2 control-label">Sequence</label>
                    <div class="col-sm-2 col-lg-1">
                        <input type="numeric" class="form-control" name="sequence" id="segment-sequence" value="<?= e($segment->sequence) ?>" maxlength="2" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="segment-name" class="col-sm-3 col-md-2 control-label">Name</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" name="name" id="segment-name" value="<?= e($segment->name) ?>" maxlength="150" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="segment-name_plural" class="col-sm-3 col-md-2 control-label">Name Plural</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" name="name_plural" id="segment-name_plural" value="<?= e($segment->name_plural) ?>" maxlength="150" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="segment-name_plural" class="col-sm-3 col-md-2 control-label">Type</label>

                    <div class="col-sm-8 col-lg-10">

                        <div class="radio">
                            <label>
                                <input type="radio" name="type" value="text" <?= e(volt_checked($segment->type, 'text')); ?>> Text
                            </label><br>
                            <small class="text-muted">A simple text field. You can type any value.</small>
                        </div>

                        <div class="radio">
                            <label>
                                <input type="radio" name="type" value="multi-select" <?= e(volt_checked($segment->type, 'multi-select')); ?>> Multi-Select
                            </label><br>
                            <small class="text-muted">A simple dropdown of pre-defined options. You must choose a pre-defined value.</small>
                        </div>

                        <div class="radio">
                            <label>
                                <input type="radio" name="type" value="advanced-multi-select" <?= e(volt_checked($segment->type, 'advanced-multi-select')); ?>> Advanced Multi-Select
                            </label><br>
                            <small class="text-muted">A dropdown of options. Each option displays on the website with an additional summary as well as an optional link to a page for even further explanation. For example: You could have a drop down of schools. On your website, the selected school could display with a short description and a link to a page with a full description and photos of the school. Updates to your schools would automatically be applied to all affected sponsorship records.</small>
                        </div>

                        <div class="radio">
                            <?php if ($segment->exists && $segment->type !== 'date'): ?>
                                <div class="alert alert-warning">
                                    <label style="margin-bottom:20px;">
                                        <input type="radio" name="type" value="date" <?= e(volt_checked($segment->type, 'date')); ?>> <strong>Date</strong>
                                        <span class="label label-default"><i class="fa fa-lock fa-fw"></i> PRIVATE ONLY</span>
                                    </label>
                                    <h4>WARNING: This is a potentially destructive change.</h4>
                                    <p>Making this change could result in your data becoming corrupted. For instance, if you currently keep D/M/Y formatted dates in your TEXT field. Changing to a DATE field would result in that data being incorrectly interpreted as M/D/Y during the data migration. For example 06/05/2019 would be converted to Jun 5, 2019 when in actuality it should have been May 6, 2019.</p>
                                    <p><u>To avoid any potential complications we recommend contacting Support prior to making this change.</u></p>
                                </div>
                            <?php else: ?>
                                <label>
                                    <input type="radio" name="type" value="date" <?= e(volt_checked($segment->type, 'date')); ?>> Date
                                    <span class="label label-default"><i class="fa fa-lock fa-fw"></i> PRIVATE ONLY</span>
                                </label><br>
                                <small class="text-muted">
                                    A date field.
                                </small>
                            <?php endif ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="segment-description" class="col-sm-3 col-md-2 control-label">Description</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" style="height:70px;" name="description" id="segment-description" ><?= e($segment->description) ?></textarea>
                        <small>For internal use only.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="segment-visibility" class="col-sm-3 col-md-2 control-label">Visibility</label>
                    <div class="col-sm-8 col-lg-10">

                        <div id="segment-visibility-0" class="radio">
                            <label>
                                <input type="radio" name="visibility" value="0" <?= e(($segment->show_in_detail && $segment->show_as_filter) ? 'checked' : '') ?>> Show Everywhere
                            </label><br>
                            <small class="text-muted">This field will be visible on your website. Your supporters will be able to filter your records using this field.</small>
                        </div>

                        <div id="segment-visibility-1" class="radio">
                            <label>
                                <input type="radio" name="visibility" value="1" <?= e(($segment->show_in_detail && !$segment->show_as_filter) ? 'checked' : '') ?>> Show Everywhere, Hide as Filter
                            </label><br>
                            <small class="text-muted">This field will be visible on your website.  This field will NOT display as a filter option on your website.</small>
                        </div>

                        <div id="segment-visibility-3" class="radio">
                            <label>
                                <input type="radio" name="visibility" value="2" <?= e((!$segment->show_in_detail && !$segment->show_as_filter) ? 'checked' : '') ?>> Private <i class="fa fa-lock"></i>
                            </label><br>
                            <small class="text-muted">This field is private. It will never displayed to the public.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="segment-description" class="col-sm-3 col-md-2 control-label">Geographic</label>
                    <div class="col-sm-8 col-lg-10">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_geographic" id="segment-is_geographic" value="1" <?= e(($segment->is_geographic) ? 'checked' : '') ?> > Enable geography (latitude &amp; longitude)
                            </label><br>
                            <small class="text-muted">Track the physical location of each option for this custom field. For example: Perhaps you want to track the physical location of each of your schools.</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default hidden" id="edit_options_wrap">
        <div class="panel-heading">
            Custom Field Values
        </div>
        <div class="panel-body">

            <div class="form-horizontal">

                <div class="form-group">
                    <label for="sequence" class="col-sm-3 col-md-2 control-label">Options</label>
                    <div class="col-sm-2 col-lg-1">
                        <div class="form-control-static">
                            <a onclick="onEditOptions();" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i> Edit Options</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</form>

<?php if ($segment->exists): ?>
<hr />
<small>
    Created by <?= e($segment->createdBy->full_name) ?> on <?= e($segment->created_at) ?> EST.<br />
    <?php if ($segment->updatedBy): ?>Last modified by <?= e($segment->updatedBy->full_name) ?> on <?= e($segment->updated_at) ?> EST.<?php endif; ?>
</small>
<?php endif; ?>
