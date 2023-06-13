
<script>
    function onDelete () {
        var profile_count = <?= e((int) $numRecurringPaymentProfiles) ?>;

        if (profile_count > 0) {
            $.alert('<strong>This product cannot be deleted.</strong></div><div class="modal-body alert-danger">There are <strong>'+profile_count+'</strong> active or suspended recurring payment profiles associated with it.</div><div class="modal-body">Alternatively, you can instead choose to not show this product on your site.', 'danger', 'fa-trash');
            return;
        }

        var func = function(){
            document.product.action = '/jpanel/products/destroy';
            document.product.submit();
        };

        $.confirm('Are you sure you want to delete this product?', func, 'danger', 'fa-trash');
    }

    function onRestore () {
        var func = function(){
            document.product.action = '/jpanel/products/restore';
            document.product.submit();
        };

        $.confirm('Are you sure you want to restore this product?', func, 'success');
    }
</script>

<style>
    #embedded-preview {
        height: 800px;
    }

    .flex-masonry {
        column-count: 2;
        column-gap: 25px;
    }

    .flex-box { break-inside: avoid; }

    @media (max-width: 1200px) {
        .flex-masonry {
            display: flex;
            flex-direction: column;
            column-count: 1;
        }

        #embed-code-panel {
            order: 2;
        }
    }
</style>

<form role="form" name="product" id="productForm" method="post" action="/jpanel/products/save" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($productModel->id) ?>" />
    <input type="hidden" name="productImage" value="1">

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <?php if($productModel->is_template): ?>
                <?= e($productModel->template_name) ?> <small>Template</small>
                <div class="visible-xs-block"></div>
            <?php else: ?>
                <?= e(\Illuminate\Support\Str::limit($pageTitle, 14)) ?> <small><?= e($productModel->code) ?></small>
                <span class="page-header-text block w-0 h-0 overflow-hidden"><?= e($pageTitle) ?> <small> <?= e($productModel->code) ?></small></span>
                <div class="visible-xs-block"></div>
            <?php endif; ?>

            <div class="pull-right">
                <?php if ($productModel->exists): ?>
                    <?php if(!$productModel->is_template && user()->can('reports.product_orders')): ?>
                        <a href="<?= e(route('backend.reports.products.index', $productModel)) ?>" class="btn btn-info btn-outline" data-toggle="tooltip" data-placement="top" title="Sales Report"><i class="fa fa-bar-chart-o"></i></a>
                    <?php endif; ?>
                    <?php if(user()->can('product.add')): ?>
                        <a onclick="$.confirm('Are you sure you want to create a duplicate of this product?', function(){ location='/jpanel/products/copy?id=<?= e($productModel->id) ?>'; }, 'warning');" class="btn btn-info btn-outline" data-toggle="tooltip" data-placement="top" title="Duplicate This Item"><i class="fa fa-copy"></i></a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if($productModel->userCan('edit') && $productModel->is_deleted == 0): ?>
                    <?php if($productModel->is_template): ?>
                        <button type="submit" name="save_template" class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Save your changes."><i class="fa fa-check"></i><span class="hidden-sm hidden-xs"> Save</span></button>
                    <?php else: ?>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Save your changes."><i class="fa fa-check"></i><span class="hidden-sm hidden-xs"> Save</span></button>
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu pull-right">
                                <li><a href="#save-template-modal" data-toggle="modal"><i class="fa fa-file-o fa-fw"></i> Save as Template</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if ($productModel->exists): ?><a onclick="onDelete();" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete this item."><i class="fa fa-trash"></i></a><?php endif; ?>
                <?php endif; ?>
                <?php if ($productModel->is_deleted == 1 && $productModel->exists): ?><a onclick="onRestore();" class="btn btn-success btn-outline"><i class="fa fa-check"></i><span class="hidden-sm hidden-xs"> Restore</span></a><?php endif; ?>
            </div>

            <?php if($productModel->exists && !$productModel->is_template): ?>
                <div class="text-secondary">
                    <a href="<?= e($productModel->abs_url) ?>" target="_blank" data-toggle="tooltip" data-placement="top" title="Click to view this item on your website."><?= e($productModel->abs_url) ?></a>
                    <a href="#" data-toggle="modal" data-target="#modal-product-url"><i class="fa fa-pencil-square"></i></a>
                </div>
            <?php endif; ?>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<?php if (!$productModel->is_template): ?>
    <?php if ($productModel->exists): ?>
        <?php $hidden_reasons = product_get_hidden_reasons($productModel) ?>
        <?php $is_hidden = count($hidden_reasons) > 0 ?>
        <?php if($is_hidden): ?>
            <div class="alert alert-danger">
                <div class="message" style="font-weight:normal;">
                    <i class="fa fa-exclamation-triangle fa-fw"></i> This product may not be visible on your website. <a href="javascript:void(0);" class="btn btn-xs btn-danger" id="hidden_reasons_show" onclick="$('#hidden_reasons_wrap, #hidden_reasons_show').toggle();">Show why...</a>
                    <div id="hidden_reasons_wrap" style="display:none;">
                        <ul>
                            <?php foreach($hidden_reasons as $reason): ?>
                                <li><?= e($reason) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div id="hidden_reasons_hide" style="font-weight:bold;"><a href="javascript:void(0);" class="btn btn-xs btn-danger" onclick="$('#hidden_reasons_wrap, #hidden_reasons_show').toggle();">Hide</a></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if(feature('membership') && count($membership_ids_required) > 0): ?>
        <div class="alert alert-warning">
            <i class="fa fa-lock fa-fw"></i> The following membership levels restrict access to this product:
            <ul class="mt-1">
                <?php foreach ($membership_list as $membership): ?>
                    <li><a href="<?= e(route('backend.memberships.edit', ['i' => $membership->id])) ?>"><?= e($membership->name) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>

<select id="product_meta_options" style="display:none;">
    <?php $meta_options = array('meta9','meta10','meta11','meta12','meta13','meta14','meta15','meta16','meta17','meta18','meta19','meta20','meta21','meta22'); ?>
    <?php foreach($meta_options as $meta): ?>
        <?php if(trim(sys_get('dp_'.$meta.'_label')) !== ''): ?><option value="<?= e($meta) ?>"><?= e(sys_get('dp_'.$meta.'_label')) ?></option><?php endif; ?>
    <?php endforeach; ?>
</select>

<select id="product_membership_options" style="display:none;">
    <?php $qMemberships = db_query('SELECT id, name FROM membership WHERE deleted_at IS NULL ORDER BY sequence'); ?>
    <?php while($membership = db_fetch_object($qMemberships)): ?>
        <option value="<?= e($membership->id) ?>"><?= e($membership->name) ?></option>
    <?php endwhile; ?>
</select>

<?php $show_memberships = $qMemberships !== false && db_num_rows($qMemberships) > 0 ?>

