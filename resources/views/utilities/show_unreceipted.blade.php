
@extends('layouts.app')
@section('title', 'Unreceipted Purchases')

@section('content')
<h1 class="page-header">Unreceipted Purchases {{ $year }}</h1>

<div class="alert alert-info">No notifications will be sent when using this function.</div>

@inject('flash', 'flash')

{{ $flash->output() }}

<table class="datatable table table-striped table-bordered">
    <thead>
        <tr>
            <th width="70"></th>
            <th>Product</th>
            <th>Code</th>
            <th>Contributions</th>
            <th>Txns</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($unreceipted as $record)
            <tr>
                <td><a href="/jpanel/utilities/process_receipts?year={{ $year }}&product={{ $record->product_id }}" class="btn btn-info btn-xs">Issue ({{ $record->affected_total }}) Receipts</a></td>
                <td>{{ $record->product_name }}</td>
                <td>{{ $record->product_code }}</td>
                <td>{{ $record->affected_orders }}</td>
                <td>{{ $record->affected_txns }}</td>
                <td>{{ $record->affected_orders + $record->affected_txns }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
