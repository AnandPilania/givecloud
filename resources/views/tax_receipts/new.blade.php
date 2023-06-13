@extends('layouts.app')
@section('title', 'Create Tax Receipt')

@section('content')
<form action="/jpanel/tax_receipts/new" method="post">
    @csrf
    <input type="hidden" name="account_id" value="{{ $receipt->account_id }}">
    <input type="hidden" name="receipt_type" value="{{ $receipt->receipt_type }}">

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Create @if ($receipt->receipt_type === 'consolidated') Consolidated @endif Tax Receipt
            </h1>
        </div>
    </div>

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="pull-left">
                <h4>
                    <strong>Account:</strong>
                    <a target="_blank" href="{{ route('backend.member.edit', $account->id) }}">{{ $account->display_name }}</a>
                </h4>
            </div>
            <div class="form-inline text-right">
                <div class="form-group" style="margin-right:20px">
                    <label for="rp" style="margin-right:5px;">Receipting Period</label>
                    <select class="form-control" name="rp" id="rp" style="width:200px">
                        <option value="this_year">This Year</option>
                        <option value="last_year" selected>Last Year</option>
                        <option value="all">All</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ma" style="margin-right:5px;">Min Receiptable Amount</label>
                    <input type="text" class="form-control" name="ma" id="ma" value="0" style="width:100px">
                </div>
            </div>
            <br>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table id="receiptable-list" class="table table-v2 table-striped table-hover responsive">
                    <thead>
                        <tr>
                            <th width="20"><input type="checkbox" class="master" name="selectedids_master" value="1" /></th>
                            <th style="min-width:200px;">Contribution/Transaction</th>
                            <th width="140">Date</th>
                            <th width="180">Receiptable Amount</th>
                            <th width="120">Receipt Type</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row form-horizontal" style="margin-top:20px">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="inputReceiptDate" class="col-sm-2 control-label">Receipt Date</label>
                <div class="input-group" style="width:220px">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control input-date" id="inputReceiptDate" name="receipt_date" value="{{ fromUtc('last year')->endOfYear()->format('M j, Y') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputDraft" class="col-sm-2 control-label">Receipt Status</label>
                <select class="form-control" name="status" id="inputStatus" style="width:200px">
                    <option value="issued">Issued</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            <div class="form-group">
                <label for="inputTaxReceiptTemplateId" class="col-sm-2 control-label">Receipt Template</label>
                <select class="form-control" name="tax_receipt_template_id" id="inputTaxReceiptTemplateId" style="width:300px">
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" @selected($template->is_default)>{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="inputAutoNotify" class="col-sm-2 control-label">Auto Email Receipts</label>
                <input id="inputAutoNotify" type="checkbox" class="switch" value="1" name="auto_notify" checked>
            </div>

            <div class="form-group" style="margin-top:40px">
                <button id="generateReceiptBtn" type="submit" class="btn btn-success" disabled>Generate Receipt</button>
            </div>
        </div>
    </div>
</form>

<script>

spaContentReady(function() {
    $.fn.dataTable.ext.classes.sProcessing = 'dataTables_processing';

    var receiptable_table = $('#receiptable-list').DataTable({
        dom: 'rt',
        sErrMode: 'throw',
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: false, targets: 1 },
            { orderable: false, targets: 2 },
            { orderable: false, targets: 3, class: 'text-right' },
            { orderable: false, targets: 4 },
        ],
        language: {
            emptyTable: 'No receiptable payments found.'
        },
        stateSave: false,
        ajax: {
            url: '/jpanel/tax_receipts/receiptable.json?account={{ $receipt->account_id }}',
            type: 'POST',
            data: function(d) {
                d.receipting_period = $('select[name=rp]').val();
                d.min_receiptable = $('input[name=ma]').val();
            }
        },
        drawCallback: function() {
            j.ui.datatable.formatRows($('#receiptable-list'));
            return true;
        },
        initComplete: function() {
            j.ui.datatable.formatTable($('#receiptable-list'));
        }
    });

    $('#rp,#ma').change(function() {
        receiptable_table.draw();
    });

    $('#receiptable-list').on('change', 'input.master,input.slave', function(e) {
        $('#generateReceiptBtn').prop('disabled', $('input.slave:checked').length === 0);
    });

    $('#inputStatus').on('change', function() {
        var $notify = $('#inputAutoNotify');
        if ($(this).val() === 'draft') {
            $notify.bootstrapSwitch('state', false);
            $notify.bootstrapSwitch('disabled', true);
        } else {
            $notify.bootstrapSwitch('disabled', false);
        }
    });
});
</script>

@endsection
