
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($segment->name) ?> Options

            <div class="pull-right">
                <a href="/jpanel/sponsorship/segments/items/add?s=<?= e($segment->id) ?>" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span></a>
            </div>
        </h1>
    </div>
</div>

<?php if($segment->is_geographic == 1): ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fa-globe"></i> Geographic View
        </div>
        <div class="panel-body" style="padding:0px;">

                <script>
                spaContentReady(function() {
                    var map;

                    function initialize() {

                        var mapOptions = {
                            zoom: 1,
                            center: new google.maps.LatLng(30,0),
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        }
                        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

                        <?php foreach ($segment->items as $item): if(is_numeric($item->latitude) && is_numeric($item->longitude)): ?>
                        new google.maps.Marker({
                            position: new google.maps.LatLng(<?= e($item->latitude) ?>,<?= e($item->longitude) ?>),
                            map: map,
                            title: <?= dangerouslyUseHTML(json_encode($item->name)) ?>,
                            clickable : true
                        });
                        <?php endif; endforeach; ?>
                    }

                    function centerOn(lat,long) {
                        map.setCenter(new google.maps.LatLng(lat,long));
                        map.setZoom(4);
                    }

                    initialize();
                });
                </script>
                <div id="map-canvas" style="width:100%; height:250px;"></div>

        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th style="width:16px;"></th>
                        <th>Name</th>
                        <?php if($segment->is_simple != 1): ?><th>Link</th><?php endif; ?>
                        <?php if($segment->is_geographic == 1): ?><th style="width:180px;">Lat/Long</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($segment->items as $item): ?>
                    <tr>
                        <td width="16"><a href="/jpanel/sponsorship/segments/items/edit?i=<?= e($item->id) ?>"><i class="fa fa-search"></i></a></td>
                        <td><?= e($item->name) ?></td>
                        <?php if($segment->is_simple != 1): ?><td><a href="<?= e($item->link) ?>" target="_blank"><?= e($item->link) ?></a><?= dangerouslyUseHTML(($item->target == '_blank') ? ' <span class="grey">(New Window)</span>' : '') ?></td><?php endif; ?>
                        <?php if($segment->is_geographic == 1): ?><td><?php if(is_numeric($item->latitude) && is_numeric($item->longitude)): ?><a href="javascript:void(0);" onclick="centerOn(<?= e($item->latitude) ?>,<?= e($item->longitude) ?>);"><?= e($item->latitude) ?>,<?= e($item->longitude) ?></a><?php endif; ?></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
