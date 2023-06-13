
@extends('layouts.app')
@section('title', 'Monitor Import')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Import
        </h1>
    </div>
</div>

<div class="row">
    <div class="import-monitor hide col-lg-4 col-lg-offset-4 col-sm-6 col-sm-offset-3 col-xs-8 col-xs-offset-2 text-center" data-import-id="{{ $import->id }}">

        <i class="fa fa-5x fa-spin fa-circle-o-notch top-gutter"></i><br>
        <h1></h1>
        <p class="file-description"><i class="fa fa-file-o"></i> {{ $import->file_name }}</p>

        <div class="progress top-gutter">
            <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 0%"></div>
        </div>

        <p class="info"></p>

        {{--<p class="countdown">Approximately 23min 1sec remaining...</p>--}}

    </div>
</div>

<script>
spaContentReady(function() {

    $('.import-monitor').each(function(i, e){
        var $e = $(e),
            import_id = $e.data('import-id'),
            estimated_minutes_remaining = false;

        $e.find('.progress-bar').css({
            '-webkit-transition': 'none',
            '-o-transition': 'none',
            'transition': 'none'
        });

        check = function(){

            var success = function (json) {

                $e.removeClass('hide');

                // make the estimates more consistent by averaging
                // the new estimate with the old estimate
                if (json.estimated_minutes_remaining) {
                    if (estimated_minutes_remaining === false) {
                        estimated_minutes_remaining = json.estimated_minutes_remaining;
                    }
                    estimated_minutes_remaining = (json.estimated_minutes_remaining + estimated_minutes_remaining) / 2;
                }

                if (json.stage == 'aborted') {

                    if ($e.find('h1').text() !== 'Aborted') {

                        $e.find('h1').html('Aborted');

                        $e.find('.progress-bar').velocity('stop').css('width', '100%');

                        $e.find('.progress-bar').addClass('progress-bar-danger')
                            .removeClass('progress-bar-striped active');

                        $e.find('.fa').addClass('text-danger fa-exclamation-triangle')
                            .removeClass('fa-circle-o-notch fa-spin');

                        if (json.started_at) {
                            $e.find('.info').html('Imported '+json.added_records.formatMoney(0)+' row(s). Updated '+json.updated_records.formatMoney(0)+' row(s). '+json.error_records.formatMoney(0)+' error(s).<br>' +
                                ( (json.status_message) ? '<strong>'+json.status_message+'</strong><br>' : '' ) +
                                '<br><a href="/jpanel/import/'+import_id+'/import-messages" class="btn btn-default"><i class="fa fa-search"></i> View Messages</a>');
                        } else {
                            $e.find('.info').html('Analyzed '+(json.analyzed_ok_records+json.analyzed_warning_records).formatMoney(0)+' row(s). '+json.analyzed_warning_records.formatMoney(0)+' warning(s).<br>' +
                                ( (json.status_message) ? '<strong>'+json.status_message+'</strong><br>' : '' ) +
                                '<br><a href="/jpanel/import/'+import_id+'/analysis-messages" class="btn btn-default"><i class="fa fa-search"></i> View Messages</a>');
                        }
                    }

                } else if (json.stage == 'import_ready') {

                    if ($e.find('h1').text() !== 'Ready to Import') {

                        $e.find('h1').html('Ready to Import');

                        $e.find('.progress-bar').velocity('stop').css('width', '100%');


                        if (json.analyzed_warning_records > 0) {

                            $e.find('.progress-bar').addClass('progress-bar-warning')
                                .removeClass('progress-bar-striped active');

                            $e.find('.fa').addClass('text-warning fa-exclamation-triangle')
                                .removeClass('fa-circle-o-notch fa-spin');

                        } else {
                            $e.find('.progress-bar').addClass('progress-bar-success')
                                .removeClass('progress-bar-striped active');

                            $e.find('.fa').addClass('text-success fa-check')
                                .removeClass('fa-circle-o-notch fa-spin');
                        }

                        $e.find('.info').html(
                            ((json.status_message)?(json.status_message+'<br>'):'') +
                            '<a href="/jpanel/import/'+import_id+'/analysis-messages">('+json.analyzed_warning_records+') Warning Messages.</a><br><br>' +
                            '<a href="/jpanel/import/'+import_id+'/start-import" class="btn btn-success"><i class="fa fa-check"></i> Start Import</a> ' +
                            '<a href="/jpanel/import/'+import_id+'/abort" class="btn btn-outline btn-danger"><i class="fa fa-ban"></i> Cancel</a>');
                    }

                } else if (json.stage == 'analysis_queued') {

                    $e.find('.progress-bar').velocity('stop').css('width', '100%');

                    if (json.error_message) {
                        $e.find('.progress-bar').addClass('progress-bar-danger')
                            .removeClass('progress-bar-striped active');

                        $e.find('.fa').addClass('text-danger fa-exclamation-triangle')
                            .removeClass('fa-circle-o-notch fa-spin');

                        $e.find('.info').html(json.error_message + '<br><br><a href="/jpanel/import/'+import_id+'/log" class="btn btn-default"><i class="fa fa-search"></i> View Log</a>');

                        $e.find('h1').html('Error');

                    } else {

                        $e.find('.progress-bar').addClass('progress-bar-success')
                            .removeClass('progress-bar-striped active');

                        $e.find('.fa').addClass('text-success fa-check')
                            .removeClass('fa-circle-o-notch fa-spin');

                        $e.find('.info').html('Imported '+json.added_records.formatMoney(0)+' record(s). Updated '+json.updated_records.formatMoney(0)+' record(s). '+json.error_records.formatMoney(0)+' error(s).<br><br><a href="/jpanel/import/'+import_id+'/log" class="btn btn-default"><i class="fa fa-search"></i> View Log</a>');

                        $e.find('h1').html('Finished!');
                    }

                } else if (json.stage == 'analyzing' && json.total_records > 0) {

                    if ($e.find('h1').text() !== 'Analyzing') {
                        $e.find('h1').html('Analyzing');
                        $e.find('.progress-bar').velocity('stop').css({ width: json.progress+'%' });
                        $e.find('.info').html('Analyzed <span class="odometer added"></span> of <span class="total"></span> rows.');
                        new Odometer({
                            el: $e.find('.info .added').get(0),
                            value: json.added_records.formatMoney(0),
                            duration: 3500,
                            animation: 'count'
                        });
                    }
                    $e.find('.info .added').html(json.current_record.formatMoney(0));
                    $e.find('.info .total').html(json.total_records.formatMoney(0));
                    if (estimated_minutes_remaining) {
                        if (!$e.find('.info .remaining').length) {
                            $e.find('.info').append('<br><small class="text-muted">Approx <span class="remaining"></span> mins remaining.</small>');
                        }
                        $e.find('.info .remaining').html(estimated_minutes_remaining.toFixed(1));
                    }
                    $e.find('.progress-bar').velocity('stop').velocity({ width: json.progress+'%' }, { duration: 3500 });

                } else if (json.stage === 'importing') {

                    if ($e.find('h1').text() !== 'Importing') {
                        $e.find('h1').html('Importing');
                        $e.find('.progress-bar').velocity('stop').css({ width: json.progress+'%' });
                        $e.find('.info').html('Imported <span class="odometer added"></span> of <span class="total"></span> rows.');
                        new Odometer({
                            el: $e.find('.info .added').get(0),
                            value: json.added_records.formatMoney(0),
                            duration: 3500,
                            animation: 'count'
                        });
                    }
                    $e.find('.info .added').html(json.added_records.formatMoney(0));
                    $e.find('.info .total').html(json.total_records.formatMoney(0));
                    if (estimated_minutes_remaining) {
                        if (!$e.find('.info .remaining').length) {
                            $e.find('.info').append('<br><small class="text-muted">Approx <span class="remaining"></span> mins remaining.</small>');
                        }
                        $e.find('.info .remaining').html(estimated_minutes_remaining.toFixed(1));
                    }
                    $e.find('.progress-bar').velocity('stop').velocity({ width: json.progress+'%' }, { duration: 3500 });

                } else if (json.stage === 'done') {

                    if ($e.find('h1').text() !== 'Finished!') {

                        $e.find('h1').html('Finished!');

                        $e.find('.progress-bar').velocity('stop').css({ width: '100%' });

                        $e.find('.progress-bar').addClass('progress-bar-success')
                            .removeClass('progress-bar-striped active');

                        $e.find('.fa').addClass('text-success fa-check')
                            .removeClass('fa-circle-o-notch fa-spin');

                        $e.find('.info').html('Imported '+json.added_records.formatMoney(0)+' record(s). Updated '+json.updated_records.formatMoney(0)+' record(s). '+json.error_records.formatMoney(0)+' error(s).<br><br><a href="/jpanel/import/'+import_id+'/import-messages" class="btn btn-default"><i class="fa fa-search"></i> View Log</a>');
                    }

                } else {

                    if ($e.find('h1').text() !== 'Reading File') {
                        $e.find('h1').html('Reading File');
                        $e.find('.progress-bar').velocity({ width: '80%' }, {
                            duration: 30000,
                            complete: function(){
                                $e.find('.progress-bar').velocity({ width: '100%' }, { duration: 100000 });
                            }
                        });
                    }
                }

                // if its not finished, keep going
                if (!json.is_complete)
                    setTimeout(check, 3500);
            };

            var error = function (response) {};

            $.ajax({
                type     : 'get',
                url      : '/jpanel/import/'+import_id,
                success  : success,
                error    : error,
                dataType : 'json'
            });
        }

        check();
    });

});
</script>
@endsection
