@extends('layouts.app')
@section('title', 'Tax Receipt Template')

@section('content')
<form action="/jpanel/settings/tax_receipts/templates/{{ $template->id }}" method="post">
@csrf

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Tax Receipt Template

            <div class="pull-right">
                <a onclick="$.confirm('Are you sure you want to create a duplicate of this template?', function(){ location='/jpanel/settings/tax_receipts/templates/{{ $template->id }}/duplicate'; }, 'warning');" class="btn btn-info btn-outline" data-toggle="tooltip" data-placement="top" title="Duplicate This Template"><i class="fa fa-copy"></i></a>
                <a href="/jpanel/settings/tax_receipts/templates/{{ $template->id }}/preview" target="_blank" class="btn btn-info btn-outline">Preview</a>
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-sm hidden-xs"> Save</span></button>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<div class="row">
    <div class="col-lg-12">
        <div class="form-group">
            <input type="text" name="name" class="form-control input-lg" placeholder="Template name (e.x. 2018 Tax Receipts)" value="{{ $template->name }}" required>
        </div>

        @unless($template->is_default)
        <div class="form-group">
            <input id="inputIsDefault" type="checkbox" class="switch" value="1" name="is_default">
            &nbsp; <label for="inputIsDefault">Use as default</label>
        </div>
        <br>
        @endunless

        <div class="form-group">
            <textarea name="body" id="tax_receipt_template" style="height:450px;" class="form-control html-tax-receipt">{{ $template->body }}</textarea>
            <br />
            <div class="alert alert-info">
                Click to access the <a onclick="$('#merge-tag-cheatsheet').toggle(); return false;">merge tag cheat-sheet</a>.
                <div class="message_expand" style="display:none;" id="merge-tag-cheatsheet">
                    <h3>Tax Receipt</h3>
                    <table class="simple">
                        <tr>
                            <td>[[first_name]]</td>
                            <td>[[name]] <i style="color:#999;">(Organization name or Person's Name Depending on Donor Type)</i></td>
                            <td>[[full_address]] <i style="color:#999;">(Preformatted full address)</i></td>
                        </tr>
                        <tr>
                            <td>[[last_name]]</td>
                            <td>[[address_01]]</td>
                            <td>[[city]]</td>
                        </tr>
                        <tr>
                            <td>[[email]]</td>
                            <td>[[address_02]]</td>
                            <td>[[state]]</td>
                        </tr>
                        <tr>
                            <td>[[number]] <i style="color:#999;">(Receipt number)</i></td>
                            <td>[[amount]] <i style="color:#999;">(Receiptable amount)</i></td>
                            <td>[[zip]]</td>
                        </tr>
                        <tr>
                            <td>[[ordered_at]]</td>
                            <td>[[issued_at]]</td>
                            <td>[[summary_table]]</td>
                        </tr>
                    </table>
                    IMPORTANT: Shortcodes do not work in emails.
                </div>
            </div>
        </div>
    </div>
</div>

</form>
@endsection
