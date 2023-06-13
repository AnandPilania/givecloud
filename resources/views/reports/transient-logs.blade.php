
@extends('layouts.app')
@section('title', 'Transient Logs')

@section('content')
<div class="row clearfix">
    <div class="col-lg-12">
        <h1 class="page-header">Transient Logs</h1>
    </div>
</div>

<div class="row">
    <form class="datatable-filters">
        <div class="datatable-filters-label">
            <div class="form-control-static no-wrap"><strong><i class="fa fa-filter"></i> Filters</strong></div>
        </div>

        <div class="datatable-filters-fields">
            <div class="row">
                <div class="form-group col-lg-4 col-md-3 col-sm-6 col-xs-6">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-search"></i></div>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search">
                    </div>
                </div>
                <div class="form-group col-lg-5 col-md-6 col-sm-6 col-xs-6">
                    <div class="input-group input-daterange">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                        <input type="text" class="form-control" name="gte_created" value="{{ request('lte_created') }}" placeholder="Created on...">
                        <span class="input-group-addon">to</span>
                        <input type="text" class="form-control" name="lte_created" value="{{ request('gte_created') }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table id="transientLogsTable" class="table table-striped table-bordered table-hover responsive">
        <thead>
            <tr>
                <th width="16"></th>
                <th>Origin</th>
                <th>Level</th>
                <th>Request ID</th>
                <th>Source</th>
                <th>Message</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
spaContentReady(function() {

    var transientLogsTable = $('#transientLogsTable').DataTable({
        dom: 'rtpi',
        iDisplayLength : 50,
        autoWidth: false,
        processing: true,
        serverSide: true,
        sDefaultContent: '',
        order: false,
        columnDefs: [
            {orderable: false, targets: 0},
            {orderable: false, targets: 1},
            {orderable: false, targets: 2},
            {orderable: false, targets: 3},
            {orderable: false, targets: 4},
            {orderable: false, targets: 5},
            {orderable: false, targets: 6},
        ],
        stateSave: false,
        ajax: {
            url: @json(route('backend.reports.transient_logs.get')),
            type: 'POST',
            data: function (d) {
                var fields = $('.datatable-filters').serializeArray();
                $.each(fields, function(i, field) {
                    d[field.name] = field.value;
                });
            }
        },
        drawCallback: function() {
            j.ui.table.init();
            return true;
        }
    });

    function drawTable() {
        transientLogsTable.draw();
    }

    $('.datatable-filters input, .datatable-filters select').not(':hidden').each(function(i, input) {
        if ($(input).data('datepicker')) {
            $(input).on('changeDate', drawTable);
        } else {
            $(input).change(drawTable);
        }
    });

    $('form.datatable-filters').on('submit', function(e) {
        e.preventDefault();
    });

    $('#transientLogsTable').on('click', 'a[data-log-id]', function() {
        var id = $(this).data('log-id');

        $.modal({
            class: 'modal-info',
            title: '<i class="fa fa-question-circle"></i> Transient Log',
            onOpen: function($modal) {
                $.getJSON(@json(route('backend.reports.transient_logs.show', ':log')).replace(':log', id), function(data) {
                    var colours = {
                        alert: 'red',
                        critical: 'red',
                        debug: 'gray',
                        emergency: 'red',
                        error: 'red',
                        info: 'blue',
                        notice: 'gray',
                        warning: 'yellow',
                    };

                    try {
                        data.context = JSON.parse(data.context);
                    } catch (e) {
                        // do nothing
                    }

                    var body = j.templates.render('transientLogTmpl', {
                        log: data,
                        colour: colours[data.level] || 'gray',
                    });

                    $modal.find('.modal-body').html(body);
                });
            }
        });
    });

});
</script>
@endsection
