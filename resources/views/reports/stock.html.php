
<div class="row clearfix">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($title) ?>

            <div class="pull-right">
                <a href="/jpanel/reports/stock.csv?fc=<?= e(request('fc')) ?>&fa=<?= e(request('fa')) ?>" class="btn btn-default"><i class="fa fa-download fa-fw"></i><span class="hidden-xs hidden-sm"> Export</span></a>
            </div>
        </h1>
    </div>
</div>

<div class="row hidden-print">
    <div class="col-lg-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <i class="fa fa-filter fa-fw"></i> Filter Options
            </div>
            <form>
                <div class="panel-body">


                    <div class="form-group col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label>Category</label>
                        <select class="form-control" name="fc" id="fc">
                            <option value="">Any</option>
                            <?= dangerouslyUseHTML(product_catCurs(0)); ?>
                        </select>
                    </div>

                    <div class="form-group col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label><?= e(sys_get('ecomm_syn_author')) ?></label>
                        <select class="form-control" name="fa" id="fa">
                            <option value="">Any</option>
                            <?php $qFilter = db_query(sprintf("SELECT DISTINCT author FROM product WHERE author != '' AND author IS NOT NULL ORDER BY author")); ?>
                            <?php while($filter = db_fetch_object($qFilter)): ?>
                                <option value="<?= e($filter->author) ?>" <?= dangerouslyUseHTML((request('fa') == $filter->author)?'selected="selected"':'') ?>><?= e($filter->author) ?></option>
                            <?php endwhile ?>
                        </select>
                    </div>

                </div>
                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary"><i></i>Filter List</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Name</th>
                        <th>Code</th>
                        <th style="text-align:center;">Qty</th>
                        <th>Last Updated</th>
                        <th style="text-align:center;">Purchased</th>
                        <th style="text-align:center;">Remaining</th>
                        <th style="text-align:center;">Restock At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = db_fetch_assoc($qList)) { ?>
                        <tr <?= dangerouslyUseHTML(($r['restockflag'] == '1')?'class="red"':'') ?>>
                            <td width="16"><a href="/jpanel/products/edit?i=<?= e($r['productid']) ?>"><i class="fa fa-search"></i></a></td>
                            <td><?= e($r['name']) ?><?= dangerouslyUseHTML(($r['variantname'] != '')?' ('.$r['variantname'].')':'') ?></td>
                            <td><?= e($r['code']) ?></td>
                            <td align="center"><?= e($r['quantitylastreported']) ?></td>
                            <td data-order="<?= e(toLocalFormat($r['quantitylastreportedat'],'U')) ?>"><?= e(toLocalFormat($r['quantitylastreportedat'], 'fdate')) ?></td>
                            <td align="center"><?= e($r['quantitypurchased']) ?></td>
                            <td align="center" style="font-weight:bold;">
                                <?php
                                    if ($r['quantityremaining'] > $r['quantityrestock']) {
                                        echo number_format($r['quantityremaining']);
                                    } else {
                                        echo '<span class="label label-danger">' . number_format(max(0, $r['quantityremaining'])) . '</span>';
                                    }
                                ?>
                            </td>
                            <td align="center"><?= e($r['quantityrestock']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
