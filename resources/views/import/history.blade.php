
@extends('layouts.app')
@section('title', 'Import History')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Import History

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <a href="/jpanel/import" class="btn btn-success"><i class="fa fa-upload"></i> New Import</a>
            </div>
        </h1>
    </div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th width="16" rowspan="2"></th>
            <th rowspan="2">Name</th>
            <th rowspan="2">Status</th>
            <th rowspan="2">File</th>
            <th colspan="4" class="text-center">Records</th>
            <th rowspan="2">Date</th>
        </tr>
        <tr>
            <th class="text-right">Total</th>
            <th class="text-right">Added</th>
            <th class="text-right">Updated</th>
            <th class="text-right">Error</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($imports as $item)
            <tr class="{{ ($item->error_message) ? 'text-danger' : '' }}">
                <td><a href="/jpanel/import/{{ $item->id }}/import-messages"><i class="fa fa-search"></i></a></td>
                <td>{{ $item->name }}</td>
                <td width="80">{{ strtoupper($item->stage) }}</td>
                <td>
                    @if ($item->file)
                        <a href="/jpanel/import/{{ $item->id }}/file">{{ $item->file_name }}</a>
                    @else
                        {{ $item->file_name }}
                    @endif
                </td>
                <td class="text-right">{{ (int) $item->total_records }}</td>
                <td class="text-right">{{ (int) $item->added_records }}</td>
                <td class="text-right">{{ (int) $item->updated_records }}</td>
                <td class="text-right">{{ (int) $item->error_records }}</td>
                <td width="120">
                    {{ $item->created_at->format('M j') }}&nbsp;
                    <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
