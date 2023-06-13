@extends('layouts.app')
@section('title', 'Create Tax Receipt')

@section('content')
<form class="form-horizontal" action="/jpanel/tax_receipts/consolidated-receipting" method="post">
    @csrf

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Consolidated Receipting
            </h1>
        </div>
    </div>

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <div class="row" style="margin-top:20px">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="rp" class="col-sm-2 control-label">Receipting Period</label>
                <div class="input-group input-daterange-pretty" style="width:320px">
                    <div class="input-group-addon" style="width:38px;border-width:1px"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="receipting_period_from" id="rp" value="{{ fromUtc('last year')->startOfYear()->format('M j, Y') }}">
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="receipting_period_to" value="{{ fromUtc('last year')->endOfYear()->format('M j, Y') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="ma" class="col-sm-2 control-label">Min Receiptable Amount</label>
                <input type="text" class="form-control" name="min_receiptable" id="ma" value="0" style="width:200px">
            </div>

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

            <div class="alert alert-warning" style="max-width:600px;margin:30px 0">
                <h4><i class="fa fa-exclamation-triangle"></i> REVIEW CAREFULLY</h4>
                Once started the generation of receipts can't be cancelled. Therefore it's super important to review the above settings closely prior to proceeding.
            </div>

            <button type="submit" class="btn btn-success">Generate Receipts</button>
        </div>
    </div>
</form>

<script>
spaContentReady(function($) {
    $('#inputStatus').on('change', function() {
        var $notify = $('#inputAutoNotify');
        if ($(this).val() === 'draft') {
            $notify.bootstrapSwitch('state', false);
            $notify.bootstrapSwitch('disabled', true);
        } else {
            $notify.bootstrapSwitch('disabled', false);
            $notify.bootstrapSwitch('state', true);
        }
    });
});
</script>
@endsection
