<?php if (\Ds\Models\ProductCategory::count() == 0): ?>

<div class="feature-highlight">
    <img class="feature-img" src="/jpanel/assets/images/icons/categorize.svg">
    <h2 class="feature-title">Organize Your Products &amp; Donations</h2>
    <p>This is where you can organize and group specific types of products or donations.</p>
    <div class="feature-actions">
        <a href="/jpanel/products/categories/add" class="btn btn-lg btn-success btn-pill"><i class="fa fa-plus"></i> Add a Category</a>
        <!--<a href="https://help.givecloud.com/en/collections/931126-receiving-donations-contributions" target="_blank" class="btn btn-lg btn-outline btn-primary btn-pill"><i class="fa fa-book"></i> Learn More</a>-->
    </div>
</div>

<?php else: ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <?php if(user()->can('productcategory.add')): ?>
                <div class="pull-right">
                    <a href="/jpanel/products/categories/add" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span></a>
                </div>
            <?php endif; ?>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<div class="rounded bg-white p-8">
    <ul class="dir">
        <?php
            $category_macro = function($parentid) use (&$category_macro) {
                $returnStr = '';
                $categories = \Ds\Models\ProductCategory::withIsLocked()
                    ->parentId($parentid)
                    ->orderBy('sequence')
                    ->get();

                if (count($categories) === 0)
                    return '';

                foreach ($categories as $category) {
                    $urlA = '/'.$category->url_name;
                    $returnStr .= '<li>';
                        $returnStr .= '<a href="/jpanel/products/categories/edit?i='.$category->id.'"><i class="fa fa-tags"></i> '.$category->name.'</a>';
                        if ($category->is_locked == 1) $returnStr .= '&nbsp;<i class="fa fa-lock text-muted" data-placement="top" data-toggle="popover" data-trigger="hover" data-content="<i class=\'fa fa-lock fa-4x fa-fw pull-left\'></i> This category can only be viewed by accounts with membership." ></i>';
                        if ($urlA != '') $returnStr .= '&nbsp;<span class="linkPreview hidden-xs hidden-sm"><a href="'.$urlA.'" target="_blank">'.$urlA.'</a></span>';
                        $returnStr .= '<ul>'.$category_macro($category->id).'</ul>';
                    $returnStr .= '</li>';
                }

                return $returnStr;
            };
        ?>
        <?= dangerouslyUseHTML($category_macro(0)) ?>
    </ul>
</div>

<?php endif; ?>
