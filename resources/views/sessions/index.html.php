<div id="home_page_content">
    <div class="row">
        <div class="text-muted top-gutter-lg text-center"><img src="/jpanel/assets/images/spinner.gif" class="spinner"> Loading...</div>
    </div>
</div>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script>
    function drawGeoCharts() {
        $('#geo-chart').each(function (_, chart) {
            var $chart = $(chart);
            var data = $chart.data('geo-data');
            var opts = $chart.data('geo-options');
            var data = google.visualization.arrayToDataTable(data);
            var geochart = new google.visualization.GeoChart(chart);
            geochart.draw(data, opts);
        });
    }
    spaContentReady(function() {
        $.ajax({
            'method' : 'post',
            'url'    : '/jpanel/dashboard',
            'success': function(response){
                document.getElementById('home_page_content').innerHTML = response;
                j.charts.init();
                google.load("visualization", "1", {packages:["geochart"], callback: drawGeoCharts});
            },
            'type'   : 'html'
        });

        <?php if($message = session('_flashMessages.success')): ?>
            toastr.success(<?= dangerouslyUseHTML(json_encode($message)) ?>);
        <?php endif; ?>
    });
</script>
