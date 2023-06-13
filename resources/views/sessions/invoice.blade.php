
@extends('layouts.guest')

@section('title', "Invoice {$invoice->number}")
@section('body_classes', 'backend-invoice')

@section('content')
    <div class="w-full bg-white rounded-lg px-8 py-16 sm:px-16 mx-6 md:mx-1/6 my-6">

        <div class="w-full flex flex-row justify-between items-start">
            <div>
                <img class="h-8 mb-4 w-auto" src="https://cdn.givecloud.co/static/etc/givecloud-logo-full-color-rgb.svg" alt="Givecloud" />
                <p>
                    <strong>Givecloud Inc.</strong><br>
                    383 Livery St<br>
                    Ottawa, ON K2V 0B5<br>
                    CANADA<br>
                    <i class="fa fa-envelope-o"></i> {{ config('mail.support.address') }}
                    <br><br>
                </p>
            </div>

            <div class="text-right">
                <h1 class="m-0">{{ $invoice->number }}</h1>
                <div>{{ toLocalFormat($invoice->invoiced_at, 'M j, Y') }}</div>
            </div>

        </div>



        <div>
            <strong>Billed To:</strong><br>
            {{ $invoice->client->name }}<br>
            {{ $invoice->client->full_address }}
            @if ($invoice->client->email)<br><i class="fa fa-envelope-o"></i> {{ $invoice->client->email }}@endif
            @if ($invoice->client->phone1)<br><i class="fa fa-phone"></i> {{ $invoice->client->phone1 }}@endif
            @if ($invoice->client->phone2)<br><i class="fa fa-phone"></i> {{ $invoice->client->phone2 }}@endif
            <br><br>
        </div>

        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center" style="width:60px;">Qty</th>
                    <th class="text-right" style="width:110px;">Price</th>
                    <th class="text-right" style="width:110px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
                @if ($invoice->discount)
                    <tr class="text-danger">
                        <td></td>
                        <td colspan="2" class="text-left">Discount <small>{{ $invoice->discount_desc }}</small></td>
                        <td class="text-right">{{ number_format($invoice->discount, 2) }}</td>
                    </tr>
                @endif
                <tr class="">
                    <td></td>
                    <td colspan="2" class="text-left">Subtotal</td>
                    <td class="text-right"><strong>{{ number_format($invoice->subtotal, 2) }}</strong></td>
                </tr>
                @if ($invoice->tax1_amt)
                    <tr class="">
                        <td></td>
                        <td colspan="2" class="text-left">Tax <small>{{ $invoice->tax1_name }}</small></td>
                        <td class="text-right">{{ number_format($invoice->tax1_amt, 2) }}</td>
                    </tr>
                @endif
                @if ($invoice->tax2_amt)
                    <tr class="">
                        <td></td>
                        <td colspan="2" class="text-left">Tax <small>{{ $invoice->tax2_name }}</small></td>
                        <td class="text-right">{{ number_format($invoice->tax2_amt, 2) }}</td>
                    </tr>
                @endif
                <tr class="">
                    <th></th>
                    <th colspan="2" class="text-left">Total Due ({{ $invoice->currency }})</th>
                    <th class="text-right">{{ number_format($invoice->total, 2) }}</th>
                </tr>
            </tbody>

        </table>

        <br>

        <div class="alert alert-default text-center">
            <i class="fa fa-exclamation-circle"></i> Make all checks payable to<br><strong style="font-size:18px; line-height:24px;"><u>Givecloud Inc.</u></strong>
        </div>

        @if ($invoice->status == 'paid')
            <div class="col-sm-8 col-sm-offset-2">
                <div class="alert alert-info text-center">
                    Payment of ${{ number_format($invoice->payment_amt, 2) }} received
                    @if ($invoice->payment_at)on {{ toLocalFormat($invoice->payment_at, 'M j, Y') }}@endif
                    @if ($invoice->payment_option)via {{ $invoice->payment_option }}@endif
                    @if ($invoice->payment_reference)(Ref: {{ $invoice->payment_reference }})@endif
                </div>
            </div>
        @endif

    </div>
@endsection