<div class="row">

    <div class="col-sm-3 col-md-2">
        <div class="list-group product-detail-tabs" id="product-detail-tabs" role="tablist">
            <a href="#general" role="tab" data-toggle="tab" class="list-group-item active">
                General
            </a>
            <a href="#fields" role="tab" data-toggle="tab" class="list-group-item">
                Custom Fields
            </a>
            <a href="#content" role="tab" data-toggle="tab" class="list-group-item  <?= e($content_editor_classes) ?>">
                Page Content
            </a>
            <?php if(feature('taxes')): ?>
                <a href="#taxes" role="tab" data-toggle="tab" class="list-group-item">
                    Sales Tax
                </a>
            <?php endif; ?>

            <?php if (feature('embedded_donation_forms') && $productModel->exists): ?>
                <a id="embed-tab" href="#embed" role="tab" data-toggle="tab" class="list-group-item">
                    Embed
                </a>
            <?php endif; ?>
        </div>

        <?php if(dpo_is_enabled()): ?>
            <div class="list-group product-detail-tabs" role="tablist">
                <a href="#dpo" role="tab" data-toggle="tab" class="list-group-item">DonorPerfect</a>
            </div>
        <?php endif; ?>

        <?php foreach ($schemas as $template): ?>
            <?php if (count($template->schema)): ?>
                <div class="list-group product-detail-tabs <?= e($template->classes) ?>" role="tablist">
                    <?php foreach ($template->schema as $data): ?>
                        <a href="#t-<?= e($data->slug) ?>" role="tab" data-toggle="tab" class="list-group-item">
                            <?= e($data->name) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="col-sm-9 col-md-10">

        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane fade in active" id="general">

                <div class="row">
                    <div class="col-md-9 col-sm-7">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                General
                            </div>
                            <div class="panel-body">

                                <div class="row">

                                    <div class="form-group col-md-3 col-lg-2">
                                        <div>
                                            <input type="hidden" id="inputPhoto" class="form-control" value="<?= e($productModel->photo->id) ?>" name="photo_id" id="inputPhoto" />
                                            <div id="inputPhoto-preview" class="media-picker" style="<?php if($productModel->photo): ?>background-image:url('<?= e($productModel->photo->thumbnail_url) ?>');<?php endif; ?>">
                                                <div class="media-picker-btns">
                                                    <a href="#" data-preview="#inputPhoto-preview" data-input="#inputPhoto" class="image-browser"><i class="fa fa-camera"></i></a>&nbsp;&nbsp;&nbsp;
                                                    <a href="<?= e($productModel->photo->public_url) ?>" target="_blank"><i class="fa fa-external-link"></i></a>&nbsp;&nbsp;&nbsp;
                                                    <a href="#" class="image-browser-clear"><i class="fa fa-trash-o"></i></a>
                                                </div>
                                            </div>
                                            <p class="text-center">Photo</p>
                                        </div>
                                    </div>

                                    <div class="col-md-9 col-lg-10">

                                        <div class="row row-padding-sm">
                                            <div class="form-group col-md-5">
                                                <label for="code"">Code (SKU)</label>
                                                <?php
                                                    $t_code = '';
                                                    if (!$isNew) $t_code = stripslashes($productModel->code);
                                                    else $t_code = strtoupper(substr(strrev(hash('ripemd160', time())),0,7));
                                                ?>
                                                <input type="text" class="form-control" name="code" id="code" value="<?= e($t_code) ?>" maxlength="45" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="A Product Code or SKU is the unique identifier for a product. It must:<br><i class='fa fa-check'></i> Be unique (including deleted products)<br><i class='fa fa-check'></i> Only contain numbers or letters<br><i class='fa fa-times'></i> <b>NOT</b> contain spaces or special characters" />
                                            </div>

                                            <div class="form-group col-md-7">
                                                <label for="name">Name</label>
                                                <input type="text" class="form-control" name="name" id="name" value="<?= e($productModel->name); ?>" maxlength="750" />
                                            </div>
                                        </div>

                                        <div class="row row-padding-sm">
                                            <div class="form-group col-md-8">
                                                <label for="summary">Summary</label>
                                                <input type="text" class="form-control" name="summary" id="summary" value="<?= e($productModel->summary); ?>" maxlength="500" />
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="author"><?= e(sys_get('ecomm_syn_author')) ?></label>
                                                <input type="text" class="form-control" name="author" id="author" value="<?= e($productModel->author); ?>" maxlength="250" />
                                            </div>
                                        </div>

                                        <textarea class="hide" name="variant_json"><?= dangerouslyUseHTML(json_encode($productModel->variants)) ?></textarea>
                                        <style>
                                            #product-variations { margin:-2px 0px; }
                                                #product-variations .data-row { padding:12px 0px; border-bottom:1px solid #eee; }
                                                #product-variations .data-row:last-of-type { border-bottom:none; }
                                                #product-variations .sortable-placeholder { background-color:#5bc0de; width:auto; height:40px; opacity:0.24; }
                                        </style>

                                        <?php if (count($currencies) > 1): ?>
                                            <div class="form-group">
                                                <label>Base Currency</label>
                                                <select name="base_currency" class="form-control">
                                                <?php foreach ($currencies as $currency): ?>
                                                    <option <?= e(volt_selected($currency, $productModel->base_currency)); ?> value="<?= e($currency->code) ?>"><?= e($currency->code) ?> (<?= e($currency->symbol) ?>) - <?= e($currency->name) ?></option>
                                                <?php endforeach; ?>
                                                </select>
                                                <p class="help-block">
                                                    <i class="fa fa-question-circle"></i> The base currency is the currency from which the conversion to other currencies will be based.
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="form-group">
                                            <a class="pull-right btn btn-xs btn-link add-product-variant" href="#"><i class="fa fa-plus"></i> Add Option</a>
                                            <label>Price &amp; Options</label>
                                            <div class="form-control" style="height:auto;">
                                                <div id="product-variations">
                                                    <div class="status bottom-gutter top-gutter text-muted text-center"><i class="fa fa-spin fa-spinner fa-2x"></i></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <!--<div class="pull-right">
                                                <a class="btn btn-xs btn-link" href="#"><i class="fa fa-check-square-o"></i> All</a>&nbsp;
                                                <a class="btn btn-xs btn-link" href="#"><i class="fa fa-square-o"></i> None</a>
                                            </div>-->
                                            <label>Categories</label>
                                            <select class="form-control selectize keep-open" size="1" name="category[]" multiple="multiple">
                                                <?php foreach(\Ds\Models\ProductCategory::topLevel()->with('childCategories.childCategories.childCategories.childCategories')->orderBy('sequence')->get() as $cat1): ?>
                                                    <option value="<?= e($cat1->id) ?>" <?= e((is_numeric(array_search($cat1->id,$categories)))?'selected':'') ?> ><?= e($cat1->name) ?></option>
                                                    <?php if($cat1->childCategories): foreach($cat1->childCategories as $cat2): ?>
                                                        <option value="<?= e($cat2->id) ?>" <?= e((is_numeric(array_search($cat2->id,$categories)))?'selected':'') ?> ><?= e($cat1->name) ?> &gt; <?= e($cat2->name) ?></option>
                                                        <?php if($cat2->childCategories): foreach($cat2->childCategories as $cat3): ?>
                                                            <option value="<?= e($cat3->id) ?>" <?= e((is_numeric(array_search($cat3->id,$categories)))?'selected':'') ?> ><?= e($cat1->name) ?> &gt; <?= e($cat2->name) ?> &gt; <?= e($cat3->name) ?></option>
                                                        <?php endforeach; endif; ?>
                                                    <?php endforeach; endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Active Dates</label>
                                            <div class="form-inline">
                                                <div class="input-group">
                                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                                    <input type="text" style="width:120px;" class="form-control datePretty" name="publish_start_date" id="publish_start_date" placeholder="First Day" value="<?= e(($productModel->publish_start_date) ? $productModel->publish_start_date->format('M j, Y') : '') ?>" />
                                                </div>
                                                <div class="form-control-static text-center">&nbsp;&nbsp;to&nbsp;&nbsp;</div>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                                    <input type="text" style="width:120px;" class="form-control datePretty" name="publish_end_date" id="publish_end_date" placeholder="Last Day" value="<?= e(($productModel->publish_end_date) ? $productModel->publish_end_date->format('M j, Y') : '') ?>" />
                                                </div>
                                            </div>
                                            <small class="text-muted">Optionally, choose the dates you want this product to be visible on your site.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Inventory Control
                            </div>
                            <div class="panel-body">
                                <div class="form-horizontal">
                                    <div class="<?php if (!feature('stock')) echo 'hidden'; ?>">

                                        <div class="form-group">
                                            <label for="outofstock_allow" class="col-md-3 control-label">Out of Stock</label>
                                            <div class="col-md-9">
                                                <select id="outofstock_allow" name="outofstock_allow" class="form-control" onchange="j.product.onOutOfStockChange();">
                                                    <option value="1" <?= dangerouslyUseHTML(($productModel->outofstock_allow == '1') ? 'selected="selected"' : '') ?>>Accept contributions even when there is no stock.</option>
                                                    <option value="0" <?= dangerouslyUseHTML(($productModel->outofstock_allow == '0') ? 'selected="selected"' : '') ?>>Stop accepting contributions when out of stock.</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="outofstock_message" class="col-md-3 control-label">Out of Stock Message</label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" placeholder="Sold out" name="outofstock_message" id="outofstock_message" value="<?= e($productModel->outofstock_message) ?>" />
                                                <small class="text-muted">Display a custom message when your product is completely sold out. Example: 'Sorry. There are no more tickets available.' (Leave this blank to use the default message). This message only displays when all your variants are sold out OR total sales exceed the value for "Limit # of Sales".</small>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-group">
                                        <label for="limit_sales" class="col-md-3 control-label">Limit # of Sales</label>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="limit_sales" id="limit_sales" value="<?= e(intval($productModel->limit_sales)) ?>" />
                                            <small class="text-muted">Limit the # of times the product can be sold. Set to 0 for unlimited.<br><span class="text-info"><i class="fa fa-exclamation-circle"></i> <?php $purchases = product_total_purchases($productModel->id); echo (($purchases) ? intval($purchases->quantitypurchased) : 'No'); ?> sales to date</span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Goal Tracking
                            </div>
                            <div class="panel-body">
                                <div class="form-horizontal">

                                    <div class="form-group">
                                        <label for="goalamount" class="col-md-3 control-label">Goal Amount</label>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <div class="input-group-addon"><?= e($productModel->base_currency->symbol) ?></div>
                                                <input type="text" class="form-control text-right" name="goalamount" id="goalamount" value="<?= e(nullable_cast('float', $productModel->goalamount)) ?>" />
                                            </div>
                                            <small class="text-muted">Leave blank to set no goal for this product.</small>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                                <input type="text" class="form-control datePretty" name="goal_deadline" id="goal_deadline" placeholder="Goal Date" value="<?= e(($productModel->goal_deadline) ? $productModel->goal_deadline->format('M j, Y') : '') ?>" />
                                            </div>
                                            <small class="text-muted">The deadline for reaching the goal. Leave blank to set no goal date for this product.</small>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="goal_progress_offset" class="col-md-3 control-label">Goal Progress Offset</label>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <div class="input-group-addon"><?= e($productModel->base_currency->symbol) ?></div>
                                                <input type="text" class="form-control text-right" name="goal_progress_offset" id="goal_progress_offset" value="<?= e(nullable_cast('float', $productModel->goal_progress_offset)) ?>" />
                                            </div>
                                            <small class="text-muted">Manually offset the goal progress.</small>
                                        </div>
                                    </div>

                                    <div class="form-group <?= e((!dpo_is_enabled()) ? 'hide' : '') ?>">
                                        <label for="goalamount" class="col-md-3 control-label">&nbsp;</label>
                                        <div class="col-md-4 form-inline">
                                            <div class="checkbox">
                                                <label><input type="radio" name="goal_use_dpo" <?= e(($productModel->goal_use_dpo == 0) ? 'checked' : '') ?> value="0">&nbsp;&nbsp;Track goal using Givecloud <small>(Recommended)</small></label>
                                            </div>
                                            <p><small class="text-muted">Use sales in Givecloud to track the progress of your goal.</small></p>
                                        </div>
                                        <div class="col-md-4 form-inline">
                                            <div class="checkbox">
                                                <label><input type="radio" name="goal_use_dpo" <?= e(($productModel->goal_use_dpo == 1) ? 'checked' : '') ?> value="1">&nbsp;&nbsp;Track goal using DonorPerfect</label>
                                            </div>
                                            <p><small class="text-muted">Use donation data in DonorPerfect (based on the coding provided in the DPO Integration tab) to track the progress of your goal.</small></p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                'Add-To' Buttons
                            </div>
                            <div class="panel-body">

                                <div class="form-horizontal">

                                    <div class="form-group">
                                        <label for="add_to_label" class="col-md-3 control-label">Button Label</label>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="add_to_label" id="add_to_label" value="<?= e($productModel->add_to_label); ?>" placeholder="Add to Cart" maxlength="200" />
                                            <small class="text-muted">The text that appears in the 'Add to Cart' button.</small>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="alt_button_label" class="col-md-3 control-label">Alternate Button</label>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="alt_button_label" id="alt_button_label" value="<?= e($productModel->alt_button_label) ?>" placeholder="Button Label" />
                                            <small class="text-muted">Leave this blank to hide the second button.</small>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control ds-urls" name="alt_button_url" id="alt_button_url" value="<?= e($productModel->alt_button_url) ?>" placeholder="Go to page..." />
                                            <small class="text-muted">This page will load after the button is clicked and the item is added to the cart.</small>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-gift"></i> Tributes &amp; Dedications
                            </div>
                            <div class="panel-body">

                                <div class="form-horizontal">

                                    <div class="form-group">
                                        <label for="add_to_label" class="col-md-3 control-label">Allow Tributes</label>
                                        <div class="col-md-9">

                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="allow_tributes" <?= e(($productModel->allow_tributes == 0) ? 'checked' : '') ?> value="0"> No
                                                </label><br>
                                                <small class="text-muted">Users will not be able to give a tribute donation. (for example: in-memory or in-honor of)</small>
                                            </div>

                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="allow_tributes" <?= e(($productModel->allow_tributes == 1) ? 'checked' : '') ?> value="1"> Allow Tributes
                                                </label><br>
                                                <small class="text-muted">Users can choose whether or not to give a tribute.</small>
                                            </div>

                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="allow_tributes" <?= e(($productModel->allow_tributes == 2) ? 'checked' : '') ?> value="2"> Require Tributes
                                                </label><br>
                                                <small class="text-muted">Users are forced to give a tribute.</small>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="add_to_label" class="col-md-3 control-label">Allow Notifications</label>
                                        <div class="col-md-9">

                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="allow_tribute_notification" <?= e(($productModel->allow_tribute_notification == 1) ? 'checked' : '') ?> value="1"> Email or Letter
                                                </label><br>
                                                <small class="text-muted">Users can optionally request a notification (or acknowledgement) via email or letter.</small>
                                            </div>

                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="allow_tribute_notification" <?= e(($productModel->allow_tribute_notification == 2) ? 'checked' : '') ?> value="2"> Email Only
                                                </label><br>
                                                <small class="text-muted">Users can optionally request a notification (or acknowledgement) via email.</small>
                                            </div>

                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="allow_tribute_notification" <?= e(($productModel->allow_tribute_notification == 3) ? 'checked' : '') ?> value="3"> Letter Only
                                                </label><br>
                                                <small class="text-muted">Users can optionally request a notification (or acknowledgement) via letter.</small>
                                            </div>

                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="allow_tribute_notification" <?= e(($productModel->allow_tribute_notification == 0) ? 'checked' : '') ?> value="0"> No
                                                </label><br>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="add_to_label" class="col-md-3 control-label">Tribute Options</label>
                                        <div class="col-md-9">
                                            <select class="selectize form-control" placeholder="All Tribute Types" multiple name="tribute_type_ids[]">
                                                <?php foreach($tributeTypes as $type): ?>
                                                    <option value="<?= e($type->id) ?>" <?= e((is_array($productModel->tribute_type_ids) && in_array($type->id, $productModel->tribute_type_ids)) ? 'selected' : '') ?> ><?= e($type->label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Staff Notifications
                            </div>
                            <div class="panel-body">
                                <div class="form-horizontal">

                                    <div class="form-group">
                                        <label for="limit_sales" class="col-md-3 control-label">Staff Email Notification</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control selectize-tags" name="email_notify" id="email_notify" value="<?= e($productModel->email_notify) ?>" />
                                            <small class="text-muted">
                                                <?php if (\Ds\Models\Email::activeType('admin_order_received')->count() === 0): ?>
                                                    <span class="text-danger"><strong><i class="fa fa-exclamation-triangle"></i> WARNING - These emails will NOT be notified.</strong> The "Contribution Received: To Admin" email is currently disabled. <a href="/jpanel/settings/email" class="btn btn-danger btn-xs">Fix It</a></span><br>
                                                <?php endif; ?>
                                                The email notification "Contribution Received: To Admin" will be sent to these addresses anytime this product is purchased. Multiple addresses must be separated with a comma.
                                            </small>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <?php if (! dpo_is_enabled()): ?>
                            <div id="account-designation-app" class="mb-24" data-dp-enabled="<?= e(json_encode(dpo_is_enabled())) ?>" data-options="<?= e(json_encode($productModel->designation_options)) ?>"></div>
                        <?php endif; ?>

                    </div>

                    <div class="col-md-3 col-sm-5">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Template
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <div>
                                        <input id="template-suffix" name="template_suffix" type="hidden" value="<?= e($productModel->template_suffix) ?>" />
                                        <?php foreach ($templates as $template): ?>
                                            <div class="template-suffix template-suffix--<?= e($template['suffix']) ?> <?= e($productModel->template_suffix !== $template['suffix'] ? 'hide' : '') ?>">
                                                <p class="text-center mb-1 text-md font-bold"><?= e($template['name']) ?></p>
                                                <img class="max-w-full border border-solid border-gray-300" src="<?= e($template['thumbnail']) ?>" />
                                            </div>
                                        <?php endforeach ?>
                                        <div class="flex mt-4 justify-center">
                                            <a href="#choose-template-modal" data-toggle="modal" class="pull-right btn btn-info">Change Template</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-5">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Options
                            </div>
                            <div class="panel-body">

                                <?php if(!$productModel->is_template): ?>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="product_as_homepage" <?php if ($productModel->code) echo volt_checked(sys_get('product_as_homepage'), $productModel->code); ?> value="1">Use as Homepage
                                        </label>
                                    </div>
                                <?php endif; ?>

                                <hr />

                                <div class="form-group">
                                    <?php if(!$productModel->is_template): ?>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="isenabled" <?= e(($productModel->isenabled) ? 'checked' : '') ?> value="1">Show on Website
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="show_in_pos" <?= e(($productModel->show_in_pos) ? 'checked' : '') ?> value="1">Show on POS
                                            </label>
                                        </div>
                                        <hr />
                                    <?php endif; ?>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="ach_only" <?= e(($productModel->ach_only) ? 'checked' : '') ?> value="1">ACH only
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="hide_price" <?= e(($productModel->hide_price) ? 'checked' : '') ?> value="1">Hide Price
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="hide_qty" <?= e(($productModel->hide_qty) ? 'checked' : '') ?> value="1">Hide Qty
                                        </label>
                                    </div>
                                </div>

                                <hr />

                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="isnew" <?= e(($productModel->isnew) ? 'checked' : '') ?> value="1">New Product
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="isfeatured" <?= e(($productModel->isfeatured) ? 'checked' : '') ?> value="1">Featured Product
                                        </label>
                                    </div>
                                    <div class="checkbox <?php if (sys_get('show_clearance') == '0') echo 'hidden'; ?>">
                                        <label>
                                            <input type="checkbox" name="isclearance" <?= e(($productModel->isclearance) ? 'checked' : '') ?> value="1">Clearance Product
                                        </label>
                                    </div>
                                    <div class="checkbox hide">
                                        <label>
                                            <input type="checkbox" name="istribute" <?= e(($productModel->istribute) ? 'checked' : '') ?> value="1">Enable DPO Tributes
                                        </label>
                                    </div>
                                    <div class="<?php if (!feature('social')) echo 'hidden'; ?>">
                                        <hr />
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="isfblike" <?= e(($productModel->isfblike) ? 'checked' : '') ?> value="1">Enable Social Buttons
                                            </label>
                                        </div>
                                    </div>
                                    <div class="<?php if (!feature('check_ins')) echo 'hidden'; ?>">
                                        <hr />
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="allow_check_in" <?= e(($productModel->allow_check_in) ? 'checked' : '') ?> value="1">Enable Event Check-Ins
                                            </label>
                                        </div>
                                    </div>

                                    <?php
                                        $tax_incentive = null;
                                        if (sys_get('gift_aid')) {
                                            $tax_incentive = 'Gift Aid';
                                        } else if (sys_get('tax_receipt_pdfs')) {
                                            $tax_incentive = 'Tax Receipts';
                                        }
                                    ?>

                                    <div class="<?php if (!$tax_incentive) echo 'hidden'; ?>">
                                        <hr />
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="is_tax_receiptable" <?= e(($productModel->is_tax_receiptable) ? 'checked' : '') ?> value="1">Allow <?= e($tax_incentive) ?>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="<?php if (!sys_get('dcc_enabled')) echo 'hidden'; ?>">
                                        <hr />
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="is_dcc_enabled" <?= e(($productModel->is_dcc_enabled) ? 'checked' : '') ?> value="1">Allow Donor Covers Costs (DCC)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Private Notes
                            </div>
                            <div class="panel-body">
                                <textarea class="form-control" style="font-size:12px;" name="notes" id="notes" rows="10"><?= e($productModel->notes); ?></textarea>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Custom emails
                                <a href="<?= e(route('backend.emails.add')) ?>" class="pull-right btn btn-info btn-xs">
                                    <i class="fa fas fa-plus"></i> Add
                                </a>
                            </div>
                            <div class="panel-body">
                                <?php if($productEmails->isEmpty() && $variantEmails->isEmpty()): ?>
                                    <div class="text-muted text-center text-lg mt-2">
                                        <i class="fa fas fa-envelope fa-2x"></i><br>
                                        No custom emails
                                </div>
                                <?php endif; ?>
                                <ul class="list-none pl-0 divide-y divide-gray-200 mb-0">
                                    <?php foreach($productEmails as $email): ?>
                                        <li class="py-2">
                                            <a href="<?= e(route('backend.emails.add', ['i' => $email->id])) ?>"><?= e($email->name) ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php foreach($variantEmails as $email): ?>
                                        <li class="py-2">
                                            <a href="<?= e(route('backend.emails.add', ['i' => $email->id])) ?>">
                                                <?= e($email->name) ?> (For <?= e(\Illuminate\Support\Str::plural('variant', $email->variants->count())) ?>: <?= e($email->variants->pluck('variantname')->implode(', ')) ?>)
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="tab-pane fade" id="fields">

                <div class="row">
                    <div class="col-lg-8 col-lg-offset-2">
                        <div id="fields_blocks"></div>
                        <script type="application/json" id="fieldsJson"><?= dangerouslyUseHTML(json_encode($qFields)) ?></script>
                        <a href="javascript:void(0);" class="btn btn-info" onclick="j.product.fields.add();"><i class="fa fa-plus fa-fw"></i> Add Input Field</a>
                        <br /><br />
                    </div>
                </div>

            </div>
            <div class="tab-pane fade" id="content">

                <div class="row">
                    <div class="form-group">
                        <div class="col-md-12">
                            <textarea class="form-control html" name="description" id="description" style="height:780px;"><?= e($productModel->description) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="taxes">


                <div class="form-horizontal">

                    <?php if(sys_get('taxcloud_api_key')): ?>

                        <div class="form-group">

                            <label for="meta1" class="col-md-3 control-label">
                                Tax Category<br />
                                <small class="text-muted">Powered by TaxCloud</small>
                            </label>
                            <div class="col-md-7">
                                <select class="form-control selectize" name="taxcloud_tic_id" placeholder="Taxable Item Category ID...">
                                    <option></option>
                                    <?php foreach ($tics as $tic): ?>
                                        <option value="<?= e($tic->TICID) ?>" <?= e(($tic->TICID == $productModel->taxcloud_tic_id) ? 'selected' : '') ?> ><?= e($tic->Description) ?> (<?= e($tic->TICID) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    <?php else: ?>

                        <div class="form-group">
                            <label for="meta1" class="col-md-3 control-label">
                                Taxes<br />
                                <a href="javascript:void(0);" class="btn btn-info btn-xs" onclick="$('input.taxoption').attr({checked:'checked'});">All</a> <a href="javascript:void(0);" class="btn btn-info btn-xs" onclick="$('input.taxoption').removeAttr('checked');">None</a><br />
                                <small><a href="https://help.givecloud.com/en/articles/3081919-taxcloud" target="_blank" rel="noreferrer"><i class="fa fa-question-circle"></i> Help</a></small>
                            </label>
                            <div class="col-md-9">

                                <div class="row">
                                <?php foreach($taxes as $tax): ?>

                                    <div class="checkbox col-sm-6 col-xs-6 col-md-6 col-lg-4">
                                        <label>
                                            <input type="checkbox" class="taxoption" name="taxids[]" id="taxid<?= e($tax->id) ?>" value="<?= e($tax->id) ?>" <?= e(($tax->isselected == 1) ? 'checked' : '') ?>> <?= e($tax->description) ?>
                                        </label>
                                    </div>

                                <?php endforeach; ?>
                                </div>

                            </div>
                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <?php if (feature('embedded_donation_forms') && $productModel->exists): ?>
                <div class="tab-pane fade" id="embed">
                    <div class="flex-masonry">
                        <div class="flex-box">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Settings
                                </div>

                                <div class="panel-body">
                                    <div class="form-horizontal">
                                        <div class="form-group">
                                            <label for="embed-title" class="col-md-3 control-label">Title</label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" name="embed-title" id="embed-title" value="<?= e($productModel->name) ?>" />
                                                <small class="text-muted">Leave this field empty if you do not want to show the title in the embedded form.</small>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="embed-summary" class="col-md-3 control-label">Summary</label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" name="embed-summary" id="embed-summary" value="<?= e($productModel->summary) ?>" />
                                                <small class="text-muted">Leave this field empty if you do not want to show the summary in the embedded form.</small>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="embed_theme" class="col-md-3 control-label">Theme</label>
                                            <div class="col-md-9">
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="embed_theme" value="light" checked> Light
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="embed_theme" value="dark"> Dark
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="embed_primary_color" class="col-md-3 control-label">Primary Color</label>
                                            <div class="col-md-9">
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="embed_primary_color" value="indigo" checked> Indigo
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="embed_primary_color" value="green"> Green
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="embed_primary_color" value="blue"> Blue
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="embed_primary_color" value="purple"> Purple
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="embed_primary_color" value="pink"> Pink
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="embed_show_goal_progress" class="col-md-3 control-label">Goal Progress</label>
                                            <div class="col-md-9">
                                                <input type="checkbox" class="switch" value="1" id="embed_show_goal_progress" name="embed_show_goal_progress">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="embed-code-panel" class="flex-box">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Code
                                </div>

                                <div class="panel-body">
                                    <div class='alert alert-info'>
                                        <i class="fa fa-fw fa-exclamation-circle"></i>
                                        Use the code below to embed the form as displayed. Note that changing the settings also changes this code.
                                    </div>

                                    <div class='alert alert-danger'>
                                        <ul class='pl-4'>
                                            <li>If you are using custom fields or tributes for this product, they will not show up on the embeddable form.</li>
                                            <?php if($gocardlessInstalled): ?>
                                                <li>The GoCardless integration will not show up on the embeddable form.</li>
                                            <?php endif; ?>
                                            <?php if($paysafeInstalled): ?>
                                                <li>The Paysafe integration will not show up on the embeddable form.</li>
                                            <?php endif; ?>
                                            <?php if($paypalInstalled): ?>
                                                <li>The Paypal integration will not show up on the embeddable form on mobile devices.</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>

                                    <div class="flex flex-col items-center bg-gray-200 rounded p-4">
                                        <div id="iframe-code" class="break-all mb-4">
                                            <?= e(sprintf(
                                                '<iframe src="%s" title="%s embedded form" style="height:800px;max-width:400px;width:100%%;" frameborder="0"></iframe>',
                                                secure_site_url('embed/donation/{$productModel->code}?theme=[theme]&primaryColor=[primaryColor]&title=[title]&summary=[summary][showGoalProgress]'),
                                                $productModel->name
                                            )) ?>
                                        </div>

                                        <button id="copy-iframe-code" type="button" class="btn btn-info">Copy</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex-box">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Preview
                                </div>

                                <div class="panel-body flex justify-center">
                                    <div id='embedded-preview-container' class="rounded-lg p-3 max-w-sm">
                                        <iframe id="embedded-preview" data-src="<?= e(secure_site_url("embed/donation/{$productModel->code}?theme=[theme]&primaryColor=[primaryColor]&title=[title]&summary=[summary][showGoalProgress]")) ?>" title="Embedded form" frameborder="0"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="tab-pane fade" id="dpo">

                <div class="panel panel-info">
                    <div class="panel-heading">
                        <img src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline">Sync this item</a>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <div class="col-md-4">
                                <select class="form-control ml-4" name="metadata[dp_syncable]">
                                    <option value="1" <?= e(volt_selected($productModel->metadata('dp_syncable', 1), 1)); ?>>Yes</option>
                                    <option value="0" <?= e(volt_selected($productModel->metadata('dp_syncable', 1), 0)); ?>>No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (dpo_is_enabled()): ?>
                    <div id="account-designation-app" class="dp-panel <?= e($productModel->metadata('dp_syncable', 1) != 1 ? 'hide' : '') ?>"
                        data-dp-enabled="<?= e(json_encode(dpo_is_enabled())) ?>"
                        data-has-variant-level-coding="<?= e(json_encode($productModel->variants->pluck('metadata.dp_gl_code')->filter()->isNotEmpty())) ?>"
                        data-disable-supporters-choice="<?= e(json_encode($productModel->template_suffix !== 'page-with-payment')) ?>"
                        data-options="<?= e(json_encode($productModel->designation_options)) ?>"
                    ></div>
                <?php endif; ?>

                <div class="panel panel-info dp-panel <?= e($productModel->metadata('dp_syncable', 1) != 1 ? 'hide' : '') ?>">
                    <div class="panel-heading">
                        <img src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline"> General

                        <a href="#" class="dpo-codes-refresh btn btn-info btn-xs pull-right"><i class="fa fa-refresh fa-fw"></i> Refresh DonorPerfect Codes</a>
                    </div>
                    <div class="panel-body">

                        <div class="form-horizontal">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="meta2" class="col-md-5 control-label">Campaign</label>
                                        <div class="col-md-7">
                                            <input type="text" autocomplete="off" class="form-control <?= e(dpo_is_enabled() ? 'dpo-codes' : '') ?>" data-code="CAMPAIGN" name="meta2" id="meta2" value="<?= e($productModel->meta2) ?>" maxlength="200" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="meta3" class="col-md-5 control-label">Solicitation</label>
                                        <div class="col-md-7">
                                            <input type="text" autocomplete="off" class="form-control <?= e(dpo_is_enabled() ? 'dpo-codes' : '') ?>" data-code="SOLICIT_CODE" name="meta3" id="meta3" value="<?= e($productModel->meta3) ?>" maxlength="200" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="meta4" class="col-md-5 control-label">Sub Solicitation</label>
                                        <div class="col-md-7">
                                            <input type="text" autocomplete="off" class="form-control <?= e(dpo_is_enabled() ? 'dpo-codes' : '') ?>" data-code="SUB_SOLICIT_CODE" name="meta4" id="meta4" value="<?= e($productModel->meta4) ?>" maxlength="200" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="meta5" class="col-md-5 control-label">Gift Type</label>
                                        <div class="col-md-7">
                                            <input type="text" autocomplete="off" class="form-control <?= e(dpo_is_enabled() ? 'dpo-codes' : '') ?>" data-code="GIFT_TYPE" name="meta5" id="meta5" value="<?= e($productModel->meta5) ?>" maxlength="200" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="meta7" class="col-md-5 control-label">TY Letter Code</label>
                                        <div class="col-md-7">
                                            <input type="text" autocomplete="off" class="form-control <?= e(dpo_is_enabled() ? 'dpo-codes' : '') ?>" data-code="TY_LETTER_NO" name="meta7" id="meta7" value="<?= e($productModel->meta7) ?>" maxlength="200" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="meta6" class="col-md-5 control-label">Fair Mkt. Value</label>
                                        <div class="col-md-7">
                                            <select name="meta6" class="form-control">
                                                <option value="0">Do Not Use</option>
                                                <option <?= dangerouslyUseHTML(($productModel->meta6) ? 'selected="selected"' : '') ?> value="1" >Populate with Purchase Value</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="meta8" class="col-md-5 control-label">Gift Memo</label>
                                        <div class="col-md-7">
                                            <input type="text" class="form-control" name="meta8" id="meta8" value="<?= e($productModel->meta8) ?>" maxlength="200" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="meta23" class="col-md-5 control-label">Acknowledge Preference</label>
                                        <div class="col-md-7">
                                            <input type="text" autocomplete="off" class="form-control <?= e(dpo_is_enabled() ? 'dpo-codes' : '') ?>" data-code="ACKNOWLEDGEPREF" name="meta23" id="meta23" value="<?= e($productModel->meta23) ?>" maxlength="200" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group <?= e((sys_get('dp_use_nocalc') == '0') ? 'hidden' : '') ?>">
                                        <label for="dpo_nocalc" class="col-md-5 control-label">NoCalc</label>
                                        <div class="col-md-7">
                                            <select name="dpo_nocalc" id="dpo_nocalc" class="form-control">
                                                <option value="N" <?= dangerouslyUseHTML(($productModel->dpo_nocalc == 'N') ? 'selected="selected"' : '') ?> >N</option>
                                                <option value="Y" <?= dangerouslyUseHTML(($productModel->dpo_nocalc == 'Y') ? 'selected="selected"' : '') ?> >Y</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="panel panel-info dp-panel <?= e($productModel->metadata('dp_syncable', 1) != 1 ? 'hide' : '') ?>">
                    <div class="panel-heading">
                        <img src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline"> Custom Integration <a href="https://help.givecloud.com/en/articles/4555453-donorperfect-syncing-user-defined-fields-custom-fields" target="_blank" rel="noreferrer"><i class="fa fa-question-circle"></i></a>
                    </div>
                    <div class="panel-body">

                        <div class="form-horizontal">
                            <div class="row">

                                <?php $has_custom_fields = false ?>
                                <?php foreach(array('meta9','meta10','meta11','meta12','meta13','meta14','meta15','meta16','meta17','meta18','meta19','meta20','meta21','meta22') as $field): ?>
                                    <?php if (sys_get('dp_'.$field.'_field') !== null && sys_get('dp_'.$field.'_field') !== '') { ?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="<?= e($field) ?>" class="col-md-5 control-label"><?= e(sys_get('dp_'.$field.'_label')) ?></label>
                                                <div class="col-md-7">
                                                    <input type="text" <?php if(sys_get('dp_'.$field.'_autocomplete') == 1): ?>class="form-control <?= e(dpo_is_enabled() ? 'dpo-codes' : '') ?>" data-code="<?= e(sys_get('dp_'.$field.'_field')) ?>"<?php else: ?>class="form-control"<?php endif; ?> name="<?= e($field) ?>" id="<?= e($field) ?>" value="<?= e($productModel->exists ? $productModel->getAttribute($field) : sys_get('dp_'.$field.'_default')); ?>" maxlength="200" />
                                                </div>
                                            </div>
                                        </div>
                                        <?php $has_custom_fields = true ?>
                                    <?php } ?>
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
            </div>

            <?php foreach ($schemas as $template): ?>
                <?php foreach ($template->schema as $data): ?>
                    <div class="tab-pane fade" id="t-<?= e($data->slug) ?>">
                        <?php gc_metadata_template_suffixes($productModel, $template, $data) ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>

        </div>
    </div>

</div>

<div class="modal modal-info fade" id="modal-product-url">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-link"></i> Change Public URL</h4>
            </div>
            <div class="modal-body">

                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> Changing this public URL will break all existing links to this product.
                </div>

                <div class="form-group">
                    <label class="control-label">Public Url</label>
                    <div class="input-group">
                        <div class="input-group-addon"><?= e(secure_site_url()) ?>/</div>
                        <input type="text" class="form-control" name="permalink" value="<?= e($productModel->permalink) ?>" placeholder="items/<?= e(\Illuminate\Support\Str::slug($productModel->name)) ?>">
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="save-template-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Save as Template</h4>
            </div>
            <div class="modal-body">

                <p class="text-muted bottom-gutter">Easily create other items just like this one by saving this as a template. You'll be able to choose this template when adding a new item.</p>

                <div class="form-group">
                    <label class="control-label">Template Name</label>
                    <input type="text" class="form-control" name="template_name" value="<?= e($productModel->template_name) ?>">
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" name="<?= e(($productModel->exists) ? 'create_template' : 'save_template') ?>" value="1"><i class="fa fa-check"></i> Save as Template</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="choose-template-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Choose Template</h4>
            </div>
            <div class="modal-body">

                <div class="flex justify-center flex-wrap">
                    <?php foreach ($templates as $template): ?>
                        <div class="flex flex-col items-center w-full lg:w-1/3 my-4">
                            <img data-suffix="<?= e($template['suffix']) ?>" class="template-img w-64 cursor-pointer border hover:border-8 border-solid border-gray-300 hover:border-blue-300" src="<?= e($template['thumbnail']) ?>" />
                            <p class="mt-1 text-lg font-bold"><?= e($template['name']) ?></p>
                        </div>
                    <?php endforeach ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

</form>

<script>
    spaContentReady(function() {

        // tabs (active/inactive)
        $('.product-detail-tabs a').click(function(ev){
            $('.product-detail-tabs a.active').removeClass('active');
            $(ev.target).addClass('active').tab('show');
        });

        /**
         * Product variant / option management
         */
        (function(){

            var $container = $('#product-variations'),
                $jsonContainer = $('textarea[name=variant_json]'),
                productVariants = JSON.parse($jsonContainer.val());

            function init() {
                $('#template-suffix').bind('change', onTemplateChange).change();
                $('#embed-tab').bind('click', updateEmbedPreviewAndCode);
                $('input[name="embed_theme"]').bind('change', updateEmbedPreviewAndCode);
                $('input[name="embed_primary_color"]').bind('change', updateEmbedPreviewAndCode);
                $('input[name="embed_show_goal_progress"]').bind('switchChange.bootstrapSwitch', updateEmbedPreviewAndCode);
                $('#embed-title').bind('change', updateEmbedPreviewAndCode);
                $('#embed-summary').bind('change', updateEmbedPreviewAndCode);
                $('#copy-iframe-code').bind('click', copyEmbedIframeCode);
                $('#embedded-preview').bind('load', onEmbeddedPreviewLoad);
                $('#choose-template-modal .template-img').bind('click', onChooseTemplate);

                if (productVariants.length == 1 && !productVariants[0].variantname) {
                    productVariants[0].variantname = 'Default Option';
                    productVariants[0].isdefault   = true;
                }

                if (productVariants.length == 0) {
                    var variant = newVariant();
                    variant.variantname = 'Default Option';
                    variant.isdefault   = true;
                    productVariants.push(variant);
                    updateJson();
                }

                drawItems(productVariants);
                $('.add-product-variant').bind('click', onAddClick);
            }

            function updateJson() {
                $jsonContainer.val(JSON.stringify(productVariants));
            }

            function onAddClick(ev) {
                ev.preventDefault(0);

                var variant = newVariant();

                openVariantModal(variant);
            }

            function onEditClick(ev){
                ev.preventDefault();

                var $target = $(ev.target),
                    $row = $target.parents('.data-row'),
                    variant = $row.data('variant');

                openVariantModal(variant);
            }

            function onVariantSave(ev){
                ev.preventDefault();

                var $form = $(ev.target),
                    $modal = $form.parents('.modal'),
                    formData = {},
                    variant = $form.data('variant');

                formData = $form.serializeObject();

                variant.variantname                    = formData.variantname;
                variant.sku                            = formData.sku;
                variant.billing_period                 = formData.billing_period;
                variant.fair_market_value              = $.toNumber(formData.fair_market_value);
                variant.quantityrestock                = $.toNumber(formData.quantityrestock);
                variant.shipping_expectation_threshold = $.toNumber(formData.shipping_expectation_threshold);
                variant.shipping_expectation_over      = formData.shipping_expectation_over;
                variant.shipping_expectation_under     = formData.shipping_expectation_under;
                variant.membership_id                  = $.toNumber(formData.membership_id);
                variant.file_type                      = formData.file_type;
                variant.fileid                         = $.toNumber(formData.fileid);
                variant.external_resource_uri          = formData.external_resource_uri;

                variant.media             = [];
                variant.linked_variants   = [];
                variant.metadata          = formData.metadata;

                $.each($form.find('.media'), function(i, mediaEl){
                    var media = $.extend({}, $(mediaEl).data('media'));
                    media.caption = $(mediaEl).find('.media-caption-in').val();
                    variant.media.push(media);
                });

                $.each($form.find('.linked-variant'), function(i, linkedVarEl){
                    variant.linked_variants.push({
                        'id'    : $(linkedVarEl).find('.linked-variant-id').val(),
                        'name'  : $(linkedVarEl).find('.linked-variant-name').val(),
                        'price' : $(linkedVarEl).find('.linked-variant-price').val(),
                        'qty'   : $(linkedVarEl).find('.linked-variant-qty').val()
                    });
                });

                if (variant.file_type === 'external' && variant.external_resource_uri !== '') {
                    variant.file = {};
                    variant.file.type                   = variant.file_type;
                    variant.file.external_resource_uri  = variant.external_resource_uri;
                    variant.file.expiry_time            = formData.file_expiry;
                    variant.file.address_limit          = -1;
                    variant.file.download_limit         = -1;
                    variant.file.description            = formData.file_description;
                } else if (variant.file_type === 'file' && variant.fileid) {
                    variant.file = {};
                    variant.file.type           = variant.file_type;
                    variant.file.fileid        = variant.fileid;
                    variant.file.filename       = $form.find('select[name=fileid] option:selected').html();
                    variant.file.expiry_time    = formData.file_expiry;
                    variant.file.address_limit  = formData.file_address_limit;
                    variant.file.download_limit = formData.file_download_limit;
                    variant.file.description    = formData.file_description;
                } else {
                    variant.file                = null;
                }

                if (variant.membership_id) {
                    if (!variant.membership) {
                        variant.membership = {};
                    }
                    variant.membership.id = variant.membership_id;
                    variant.membership.name = $form.find('select[name=membership_id] option:selected').html();
                } else {
                    variant.membership = null;
                }

                if (formData.is_donation == 1) {
                    variant.is_donation    = true;
                    variant.price          = null;
                    variant.saleprice      = null;
                    variant.is_sale        = false;
                    variant.actual_price   = null;
                    variant.cost           = $.toNumber(formData.cost);
                    variant.price_presets  = formData.price_presets;
                    variant.price_minimum  = $.toNumber(formData.price_minimum);

                } else {
                    variant.is_donation    = false;
                    variant.price          = $.toNumber(formData.price, 0.0);
                    variant.saleprice      = $.toNumber(formData.saleprice);
                    variant.is_sale        = (variant.saleprice)                  ? true : false;
                    variant.actual_price   = (variant.is_sale)                    ? variant.saleprice : variant.price;
                    variant.cost           = $.toNumber(formData.cost);
                    variant.price_presets  = null;
                    variant.price_minimum  = null;
                }

                if (variant.billing_period === 'onetime') {
                    variant.billing_starts_on = null;
                    variant.billing_ends_on = null;
                    variant.total_billing_cycles = null;
                } else {
                    variant.billing_starts_on = formData.billing_starts_on || null;
                    variant.billing_ends_on = formData.billing_ends_on || null;
                    variant.total_billing_cycles = $.toNumber(formData.total_billing_cycles);
                }

                if (formData.isshippable == 2) {
                    variant.isshippable = true;
                    variant.is_shipping_free = true;
                    variant.weight     = $.toNumber(formData.weight);
                } else if (formData.isshippable == 1) {
                    variant.isshippable = true;
                    variant.is_shipping_free = false;
                    variant.weight     = $.toNumber(formData.weight);
                } else {
                    variant.isshippable = false;
                    variant.is_shipping_free = false;
                    variant.weight = null;
                }

                if (formData._update_quantity == 1) {
                    variant._update_quantity = true;
                    variant.quantity         = formData.quantity;
                } else {
                    variant._update_quantity = false;
                    variant.quantity         = null;
                }

                // if the id doesn't already exist in the list of variants, add it
                if ($.grep(productVariants,function(n){ return n.id == variant.id; }).length == 0) {
                    productVariants.push(variant);
                }

                $modal.modal('hide');

                updateJson();
                drawItems(productVariants);
            }

            function onRedirectsToChange(ev) {
                ev.preventDefault();

                if (ev.target.checked) {
                    $('.redirects-to-hide').addClass('hide');
                    $('.redirects-to-show').removeClass('hide');
                } else {
                    $('.redirects-to-show').addClass('hide');
                    $('.redirects-to-hide').removeClass('hide');
                    var input = $('input[name="metadata[redirects_to]"]').get(0);
                    if (input && input.selectize) {
                        input.selectize.clear(true);
                    }
                }
            }

            function onBillingPeriodChange(ev) {
                ev.preventDefault();

                var billing_period = $(ev.target).val();
                if (billing_period === 'onetime') {
                    $('.recurring-price-options').addClass('hide');
                } else {
                    $('.recurring-price-options').removeClass('hide');
                }
            }

            function onChangeDownloadType() {
                var $el = $(this);
                var $modal = $el.closest('.modal');
                if ($el.val() === 'external') {
                    $modal.find('.file-to-deliver, .download-options-devices, .download-options-limit').addClass('hide');
                    $modal.find('.external-uri').removeClass('hide');
                } else {
                    $modal.find('.file-to-deliver, .download-options-devices, .download-options-limit').removeClass('hide');
                    $modal.find('.external-uri').addClass('hide');
                }
            }

            function onPriceTypeChange(ev) {
                ev.preventDefault();

                var is_donation = ($(ev.target).val() == 1);
                if (is_donation) {
                    $('.fixed-price-options').addClass('hide');
                    $('.donation-price-options').removeClass('hide');
                } else {
                    $('.fixed-price-options').removeClass('hide');
                    $('.donation-price-options').addClass('hide');
                }
            }

            function onEmbeddedPreviewLoad() {
                var $previewContainer = $('#embedded-preview-container');
                var selectedTheme = $('input[name="embed_theme"]:checked').val();
                var nextPreviewContainerBackgroundColor = selectedTheme === 'dark' ? '#1a202c' : 'transparent';

                $previewContainer.css('background-color', nextPreviewContainerBackgroundColor);
            }

            function updateEmbedPreviewAndCode() {
                var $preview = $('#embedded-preview');
                var $code = $('#iframe-code');
                var $iframeCode = $($code.text());
                var title = $('#embed-title').val();
                var summary = $('#embed-summary').val();
                var selectedTheme = $('input[name="embed_theme"]:checked').val();
                var selectedPrimaryColor = $('input[name="embed_primary_color"]:checked').val();
                var showGoalProgress = $('input[name="embed_show_goal_progress"]:checked').val();

                var src = $preview.data('src')
                    .replace('[theme]', selectedTheme)
                    .replace('[primaryColor]', selectedPrimaryColor)
                    .replace('[title]', title)
                    .replace('[summary]', summary)
                    .replace('[showGoalProgress]', showGoalProgress ? '&showGoalProgress=1' : '');

                $preview.attr('src', src);
                $iframeCode.attr('src', src);
                $code.empty().text($iframeCode[0].outerHTML.replace(/&amp;/g, "&"));
            }

            function copyEmbedIframeCode(ev) {
                var $button = $(ev.target);
                var $temp = $('<input>');
                var $code = $('#iframe-code');

                $('body').append($temp);
                $temp.val($code.text()).select();
                document.execCommand("copy");

                $button.html('<i class="fa fa-check"></i> Copied');
                $temp.remove();

                setTimeout(function() { $button.text('Copy') }, 4000);
            }

            function onTemplateChange() {
                $('.template-suffix').addClass('hide').attr('disabled', true);
                $('.template-suffix.template-suffix--' + $(this).val()).removeClass('hide').removeAttr('disabled');
            }

            function onChooseTemplate() {
                var $modal = $(this).closest('.modal');
                $('#template-suffix').val($(this).data('suffix')).trigger('change');
                $modal.modal('hide');
            }

            function openVariantModal(variant) {
                var data = Object.assign({}, variant, { php: <?= dangerouslyUseHTML(json_encode([
                    'base_currency_symbol' => $productModel->base_currency->symbol,
                    'dp_custom_fields' => collect(['meta9','meta10','meta11','meta12','meta13','meta14','meta15','meta16','meta17','meta18','meta19','meta20','meta21','meta22'])
                        ->map(function ($field) {
                            return [
                                'name' => $field,
                                'dp_field' => sys_get("dp_{$field}_field"),
                                'dp_label' => sys_get("dp_{$field}_label"),
                                'dp_autocomplete' => sys_get("dp_{$field}_autocomplete"),
                            ];
                        }),
                    'dp_use_nocalc' => sys_get('dp_use_nocalc'),
                    'memberships' => $memberships->map(function ($membership) {
                        return [
                            'id' => $membership->id,
                            'name' => $membership->name,
                        ];
                    }),
                    'shipping_expectation_over' => e(sys_get('shipping_expectation_over')),
                    'shipping_expectation_under' => e(sys_get('shipping_expectation_under')),
                ])) ?> });

                var $modal = j.templates.render('variantModalTmpl', data).appendTo('body'),
                    $form = $modal.find('form');

                $form.data('variant', variant);

                $form.find('input[name=redirects_to_check]')
                    .bind('change', onRedirectsToChange)
                    .change();

                $form.find('select[name=billing_period]')
                    .bind('change', onBillingPeriodChange)
                    .change();

                $form.find('select[name=is_donation]')
                    .bind('change', onPriceTypeChange)
                    .change();

                $modal.find('.edit-quantity-remaining')
                    .bind('click', onUpdateQuantityRemaining);

                $modal.find('.set-as-default').bind('click', onSetDefault);

                $form.bind('submit', onVariantSave);

                $modal.bind('hidden.bs.modal', function(ev){ $modal.remove(); });
                $modal.bind('shown.bs.modal', function(ev){ $modal.find("input[name=variantname]").focus(); });

                $modal.find('.selectize').selectize({
                    'create' : true,
                    'persist' : true,
                    'createOnBlur' : true,
                    'plugins': ['remove_button','drag_drop'],
                });

                var fileTypeEl = $modal.find('select[name=file_type]');
                fileTypeEl.on('change', onChangeDownloadType);
                onChangeDownloadType.apply(fileTypeEl);

                $modal.find('input[name=total_billing_cycles]').on('change', function() {
                    $modal.find('input[name=billing_ends_on]').val('');
                });

                $modal.find('input[name=billing_ends_on]').on('change', function() {
                    $modal.find('input[name=total_billing_cycles]').val('');
                });

                $modal.find('.input-date').each(function(i, el){
                    $(el).datepicker({
                        format: 'M d, yyyy',
                        autoclose:true
                    });
                });

                $.dsDownloads();
                $.dsProducts();

                $modal.find('[data-toggle="popover"]').popover();

                $modal.find('.media-upload').medialy({
                    'media' : variant.media
                });

                $modal.find('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    $.dpoCodes($(e.target).attr('href'));
                });

                configureLinkedVariants(variant.linked_variants);

                $modal.modal('show');

                return $modal;
            }

            function configureLinkedVariants(linked_variants) {
                // selectize
                $.dsVariants();

                // add button
                $('.linked-variant-add').on('click', addLinkedVariant);

                // populate existing links
                populateLinkedVariants(linked_variants);
            }

            function populateLinkedVariants(linkedVariants) {
                $('#linked-variants').empty();

                $.each(linkedVariants, function(i, linkedVariant){
                    var v = linkedVariant;

                    if (typeof linkedVariant.product !== 'undefined') {
                        v = {
                            'name'  : linkedVariant.product.name + ((linkedVariant.variantname) ? (' - '+linkedVariant.variantname) : ''),
                            'qty'   : linkedVariant.pivot.qty,
                            'id'    : linkedVariant.id,
                            'price' : linkedVariant.pivot.price
                        };
                    }

                    drawLinkedVariant(v);
                });

                $('#linked-variant-count').html($('.linked-variant').length);
            }

            function drawLinkedVariant(variant) {
                var $el = _linkedVariantHtml(variant)
                    .data('variant',variant)
                    .appendTo($('#linked-variants'));

                $el.find('.linked-variant-delete')
                    .on('click', deleteLinkedVariant);
            }

            function linkedVariantsChanged() {
                var variant = $('#variant-modal form').data('variant'),
                    total_actual_price = (variant.saleprice) ? variant.saleprice : variant.price;

                $('.linked-variant').each(function(i,v){ total_actual_price += $.toNumber($(v).data('variant').price, 0); });

                variant.total_linked_actual_price = total_actual_price;

                $('#linked-variant-count').html($('.linked-variant').length);
            }

            function deleteLinkedVariant(ev) {
                ev.preventDefault();

                $(this).parents('tr').first().remove();
                linkedVariantsChanged();
            }

            function addLinkedVariant(ev) {
                ev.preventDefault();

                var variant = $('#variant-modal form').data('variant'),
                    selectize = $('#linked-variant-id').data('selectize'),
                    $item = selectize.getItem($('#linked-variant-id').val()),
                    linked_variant = {
                        'id'    : $('#linked-variant-id').val(),
                        'name'  : $item.data('name'),
                        'price' : $('#linked-variant-price').val(),
                        'qty'   : $('#linked-variant-qty').val()
                    };

                if (variant.id == linked_variant.id) {
                    return alert('You cannot link a variant to itself.');
                }

                var dupes = 0;
                $('.linked-variant').each(function(i, el){
                    if ($(el).find('.linked-variant-id').val() == linked_variant.id) {
                        dupes++;
                    }
                });

                if (dupes) {
                    return alert('You cannot link a variant more than once.');
                }

                selectize.clear();
                $('#linked-variant-price').val('0.00');
                $('#linked-variant-qty').val('1');

                drawLinkedVariant(linked_variant);
                linkedVariantsChanged();
            }

            function onTrashClick(ev){
                ev.preventDefault();

                if ($container.find('.data-row').length == 1) {
                    return $.alert('You must have atleast one option.', 'danger', 'fa-exclamation-triangle');
                }

                var $target = $(ev.target),
                    $row = $target.parents('.data-row'),
                    variant = $row.data('variant');

                var _onConfirm = function(){
                    $row.slideUp().promise().done(function(){
                        $(this).remove();

                        variant._is_deleted = true;
                        if (variant.isdefault) {
                            productVariants[0].isdefault = true;
                        }

                        updateJson();
                        drawItems(productVariants);
                    });
                }

                if (!variant._is_new) {
                    $.confirm("<p>Are you sure you want to remove this variant?</p><p class='text-info'><i class='fa fa-info-circle'></i> Note: Sales data for this variant will still be available. This will not affect past contributions.</p>", _onConfirm, 'danger', 'fa-trash');
                } else {
                    _onConfirm();
                }
            }

            function onCopyClick(ev){
                ev.preventDefault();

                var $target = $(ev.target),
                    $row = $target.parents('.data-row'),
                    variant = $row.data('variant');

                var copiedVariant = $.extend(newVariant(),variant);
                copiedVariant._is_new = true;
                copiedVariant.id = (+new Date());
                copiedVariant.sequence = (productVariants.length+1);
                copiedVariant.isdefault = false;
                copiedVariant.variantname += ' (COPY)';

                productVariants.push(copiedVariant);

                drawItems(productVariants);
            }

            function drawItem(variant){

                // make empty relationships null
                if (typeof variant.file == 'undefined') {
                    variant.file = null;
                }
                if (typeof variant.membership == 'undefined') {
                    variant.membership = null;
                }
                if (typeof variant._update_quantity == 'undefined') {
                    variant._update_quantity = false;
                }
                if (typeof variant._is_new == 'undefined') {
                    variant._is_new = false;
                }

                var data = Object.assign({}, variant, { php: <?= dangerouslyUseHTML(json_encode([
                    'base_currency_symbol' => $productModel->base_currency->symbol,
                ])) ?> });

                var $row = j.templates.render('variantRowTmpl', data).appendTo($container);
                $row.data('variant',variant);
                $row.find('.edit-variant').bind('click', onEditClick);
                $row.find('.trash-variant').bind('click', onTrashClick);
                $row.find('.copy-variant').bind('click', onCopyClick);
            }

            function drawItems(variants) {
                $container.empty();

                $.each(variants, function(i, variant){
                    if (typeof variant._is_deleted === 'undefined') {
                        drawItem(variant);
                    }
                });

                $container.sortable({
                    items       : '.data-row',
                    placeholder : "sortable-placeholder",
                    handle      : '.sort-variant',
                    update      : onResequence
                });
            }

            function onUpdateQuantityRemaining(ev) {
                ev.preventDefault();

                var $btn = $(ev.target),
                    $form = $btn.parents('form'),
                    variant = $form.data('variant'),
                    $qty_flag = $form.find('input[name="_update_quantity"]'),
                    $qty_field = $form.find('input[name="quantity"]');

                if ($qty_flag.val() == '0') {
                    $qty_flag.val('1');
                    $qty_field.prop('disabled', false)
                        .removeClass('disabled')
                        .focus();
                    $btn.find('i').removeClass('fa-pencil').addClass('fa-times');
                } else {
                    $btn.data('original_val', $qty_field.val());
                    $qty_flag.val('0');
                    $qty_field.prop('disabled', true)
                        .addClass('disabled')
                        .val(variant.quantity);
                    $btn.find('i').removeClass('fa-times').addClass('fa-pencil');
                }
            }

            function onResequence(ev, ui) {
                var new_variants_array = [];

                $container.find('.data-row').each(function(i, row){
                    new_variants_array[i]          = jQuery.extend({}, $(row).data('variant'));
                    new_variants_array[i].sequence = i+1;
                });

                for (var ix in productVariants) {
                    if (productVariants[ix]._is_deleted) {
                        new_variants_array.push(jQuery.extend({}, productVariants[ix]));
                    }
                }

                productVariants = new_variants_array;

                updateJson();
            }

            function onSetDefault(ev) {
                ev.preventDefault();

                var $btn = $(ev.target),
                    $form = $btn.parents('form'),
                    variant = $form.data('variant');

                $btn.html('<i class="fa fa-check-square-o"></i> Default').addClass('disabled btn-outline');

                $.each(productVariants, function(i, v){ v.isdefault = ((v.id == variant.id) ? 1 : 0); })

                drawItems(productVariants);
            }

            function newVariant() {
                return {
                    '_is_new'            : true,
                    '_update_quantity'   : false,
                    'id'                 : (+new Date()),
                    'sequence'           : (productVariants.length+1),
                    'price'              : 0.00,
                    'weight'             : null,
                    'saleprice'          : null,
                    'actual_price'       : 0.00,
                    'cost'               : null,
                    'sku'                : null,
                    'variantname'        : null,
                    'isdefault'          : null,
                    'billing_period'     : 'onetime',
                    'is_sale'            : false,
                    'is_donation'        : false,
                    'billing_starts_on'  : null,
                    'billing_ends_on'    : null,
                    'total_billing_cycles' : null,
                    'price_presets'      : null,
                    'price_minimum'      : null,
                    'quantitymodifieddatetime' : null,
                    'quantitymodifieddatetime_formatted' : null,
                    'quantity_modified_by_full_name' : null,
                    'shipping_expectation_threshold' : null,
                    'shipping_expectation_over' : null,
                    'shipping_expectation_under' : null,
                    'membership_id'      : null,
                    'membership'         : null,
                    'file'               : null,
                    'isshippable'        : false,
                    'is_shipping_free'   : false,
                    'quantity'           : 0,
                    'quantityrestock'    : null,
                    'fair_market_value'  : null,
                    'linked_variants'    : [],
                    'media'              : [],
                    'metadata'           : []
                };
            }

            // refactor so that data is based on INPUTs in the UI
            // ....

            function _linkedVariantHtml(item) {

                item.price = $.toNumber(item.price);
                if (isNaN(item.price)) {
                    item.price = 0.00;
                }

                return $('<tr class="linked-variant">' +
                    '<td>' + item.name + '</td>' +
                    '<td class="text-center">' + item.qty + '</td>' +
                    '<td class="text-right">' + item.price.formatMoney() + '</td>' +
                    '<td class="text-center">' +
                        '<input type="hidden" class="linked-variant-id" value="' + item.id + '">' +
                        '<input type="hidden" class="linked-variant-name" value="' + item.name + '">' +
                        '<input type="hidden" class="linked-variant-price" value="' + item.price + '">' +
                        '<input type="hidden" class="linked-variant-qty" value="' + item.qty + '">' +
                        '<i class="linked-variant-delete fa fa-times"></i>' +
                    '</td>' +
                '</tr>');
            }

            init();
        })();


        document.querySelector('[name="metadata[dp_syncable]"]').addEventListener('change', function(e){
            document.querySelectorAll('.dp-panel').forEach((el) => {
                    if(e.target.value === '1') {
                        return el.classList.remove('hide');
                    }
                    el.classList.add('hide');
                });
            });
    });
</script>

<style>
    .variant-advanced-options { margin:10px 0px 10px -10px; }
    .variant-advanced-options a { color:#666; margin:2px 10px; text-decoration:underline; text-decoration-style: dotted; }
</style>
