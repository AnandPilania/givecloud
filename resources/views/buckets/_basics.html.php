<div class="panel panel-default setting-panel form-horizontal">
    <div class="panel-heading">Basics</div>
    <div class="panel-body">

        <div class="form-group setting" data-search="default logo">
            <label class="col-sm-4 control-label">
                Default Logo:
                <p><small class="text-muted">Default logo</small></p>
            </label>
            <div class="col-sm-8">
                <?php if(trim(sys_get('default_logo')) !== ''): ?><img src="<?= e(sys_get('default_logo')) ?>" style="margin-bottom:7px; height: 35px;" /><?php endif; ?>
                <div class="input-group">
                    <input type="text" class="form-control" value="<?= e(sys_get('default_logo')) ?>" name="basics[default_logo]" id="__default_logo" />
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default image-browser" data-image-browser-output="__default_logo"><i class="fa fa-folder-open-o"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group setting" data-search="default color scheme primary">
            <label class="col-sm-4 control-label">
                Primary Color
            </label>
            <div class="col-sm-8 col-md-3">
                <input class="form-control color-picker" name="basics[default_color_1]" value="<?= e(sys_get('default_color_1')) ?>">
            </div>
        </div>

        <div class="form-group setting" data-search="default color scheme secondary">
            <label class="col-sm-4 control-label">
                Secondary Color
            </label>
            <div class="col-sm-8 col-md-3">
                <input class="form-control color-picker" name="basics[default_color_2]" value="<?= e(sys_get('default_color_2')) ?>">
            </div>
        </div>

        <div class="form-group setting" data-search="default color scheme alternate">
            <label class="col-sm-4 control-label">
                Alternate Color
            </label>
            <div class="col-sm-8 col-md-3">
                <input class="form-control color-picker" name="basics[default_color_3]" value="<?= e(sys_get('default_color_3')) ?>">
            </div>
        </div>

    </div>
</div>

<?php
    if ($basics) {
        $category = $basics;
        include '_settings.html.php';
    }
?>
