
@extends('layouts.app')
@section('title', 'Tributes')

@section('content')
<script>
    exportRecords = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/tributes.csv?' + $.param(d);
    }

    exportLabels = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/tributes_labels.pdf?' + $.param(d);
    }

    _printUnsentLetters = function () {
        var ids = j.ui.datatable.values('#tributesDataTable');
        if (ids.length == 0) {
            return $.alert('<strong>You have no items selected.</strong><br><br>Use the checkboxes on the left side of the table to select items to batch process.', 'danger', 'fa-check-square-o');
        }
        window.open('/jpanel/tributes/printUnsentLetters?ids='+j.ui.datatable.values('#tributesDataTable'), '_blank');
    }

    _printUnsentLabels = function () {
        var ids = j.ui.datatable.values('#tributesDataTable');
        if (ids.length == 0) {
            return $.alert('<strong>You have no items selected.</strong><br><br>Use the checkboxes on the left side of the table to select items to batch process.', 'danger', 'fa-check-square-o');
        }
        window.open('/jpanel/tributes_labels.pdf?ids='+j.ui.datatable.values('#tributesDataTable'), '_blank');
    }

    _markAsSent = function () {
        var ids = j.ui.datatable.values('#tributesDataTable');
        if (ids.length == 0) {
            return $.alert('<strong>You have no items selected.</strong><br><br>Use the checkboxes on the left side of the table to select items to batch process.', 'danger', 'fa-check-square-o');
        }

        $.confirm('Are you sure you want to mark <span class="badge">'+ids.length+'</span> tributes as sent?', function(){
            window.location = '/jpanel/tributes/sendUnsentLetters?ids='+ids;
        }, 'warning', 'fa-exclamation-triangle');
    }
</script>

<div class="row clearfix">
    <div class="col-lg-12">
        <h1 class="page-header">
            Tributes

            <div class="pull-right">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-outline dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-list-ul"></i> Bulk... <span class="badge checkbox-counter"></span> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right">
                        <li class="dropdown-header"><span class="text-info"><i class="fa fa-check-square-o"></i> Use the checkboxes to the left of each<br>item to batch process multiple items.</span></li>

                        <li class="divider"></li>
                        <li><a onclick="_printUnsentLetters();"><i class="fa fa-fw fa-print"></i> Print Letters</a></li>
                        @if (sys_get('ml_enabled'))<li><a onclick="_printUnsentLabels();"><i class="fa fa-fw fa-print"></i> Print Labels</a></li>@endif
                        <li><a onclick="_markAsSent();"><i class="fa fa-fw fa-envelope-o"></i> Mark as Sent</a></li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-fw fa-download"></i> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right">
                        <li><a onclick="exportRecords(); return false;"><i class="fa fa-fw fa-download"></i> Export Tributes</a></li>
                        @if (sys_get('ml_enabled'))<li><a onclick="exportLabels(); return false;"><i class="fa fa-fw fa-envelope"></i> Mailing Labels PDF</a></li>@endif
                    </ul>
                </div>

            </div>
        </h1>
    </div>
</div>

@inject('flash', 'flash')

{{ $flash->output() }}

@if ($unsentCount > 0 and !($input->notify == 'letter' and $input->is_sent == 0))
    <div class="alert alert-warning text-center">
        You have <span class="badge">{{ $unsentCount }}</span> unsent letters. <a href="?notify=letter&is_sent=0" class="btn btn-warning btn-xs">View</a>
    </div>
@endif

<div class="row">
    <form class="datatable-filters">
        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" name="search" id="filterSearch" value="{{ $input->search }}" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter Tributes by:<br><i class='fa fa-check'></i> Contribution Number<br><i class='fa fa-check'></i> Tribute Name<br><i class='fa fa-check'></i> Tribute Recipeint" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Notifications</label>
                <select class="form-control" name="notify">
                    <option value="">All Notifications</option>
                    <option value="letter" @selected($input->notify == 'letter')>Letters Only</option>
                    <option value="email" @selected($input->notify == 'email')>Emails Only</option>
                    <option value="none" @selected($input->notify == 'none')>No Notification</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Status</label>
                <select class="form-control" name="is_sent">
                    <option value="">Any Status</option>
                    <option value="1" @selected($input->is_sent == '1')>Sent</option>
                    <option value="0" @selected($input->is_sent == '0')>Unsent</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Tribute Types</label>
                <select class="form-control" name="type">
                    <option value="">All Tribute Types</option>
                    @foreach ($tributeTypes as $type)
                        <option value="{{ $type->id }}" @selected($input->type == $type->id)>{{ $type->label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Created</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                    <input type="text" class="form-control" name="created_at_str" value="{{ $input->created_at_str }}" placeholder="Created on..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="created_at_end" value="{{ $input->created_at_end }}" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Notified</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                    <input type="text" class="form-control" name="notify_at_str" value="{{ $input->notify_at_str }}" placeholder="Notify on..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="notify_at_end" value="{{ $input->notify_at_end }}" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Sent</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                    <input type="text" class="form-control" name="notified_at_str" value="{{ $input->notified_at_str }}" placeholder="Sent on..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="notified_at_end" value="{{ $input->notified_at_end }}" />
                </div>
            </div>

            <div class="form-group pt-1 px-2">
                <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
            </div>

        </div>
    </form>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table id="tributesDataTable" class="table table-striped table-bordered table-hover responsive">
                <thead>
                    <tr>
                        <th width="16"><input type="checkbox" class="master" name="selectedids_master" value="1" /></th>
                        <th width="16"></th>
                        <th>Contribution</th>
                        <th>Type</th>
                        <th>Tribute Name</th>
                        <th>Notify</th>
                        <th>Notify Name</th>
                        <th>Amount</th>
                        <th>Created on</th>
                        <th>Notify on</th>
                        <th>Sent on</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection
