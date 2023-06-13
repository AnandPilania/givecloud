<?php if (\Ds\Models\Product::query()->withoutDonationForms()->withoutTemplates()->count() == 0): ?>

<div class="feature-highlight">
    <img class="feature-img" src="/jpanel/assets/images/icons/online-shop.svg">
    <h2 class="feature-title">Create Donation Pages &amp; Products</h2>
    <p>This is where you'll create donation pages, products, events, registrations, funds, projects and so on.</p>
    <div class="feature-actions">
        <a href="#start-from-template-modal" data-toggle="modal" class="btn btn-lg btn-success btn-pill"><i class="fa fa-plus"></i> Add an Item</a>
        <!--<a href="https://help.givecloud.com/en/collections/931126-receiving-donations-contributions" target="_blank" class="btn btn-lg btn-outline btn-primary btn-pill"><i class="fa fa-book"></i> Learn More</a>-->
    </div>
</div>

<div class="modal fade" id="start-from-template-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add an Item</h4>
            </div>
            <div class="modal-body">

                <p class="text-muted bottom-gutter">Let's help you get started with a couple of templates:</p>

                <?php if($templates->count() > 0): ?>
                    <?php foreach($templates as $template): ?>
                        <a href="/jpanel/products/templates/<?= e($template->id) ?>/create" class="btn btn-block btn-primary"><?= e($template->template_name) ?></a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted text-lg text-center top-gutter bottom-gutter">
                        No Templates
                    </div>
                <?php endif; ?>

                <div class="top-gutter bottom-gutter text-sm text-muted text-center">
                    - OR -
                </div>

                <a href="/jpanel/products/add" class="btn btn-block btn-primary btn-outline">Start from Scratch</a>
            </div>
        </div>
    </div>
</div>

<?php else: ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <?= e($pageTitle) ?>

            <div class="visible-xs-block"></div>

            <?php if(user()->can('product.add')): ?>
                <div class="pull-right">
                    <?php if ($templates->count() > 0): ?>
                        <div class="btn-group">
                            <a href="/jpanel/products/add" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span></a>
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu pull-right">
                                <li class="dropdown-header">Add from Template</li>
                                <?php foreach(\Ds\Models\Product::templates()->get() as $template): ?>
                                    <li><a href="/jpanel/products/templates/<?= e($template->id) ?>/create"><i class="fa fa-plus fa-fw"></i> <?= e($template->template_name) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="/jpanel/products/add" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span></a>
                    <?php endif; ?>
                    <div class="btn-group">
                    <button title="Export account data." type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-download fa-fw"></i><span class="hidden-xs hidden-sm"> Export</span> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right">
                        <li><a onclick="exportRecordsItems(); return false;">Items</a></li>
                        <li><a onclick="exportRecordsOptions(); return false;">Variant Options</a></li>
                    </ul>
                </div>
                </div>
            <?php endif; ?>

        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<div class="row">
    <form class="datatable-filters">
        <input type="hidden" name="fA" value="<?= e(request('fA')); ?>">

        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control delay-filter" name="fb" id="fb" value="" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter products by:<br><i class='fa fa-check'></i> Name<br><i class='fa fa-check'></i> Code<br><i class='fa fa-check'></i> Summary" />
                </div>
            </div>


            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Category</label>
                <select name="fc" class="form-control" placeholder="<?= e(sys_get('ecomm_syn_author')) ?>">
                    <option value="">Any Category</option>
                    <?= dangerouslyUseHTML(product_catCurs(0)) ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label"><?= e(sys_get('ecomm_syn_author')) ?></label>
                <select name="fa" class="form-control" placeholder="<?= e(sys_get('ecomm_syn_author')) ?>">
                    <option value="">Any <?= e(sys_get('ecomm_syn_author')) ?></option>
                    <?php foreach (\Ds\Models\Product::filterOptions() as $filter): ?>
                        <option value="<?= e($filter) ?>" <?= dangerouslyUseHTML((request('fa') == $filter)?'selected="selected"':'') ?>><?= e($filter) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Status</label>
                <select name="fd" class="form-control" placeholder="Status">
                    <option value="0" <?= dangerouslyUseHTML((request('fd') == 0)?'selected="selected"':'') ?>>Active Products</option>
                    <option value="1" <?= dangerouslyUseHTML((request('fd') == 1)?'selected="selected"':'') ?>>Deleted Products</option>
                    <option value="2" <?= dangerouslyUseHTML((request('fd') == 2)?'selected="selected"':'') ?>>Templates</option>
                </select>
            </div>

            <?php if(dpo_is_connected()): ?>
            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Syncs to DP</label>
                <select name="dp_sync" class="form-control">
                    <option value>Show All</option>
                    <option value="1" <?= e(volt_selected('1', request('dp_sync'))); ?>>Yes</option>
                    <option value="0" <?= e(volt_selected('0', request('dp_sync'))); ?>>No</option>
                </select>
            </div>
            <?php endif; ?>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Has Shippable Options</label>
                <select name="is_shippable" class="form-control">
                    <option value>Show All</option>
                    <option value="Yes" <?= dangerouslyUseHTML((request('is_shippable') == 'Yes')?'selected="selected"':'') ?>>Yes</option>
                    <option value="No" <?= dangerouslyUseHTML((request('is_shippable') == 'No')?'selected="selected"':'') ?>>No</option>
                </select>
            </div>

            <div class="form-group pt-1 px-2">
                <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
            </div>

        </div>
    </form>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table id="product-list" class="table table-v2 table-striped table-hover responsive">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th width="250">Options</th>
                        <th>Url</th>
                        <th>Filter</th>
                        <th width="120" class="text-right">Goal</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    spaContentReady(function() {

        if ($('#product-list').length > 0) {
            var products_table = $('#product-list').DataTable({
                "dom": 'rtpi',
                "iDisplayLength" : 20,
                "autoWidth": false,
                "fixedHeader" : true,
                "processing": true,
                "serverSide": true,
                "order": [[ 0, "asc" ]],
                "columnDefs": [
                    {"class":"text-left", "targets":0},
                    {"class":"text-left", "targets":1, "orderable": false},
                    {"class":"text-left", "targets":2},
                    {"class":"text-left", "targets":3},
                    {"class":"text-right", "targets":4, "orderable": false},
                    //{ "orderable": false, "targets": 0}
                ],
                "stateSave": false,
                "ajax": {
                    "url": "/jpanel/products.ajax",
                    "type": "POST",
                    "data": function (d) {
                        fields = $('.datatable-filters').serializeArray();
                        $.each(fields,function(i, field){
                            d[field.name] = field.value;
                        })
                    }
                }
            });

            $('.datatable-filters input, .datatable-filters select').not(':hidden').each(function(i, input){
                if ($(input).data('datepicker'))
                    $(input).on('changeDate', function () {
                        products_table.draw();
                    });

                else
                    $(input).change(function(){
                        products_table.draw();
                    });
            });

            $('form.datatable-filters').on('submit', function(ev){
                ev.preventDefault();
            });

            j.ui.datatable.enableFilters(products_table);
        }
    });
</script>

<?php endif; ?>

<script>
    exportRecordsItems = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/products.csv?' + $.param(d);
    }
    exportRecordsOptions = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/products/variants.csv?' + $.param(d);
    }
</script>
