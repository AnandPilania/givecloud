
<form action="/jpanel/reports/inventory-export.csv" method="post">
<?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Inventory Sold
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<div class="row">

    <div class="col-lg-6 col-lg-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-basic">
            <div class="panel-body">
                <div class="bottom-gutter">
                    <div class="panel-sub-title">1. Select Items</div>

                    <div class="form-group">
                        <!--<label>Category(s)</label>-->
                        <select required class="form-control selectize keep-open" size="1" placeholder="Choose Category(s)..." name="category_ids[]" multiple="multiple">
                            <?php foreach(\Ds\Models\ProductCategory::topLevel()->with('childCategories.childCategories.childCategories.childCategories')->orderBy('sequence')->get() as $cat1): ?>
                                <option value="<?= e($cat1->id) ?>" ><?= e($cat1->name) ?></option>
                                <?php if($cat1->childCategories): foreach($cat1->childCategories as $cat2): ?>
                                    <option value="<?= e($cat2->id) ?>" ><?= e($cat1->name) ?> &gt; <?= e($cat2->name) ?></option>
                                <?php endforeach; endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="bottom-gutter">
                    <div class="panel-sub-title">2. Date Range</div>

                    <div class="form-group">
                        <div class="input-group input-daterange" style="max-width:280px;">
                            <input type="text" class="form-control" name="start_date" value="" required>
                            <div class="input-group-addon">to</div>
                            <input type="text" class="form-control" name="end_date" value="" required>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="panel-sub-title">3. Download Report</div>
                    <button class="btn btn-lg btn-success"><i class="fa fa-fw fa-download"></i> Download</button>
                </div>
            </div>
        </div>
    </div>

</div>

</form>
