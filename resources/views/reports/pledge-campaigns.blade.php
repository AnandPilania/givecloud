
@extends('layouts.app')
@section('title', 'Pledge Campaigns')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Pledge Campaigns

            @if (count($campaigns) > 1)
                <div class="inline-block w-64 mt-1.5 align-top">
                    <select class="form-control" name="campaign" id="campaign" size="1" placeholder="Select a campaign">
                        @foreach ($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" <?= e(volt_selected($campaign->id, request('campaigns'))); ?>>{{ $campaign->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" class="form-control" name="campaign" id="campaign" value="{{ $campaigns[0]->id ?? '' }}">
            @endif

            <div class="pull-right">
                <button class="btn btn-default datatable-export"><i class="fa fa-download fa-fw"></i> Export</button>
            </div>

        </h1>
    </div>
</div>

@inject('flash', 'flash')

<div class="toastify hide">
    {{ $flash->output() }}
</div>

<div class="row">
    <form class="datatable-filters">

        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control delay-filter" name="search" value="" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter fundraising pages by:<br><i class='fa fa-check'></i> Name, Description, Url<br><i class='fa fa-check'></i> Author Name, Team Member Names" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Commitment Date</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="commitment_date_a" value="" placeholder="Date..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="commitment_date_b" value="" />
                </div>
            </div>

        </div>
    </form>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table id="pledges-list" class="table table-v2 table-striped table-hover responsive">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th width="90">Type</th>
                        <th width="200">Supporter</th>
                        <th width="300">Comments</th>
                        <th width="160">Amount</th>
                        <th width="120">Date</th>
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
    var pledges_list = $('#pledges-list').DataTable({
        "dom": 'rtpi',
        "sErrMode":'throw',
        "iDisplayLength" : 50,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "order": [[ 5, 'desc' ]],
        "columnDefs": [
            { "orderable": false, "targets": 0, "class" : "text-center" },
            { "orderable": false, "targets": 1, "class" : "text-left" },
            { "orderable": true, "targets": 2, "class" : "text-left" },
            { "orderable": true, "targets": 3, "class" : "text-left" },
            { "orderable": true, "targets": 4, "class" : "text-right" },
            { "orderable": true, "targets": 5, "class" : "text-left" },
        ],
        "ajax": {
            "url": "/jpanel/reports/pledge-campaigns.json",
            "type": "POST",
            "data": function (d) {
                d.campaign = $('.form-control[name=campaign]').val();
                d.search = $('input[name=search]').val();
                d.commitment_date_a = $('input[name=commitment_date_a]').val();
                d.commitment_date_b = $('input[name=commitment_date_b]').val();
            }
        },
        "stateSave": false,

        "drawCallback" : function(){
            j.ui.datatable.formatRows($('#pledges-list'));
            $.ajaxModals();
            return true;
        },

        "initComplete" : function(){
            j.ui.datatable.formatTable($('#pledges-list'));
        }
    });

    j.ui.datatable.enableFilters(pledges_list);

    $('#campaign').on('change', function(e) {
        pledges_list.draw();
    });

    $('.datatable-export').on('click', function(ev){
        ev.preventDefault();

        var data = j.ui.datatable.filterValues('#pledges-list');
        window.location = '/jpanel/reports/pledge-campaigns.csv?'+$.param(data);
    });
});
</script>
@endsection
