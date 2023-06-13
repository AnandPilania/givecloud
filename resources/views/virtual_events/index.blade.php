
@extends('layouts.app')
@section('title', 'Virtual Events')

@section('content')

    @if ($event_count == 0)

        <div class="feature-highlight">
            <img class="feature-img" src="/jpanel/assets/images/icons/virtual-events.svg">
            <h2 class="feature-title">Create Virtual Events</h2>
            <p>This is where you'll manage your virtual events.</p>
            <div class="feature-actions">
                <a href="/jpanel/virtual-events/create" class="btn btn-lg btn-success btn-pill"><i class="fa fa-plus"></i> Add an Event</a>
            </div>
        </div>


    @else

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header clearfix">
                    {{ $pageTitle }}

                    <div class="visible-xs-block"></div>

                    <div class="pull-right">
                        <a href="/jpanel/virtual-events/create" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span></a>
                    </div>
                </h1>
            </div>
        </div>

        <div class="toastify hide">
            <?= dangerouslyUseHTML(app('flash')->output()) ?>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="virtual-events-list" class="table table-v2 table-striped table-hover responsive">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            spaContentReady(function() {

                if ($('#virtual-events-list').length > 0) {
                    var products_table = $('#virtual-events-list').DataTable({
                        "dom": 'rtpi',
                        "iDisplayLength" : 20,
                        "autoWidth": false,
                        "fixedHeader" : true,
                        "processing": true,
                        "serverSide": true,
                        "order": [[ 1, "desc" ]],
                        "columnDefs": [
                            {"class":"text-left", "targets":0},
                            {"class":"text-right", "targets":1}
                        ],
                        "stateSave": false,
                        "ajax": {
                            "url": "/jpanel/virtual-events.ajax",
                            "type": "POST",
                            "data": function (d) {
                                fields = $('.datatable-filters').serializeArray();
                                $.each(fields,function(i, field){
                                    d[field.name] = field.value;
                                })
                            }
                        }
                    });
                }
            });
        </script>

    @endif


@endsection
