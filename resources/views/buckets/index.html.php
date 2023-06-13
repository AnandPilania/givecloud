<style>
    #customizations-pane { height:400px; -webkit-overflow-scrolling: touch; overflow-x:hidden; overflow-y:auto; min-height:300px; }
</style>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <?php if ($hasUnlockedTheme): ?>
                    <a href="/jpanel/design" class="btn btn-default"><i class="fa fa-th-large"></i> Browse Themes</a>
                <?php endif ?>

                <?php if(user()->can('customize.edit')): ?><a href="#" id="save-button" onclick="$('#settings_form').submit();" data-loading-text="<i class='fa fa-spinner fa-spin '></i>" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Save</a><?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<form role="form" id="settings_form" name="settings" action="/jpanel/design/customize/save" method="post">
<?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-sm-4 col-md-3">

        <div class="input-group input-group-lg input-group-transparent bottom-gutter">
            <div class="input-group-addon"><i class="fa fa-search"></i></div>
            <input type="search" class="form-control setting-search" placeholder="Search...">
            <div class="input-group-btn"><button class="btn btn-default reset-search" type="button"><i class="fa fa-times"></i></button></div>
        </div>

        <ul class="list-group" role="tablist" data-tabs="tabs">
            <a href="#basics" role="tab" data-toggle="tab" class="list-group-item stop-search">Basics</a>
            <?php foreach ($std_categories as $category): ?>
                <a href="#<?= e(\Illuminate\Support\Str::slug($category)) ?>" role="tab" data-toggle="tab" class="list-group-item stop-search"><?= e($category) ?></a>
            <?php endforeach; ?>
        </ul>
        <ul class="list-group" role="tablist" data-tabs="tabs">
            <?php foreach ($adv_categories as $category): ?>
                <a href="#<?= e(\Illuminate\Support\Str::slug($category)) ?>" role="tab" data-toggle="tab" class="list-group-item stop-search">
                    <?php if ($category === 'Advanced'): ?><i class="fa fa-cogs fa-fw"></i><?php endif ?> <?= e($category) ?>
                </a>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="col-sm-8 col-md-9">
        <div id="customizations-pane">
            <div class="search-status text-center text-muted hide">
                <i class="fa fa-search fa-3x bottom-gutter"></i><br>
                Type something to search
            </div>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="__loading">
                    <div class="text-muted text-center top-gutter text-lg">
                        <i class="fa fa-4x fa-spinner fa-spin"></i>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane" id="basics">
                    <?php include '_basics.html.php' ?>
                </div>

                <?php foreach ($categories as $category): ?>
                    <div role="tabpanel" class="tab-pane setting-tab" id="<?= e(\Illuminate\Support\Str::slug($category->name)) ?>">

                    <?php include '_settings.html.php' ?>

                    <?php if(user()->can('template.edit') && $category->name == 'Custom'): ?>
                        <a href="/jpanel/design/customize/add" class="btn btn-success"><i class="fa fa-plus"></i> Add Customization</a>
                    <?php endif; ?>

                    </div>

                <?php endforeach ?>
            </div>
        </div>
    </div>
</div>
