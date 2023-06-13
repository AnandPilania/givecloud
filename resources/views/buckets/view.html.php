
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this content?');
        if (f) {
            document.setting.action = '/jpanel/design/customize/destroy';
            document.setting.submit();
        }
    }

    function newCategory () {
        $('#category_wrap').empty().append($('<input type="text" class="form-control" name="category" id="category" value="" maxlength="500" />'));
        $('#category').focus();
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <a onclick="$('#settingForm').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Save</a>
                <a onclick="onDelete();" class="btn btn-danger"><i class="fa fa-times fa-fw"></i> Delete</a>
            </div>
        </h1>
    </div>
</div>

<form name="setting" class="form-horizontal" id="settingForm" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($setting->id) ?>" />

    <div class="form-group">
        <label class="col-sm-3 control-label">Name:</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" name="name" id="name" value="<?= e($setting->name) ?>" maxlength="500" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">Label:</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" name="label" id="description" value="<?= e($setting->label) ?>" maxlength="500" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">Type:</label>
        <div class="col-sm-4">
            <select id="type" name="type" class="form-control">
                <option value="text"     <?php if ($setting->type == 'text')     echo 'selected="selected"'; ?>>Text</option>
                <option value="bigText"  <?php if ($setting->type == 'bigText')  echo 'selected="selected"'; ?>>Big Text</option>
                <option value="html"     <?php if ($setting->type == 'html')     echo 'selected="selected"'; ?>>HTML</option>
                <option value="image"    <?php if ($setting->type == 'image')    echo 'selected="selected"'; ?>>Image</option>
                <option value="color"    <?php if ($setting->type == 'color')    echo 'selected="selected"'; ?>>Color</option>
                <option value="link"     <?php if ($setting->type == 'link')     echo 'selected="selected"'; ?>>Link</option>
                <option value="on-off"   <?php if ($setting->type == 'on-off')   echo 'selected="selected"'; ?>>On/Off Switch</option>
                <option value="product"  <?php if ($setting->type == 'product')  echo 'selected="selected"'; ?>>Product</option>
                <option value="raw-html" <?php if ($setting->type == 'raw-html') echo 'selected="selected"'; ?>>Raw HTML</option>
                <option value="css"      <?php if ($setting->type == 'css')      echo 'selected="selected"'; ?>>CSS</option>
                <option value="js"       <?php if ($setting->type == 'js')       echo 'selected="selected"'; ?>>JavaScript</option>
            </select>
        </div>
    </div>

    <div class="form-group hide">
        <label class="col-sm-3 control-label">Category:</label>
        <div class="col-sm-6" id="category_wrap">
            <div class="input-group">
                <select id="category" name="category" class="form-control">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category->name) ?>" <?php if ($category->name == $setting->category) echo 'selected="selected"'; ?>><?= e($category->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="input-group-btn">
                    <button class="btn btn-info" type="button" onclick="newCategory();"><i class="fa fa-plus"></i> New</button>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">Hint:</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" name="info" id="hint" value="<?= e($setting->info) ?>" maxlength="500" />
        </div>
    </div>

</form>
