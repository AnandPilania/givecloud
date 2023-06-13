
@extends('layouts.app')
@section('title', 'Tax Receipts')

@section('content')
<script>
    exportRecords = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/tax_receipts.csv?' + $.param(d);
    }

    _batchSelected = function (action) {
        var selection = j.taxReceipt.selection();
        if (selection.ids && selection.ids.length === 0) {
            return $.alert('<strong>You have no tax receipt(s) selected.</strong><br><br>Use the checkboxes on the left side of the table to select tax receipts to batch process.', 'danger', 'fa-exclamation-triangle');
        }

        $.confirm('Are you sure you want to '+action.replace(/_/g,' ')+' these tax receipt(s)?', function () {
            if (action === 'print') {
                var $form = $('<form method="post" action="/jpanel/tax_receipts/bulk?action=print" target="_blank" />').appendTo('body');
                $form.append('@csrf');
                if (selection.ids) {
                    _.each(selection.ids, function(id) {
                        $('<input type="hidden" name="ids[]"/>').val(id).appendTo($form);
                    });
                } else {
                    _.each(selection, function(value, key) {
                        $('<input type="hidden"/>').prop('name', key).val(value).appendTo($form);
                    });
                }
                $form.submit();
                $form.remove();
            } else {
                $(j.taxReceipt.dataTable.settings()[0].aanFeatures.r).show();
                axios.post('/jpanel/tax_receipts/bulk?action='+action, selection)
                    .then(function(res) {
                        if (action === 'notify') {
                            $(j.taxReceipt.dataTable.settings()[0].aanFeatures.r).hide();
                            toastr.success('Receipts have been re-notified successfully.');
                        } else {
                            j.taxReceipt.dataTable.draw();
                        }
                    });
            }
        }, 'warning', 'fa-question-circle');
    }
</script>

<div class="row clearfix">
    <div class="col-lg-12">
        <h1 class="page-header">
            Tax Receipts

            <div class="pull-right">
                @if (user()->can('taxreceipt.edit'))
                    <a href="/jpanel/tax_receipts/consolidated-receipting" class="btn btn-default">Consolidated Receipting</a>
                @endif

                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-list-ul fa-fw"></i> Bulk... <span class="badge checkbox-counter"></span> <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li class="dropdown-header"><span class="text-info"><i class="fa fa-check-square-o"></i> Use the checkboxes to the left of each<br>item to batch process multiple items.</span></li>
                        <li class="divider"></li>
                        <li>
                            <a onclick="_batchSelected('issue');">
                                <i class="fa fa-fw fa-check"></i> Issue<br>
                                <ul class="help-block text-muted">
                                    <li><strong>Draft receipts will be issued</strong></li>
                                    <li>Issued receipts will be ignored</li>
                                </ul>
                            </a>
                        </li>
                        <li>
                            <a onclick="_batchSelected('issue_and_notify');">
                                <i class="fa fa-fw fa-check"></i> Issue &amp; Notify
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a onclick="_batchSelected('notify');">
                                <i class="fa fa-fw fa-check"></i> Re-Notify
                                <ul class="help-block text-muted">
                                    <li><strong>Issued receipts will be re-notified</strong></li>
                                </ul>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a onclick="_batchSelected('void');">
                                <i class="fa fa-fw fa-times"></i> Void/Delete<br>
                                <ul class="help-block text-muted">
                                    <li><strong>Issued receipts will be voided</strong></li>
                                    <li>Draft receipts will be deleted</li>
                                </ul>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li style="margin-bottom: 3px;"><a onclick="_batchSelected('print');"><i class="fa fa-fw fa-print"></i> Print</span></a></li>
                    </ul>
                </div>

                <a class="btn btn-default" onclick="exportRecords(); return false;"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span></a>
            </div>
        </h1>
    </div>
</div>

@inject('flash', 'flash')

{{ $flash->output() }}

<div class="row">
    <form class="datatable-filters">
        <div class="datatable-filters-label">
            <div class="form-control-static no-wrap"><strong><i class="fa fa-filter"></i> Filters</strong></div>
        </div>

        <div class="datatable-filters-fields">
            <div class="row">

            <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-6">
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" name="search" id="filterSearch" value="{{ $filters->search }}" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter Tax Receipts by:<br><i class='fa fa-check'></i> Name &amp; Address<br><i class='fa fa-check'></i> Email &amp; Phone<br><i class='fa fa-check'></i> Receipt Number" />
                </div>
            </div>

            <div class="form-group col-lg-2 col-md-3 col-sm-6 col-xs-6">
                <select class="form-control selectize" name="filter_by">
                    <option value="">Filter by...</option>
                    <option value="no_name">No name</option>
                    <option value="no_email">No email</option>
                    <option value="incomplete_address">Incomplete address</option>
                </select>
            </div>

            <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-6">
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                    <input type="text" class="form-control" name="issued_at_str" value="{{ $filters->issued_at_str }}" placeholder="Issued on..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="issued_at_end" value="{{ $filters->issued_at_end }}" />
                </div>
            </div>

            <div class="form-group col-lg-2 col-md-3 col-sm-6 col-xs-6">
                <select class="form-control selectize" name="status">
                    <option value="">Status...</option>
                    <option value="draft">Draft</option>
                    <option value="issued">Issued</option>
                    <option value="void">Void</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div id="taxReceiptsDataTableSearchSelection" class="well text-center text-muted select-search" style="display:none">
            <div class="notselected">
                All <strong>50</strong> tax receipts on this page are selected.&nbsp;&nbsp;
                <button class="btn btn-link" onclick="j.taxReceipt.selectSearch();">Select all tax receipts that match these filters</button>
            </div>
            <div class="selected" style="display:none">
                All tax receipts matching these filters are selected.&nbsp;&nbsp;
                <button class="btn btn-link" onclick="j.taxReceipt.clearSelection();">Clear selection</button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="taxReceiptsDataTable" class="table table-v2 table-striped table-hover responsive">
                <thead>
                    <tr>
                        <th width="20"></th>
                        <th>Number</th>
                        <th>Issued To</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Issued At</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection
