
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Kiosks

            <div class="pull-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-create-kiosk" style="vertical-align:top; margin-top:3px;">
                    <i class="fa fa-plus fa-fw"></i><span class="hidden-sm hidden-xs"> Create</span>
                </button>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>


<?php if (count($kiosks) === 0): ?>

<div class="row">
    <div class="text-muted text-center col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-12">
        <h2>
            <i class="fa fa-4x fa-desktop bottom-gutter"></i><br>
            Raise More Money
        </h2>
        <p class="text-lg">Allow your donors to donate directly from a kiosk station.</p>
        <button type="button" class="btn btn-lg btn-pill btn-primary" data-toggle="modal" data-target="#modal-create-kiosk">
            Create Your First Kiosk &nbsp;<i class="fa fa-plus"></i>
        </button>
    </div>
</div>

<?php else: ?>

<div class="row kiosks-list">
<?php foreach ($kiosks as $kiosk): ?>
    <div class="col-sm-12 col-md-3">
        <a class="panel panel-default" href="/jpanel/kiosks/<?= e($kiosk->id) ?>">
            <div class="panel-body">
                <div class="text-center">
                    <i class="fa fa-4x fa-desktop"></i>
                </div>
            </div>
            <div class="panel-footer">
                <div class="text-center">
                    <h4><?= e($kiosk->name) ?></h4>
                </div>
            </div>
        </a>
    </div>
<?php endforeach ?>
</div>

<?php endif ?>


<div class="modal fade modal-success" tabindex="-1" role="dialog" id="modal-create-kiosk">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/jpanel/api/v1/kiosks">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fa fa-plus-square" aria-hidden="true"></i> CREATE KIOSK
                    </h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="inputName" class="sr-only">Name</label>
                        <input id="inputName" type="text" class="form-control input-lg" name="name" placeholder="Name" required>
                    </div>
                    <div class="form-group">
                        <label for="inputProduct" class="sr-only">Product</label>
                        <select id="inputProduct" class="form-control input-lg ds-products" name="product_id" placeholder="Product" required></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="text-left">
                        <button type="submit" class="btn btn-default" data-style="expand-left" data-spinner-color="#ccc"><strong>Create</strong></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<style>
.kiosks-list a.panel {position:relative;display:block;color:#333;}
.kiosks-list a.panel:hover {text-decoration:none;top:-1px;left:-1px;}
.kiosks-list a.panel .panel-body .fa {font-size:140px}
</style>

<script>
spaContentReady(function($){

    var $modal = $('#modal-create-kiosk').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    var $form = $modal.find('form');
    var $submitBtn = Ladda.create($form.find('button[type=submit]')[0]);
    var $cancelBtn = $form.find('button[type=button]');

    $modal.on('show.bs.modal', function(event) {
        $form.find('input').val('');
        $submitBtn.stop();
        $cancelBtn.prop('disabled', false);
    });

    $form.on('submit', function(event) {
        event.preventDefault();

        $submitBtn.start();
        $cancelBtn.prop('disabled', true);

        $.post(this.action, $form.serializeArray())
            .done(function(res) {
                window.location.href = '/jpanel/kiosks/' + res.kiosk.id;
            });
    });

});
</script>
