
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this snippet?');
        if (f) {
            document.posting.action = '/jpanel/feeds/posts/destroy';
            document.posting.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <?= e($pageTitle) ?>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <?php if($post->userCan('edit') && $post->deleted_at == ''): ?>
                    <a onclick="$('#post-form').submit();" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-xs hidden-sm"> Save</span></a>
                    <a onclick="onDelete();" class="btn btn-danger <?= e(($isNew == 1) ? 'hidden' : '') ?>"><i class="fa fa-times"></i><span class="hidden-xs hidden-sm"> Delete</span></a>
                <?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<form name="posting" method="post" id="post-form" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($post->id) ?>" />
    <input type="hidden" name="type_id" value="<?= e($post->type); ?>" />

    <div class="form-horizontal">

        <div class="form-group">
            <input type="text" class="form-control input-lg" name="name" id="name" value="<?= e($post->name) ?>" maxlength="64" placeholder="Name">
        </div>

        <div class="form-group">
            <label for="url" class="sr-only control-label">Body</label>
            <?php if ($post->misc1 === 'liquid'): ?>
                <input id="inputBody" type="hidden" name="body" value="<?= e($post->body) ?>" />
                <div class="code-editor" data-mode="liquid" data-input="#inputBody" style="width:100%; height:600px;"></div>
            <?php else: ?>
                <textarea class="html form-control" style="height:300px;" name="body"><?= e($post->body); ?></textarea>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="misc1" class="control-label pull-left">Content type</label>
            <select name="misc1" id="misc1" class="form-control" style="display: inline-block; margin-left: 8px; width: 120px;">
                <option <?= e(volt_selected('html', $post->misc1, 'html')); ?> value="html">HTML</option>
                <option <?= e(volt_selected('liquid', $post->misc1)); ?> value="liquid">Liquid</option>
            </select>
        </div>

    </div>
</form>
