
<script>
    exportRecords = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/reports/products.csv?' + $.param(d);
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($title) ?>
            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <a class="btn btn-default" onclick="exportRecords(); return false;"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span></a>
            </div>
        </h1>
    </div>
</div>

<div class="row">
    <form class="datatable-filters">

        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" name="search" id="filterSearch" value="" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter Products by:<br><i class='fa fa-check'></i> Code &amp; Name<br><i class='fa fa-check'></i> Variant Name<br><i class='fa fa-check'></i> Receipt Number" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Status</label>
                <select name="is_deleted" class="form-control" placeholder="Status">
                    <option value="0">Active Products</option>
                    <option value="1">Deleted Products</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control selectize" placeholder="Any Category">
                    <option value=""></option>
                    <?php
                        $catCurs = function ($parentid, $level=0) use (&$catCurs) {
                            $returnStr = '';
                            $qNode = db_query(sprintf("SELECT c.id,
                                        c.sequence,
                                        c.parent_id,
                                        c.name,
                                        LCASE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.name,')',''),'(',''),'''',''),'/',''),'&',''),'-',''),'~',''),'?',''),' ','')) AS url
                                    FROM productcategory c
                                    WHERE IFNULL(c.parent_id,0) = %d
                                    ORDER BY c.sequence",
                                $parentid
                            ));
                            while ($cat = db_fetch_object($qNode)) {
                                $returnStr .= '<option value="'.$cat->id.'" '.((request('fc') == $cat->id)?'selected="selected"':'').'>';

                                if ($level > 0) {
                                    $x = 0;
                                    while($x < $level) { $returnStr .= '&nbsp;&nbsp;&nbsp;&nbsp;'; $x = $x+1; } // add spaces
                                }

                                $returnStr .= e($cat->name).'</option>';
                                // recurs
                                $returnStr .= $catCurs($cat->id,$level+1);
                            }
                            return $returnStr;
                        };

                    ?>
                    <?= dangerouslyUseHTML($catCurs(0)) ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Ordered on</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                    <input type="text" class="form-control" name="ordered_at_str" value="" placeholder="Ordered on..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="ordered_at_end" value="" />
                </div>
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
            <table class="table table-striped table-bordered table-hover" id="ordersByProductTable">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>First Purchase</th>
                        <th>Last Purchase</th>
                        <th>Contributions</th>
                        <th>Total Qty</th>
                        <th>Total Amount (<?= e(currency()->code) ?>)</th>
                        <th style="width:145px;"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
