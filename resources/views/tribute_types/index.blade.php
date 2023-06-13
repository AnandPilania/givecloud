
@extends('layouts.app')
@section('title', 'Tribute Types')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Tribute Types

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <a href="/jpanel/tribute_types/add" class="btn btn-success"><i class="fa fa-plus"></i><span class="hidden-xs hidden-sm"> Add</span></a>
            </div>
        </h1>
    </div>
</div>

@inject('flash', 'flash')

{{ $flash->output() }}

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th width="65" class="text-center">#</th>
                        <th>Label</th>
                        <th width="140">First Tribute</th>
                        <th width="140">Last Tribute</th>
                        <th width="140" class="text-center">Count</th>
                        <th width="140" class="text-right">Avg Amount</th>
                        <th width="140" class="text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($tributeTypes) > 0)
                        @foreach ($tributeTypes as $type)
                            <tr @if(!$type->is_enabled)class="text-muted"@endif>
                                <td width="16"><a href="/jpanel/tribute_types/{{ $type->id }}/edit"><i class="fa fa-search"></i></a></td>
                                <td class="text-center">{{ $type->sequence }}</td>
                                <td>{{ $type->label }} @if(!$type->is_enabled)<span class="label label-default">OFFLINE</span>@endif</td>
                                <td>{{ toLocalFormat($type->first_tribute_at) }}</td>
                                <td>{{ toLocalFormat($type->last_tribute_at) }}</td>
                                <td class="text-center">{{ $type->tribute_count }}</td>
                                <td class="text-right">{{ numeral($type->avg_amount) }}</td>
                                <td class="text-right">{{ numeral($type->total_amount) }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
