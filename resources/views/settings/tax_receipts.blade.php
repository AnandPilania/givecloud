@extends('layouts.app')
@section('title', 'Tax Receipts')

@section('content')
<form class="form-horizontal" action="/jpanel/settings/tax_receipts" method="post">
    @csrf

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Tax Receipts

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
            </div>
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-university"></i> Tax Receipts
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-university"></i> Tax Receipts</div>
                    <div class="panel-sub-desc">
                        Generate tax receipts for eligible payments.

                        <br /><br />
                        <span class="text-info"><i class="fa fa-exclamation-circle"></i> <strong>Note:</strong> Be sure to examine each of your products to be sure they are setup for tax receipting.</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Enable</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="tax_receipt_pdfs" <?= e((sys_get('tax_receipt_pdfs') == 1) ? 'checked' : '') ?> onchange="if ($(this).is(':checked')) $('.pdf-only').removeClass('hide'); else $('.pdf-only').addClass('hide');">
                        </div>
                    </div>

                    <div class="pdf-only <?= e((sys_get('tax_receipt_pdfs') == 0) ? 'hide' : '') ?>" style="margin-top:30px">
                        <div class="form-group">
                            <label for="inputTaxReceiptType" class="col-md-4 control-label">Receipt Type</label>
                            <div class="col-md-8">
                                <select id="inputTaxReceiptType" name="tax_receipt_type" class="form-control" style="max-width:200px">
                                    <option value="single" @selected(sys_get('tax_receipt_type') === 'single')>Single Receipt</option>
                                    <option value="none" @selected(sys_get('tax_receipt_type') === 'none')>No Receipt</option>
                                    <option value="consolidated" @selected(sys_get('tax_receipt_type') === 'consolidated')>Consolidated</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Issuing Country</label>
                            <div class="col-md-8">
                                <select name="tax_receipt_country" class="form-control">
                                    <option value="AU" @selected(sys_get('tax_receipt_country') === 'AU')>Australia</option>
                                    <option value="CA" @selected(sys_get('tax_receipt_country') === 'CA')>Canada</option>
                                    <option value="US" @selected(sys_get('tax_receipt_country') === 'US')>United States of America</option>
                                    <option value="ANY" @selected(sys_get('tax_receipt_country') === 'ANY')>Any Country</option>
                                </select>
                                <p class="help-block" style="margin-bottom:0">
                                    <span class="text-info"><i class="fa fa-exclamation-circle"></i> <strong>Note:</strong> If <strong>'Any Country'</strong> is selected, tax receipts will be issued to all donors regardless of their billing country.</span>
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Receipt # Format</label>
                            <div class="col-md-8">
                                <input class="form-control" type="text" name="tax_receipt_number_format" value="{{ sys_get('tax_receipt_number_format') }}">
                                <div class="help-block">
                                    <span class="text-info">
                                        <div data-toggle="collapse" data-target="#availableReceiptNumberFormattingOptions" style="cursor:pointer">
                                            <i class="fa fa-question-circle"></i> <strong>Hint:</strong> <span style="text-decoration:underline">Show available receipt number formatting options.</span>
                                        </div>
                                        <div id="availableReceiptNumberFormattingOptions" class="collapse">
                                            <dl class="dl-horizontal dl-sm">
                                                <dt>[YY]</dt>
                                                <dd>Two digit year (example: {{ date('y') }})</dd>
                                                <dt>[YYYY]</dt>
                                                <dd>Four digit year (example: {{ date('Y') }})</dd>
                                                <dt>[00000]</dt>
                                                <dd>The unique receipt sequence number. The number of zeros in this code determines the number of digits displayed in the code.</dd>
                                            </dl>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label" style="margin-top:-2px;padding-top:0;">Include Description in Donation(s) Summary</label>
                            <div class="col-md-8">
                                <input type="checkbox" class="switch" value="1" name="tax_receipt_summary_include_description" @checked(sys_get('tax_receipt_summary_include_description') == 1)>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label" style="margin-top:-2px;padding-top:0;">Include GL Account in Donation(s) Summary</label>
                            <div class="col-md-8">
                                <input type="checkbox" class="switch" value="1" name="tax_receipt_summary_include_gl" @checked(sys_get('tax_receipt_summary_include_gl') == 1)>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default pdf-only <?= e((sys_get('tax_receipt_pdfs') == 0) ? 'hide' : '') ?>">
        <div class="panel-heading visible-xs">
            <i class="fa fa-envelope"></i> Receipt Templates
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-envelope"></i> Receipt Templates</div>
                    <div class="panel-sub-desc">
                        Customize your tax receipt templates.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-6 col-md-offset-4">
                    <br>

                    @foreach ($templates as $template)
                        <div>
                            <a style="padding:1px;" class="btn btn-outline btn-xs" href="/jpanel/settings/tax_receipts/templates/{{ $template->id }}" data-toggle="tooltip" data-placement="top" title="Edit This Template"><i class="fa fa-pencil"></i></a>
                            <a style="padding:1px;" class="btn btn-outline btn-xs" onclick="$.confirm('Are you sure you want to create a duplicate of this template?', function(){ location='/jpanel/settings/tax_receipts/templates/{{ $template->id }}/duplicate'; }, 'warning');" data-toggle="tooltip" data-placement="top" title="Duplicate This Template"><i class="fa fa-files-o"></i></a>
                            <span style="display:inline-block;margin-left:6px;vertical-align:top;margin-top:2px;">
                                {{ $template->name }}

                                @if ($template->is_default)
                                    <span class="badge">default</span>
                                @endif
                            </span>
                        </div>
                    @endforeach

                    <br>
                </div>
                </div>

            </div>

        </div>
    </div>

    <div class="panel panel-default pdf-only <?= e((sys_get('tax_receipt_pdfs') == 0) ? 'hide' : '') ?>">
        <div class="panel-heading visible-xs">
            <i class="fa fa-envelope"></i> Email Notification
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-envelope"></i> Email Notification</div>
                    <div class="panel-sub-desc">
                        Customize the notification email that is used when sending tax receipts via email.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-6 col-md-offset-4">
                    <br>
                    <a class="btn btn-info btn-sm" target="_blank" href="/jpanel/emails/edit?i=<?= e(\Ds\Models\Email::where('type', 'customer_tax_receipt')->first()->id) ?>"><i class="fa fa-pencil"></i> Edit Email</a>
                </div>
                </div>

            </div>
        </div>
    </div>

</div></div>

</form>
@endsection
