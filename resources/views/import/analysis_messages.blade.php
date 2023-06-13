
@extends('layouts.app')
@section('title', 'Analysis Messages')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Analysis Messages

            <div class="pull-right">
                @if ($import->stage == 'import_ready')
                    <a href="/jpanel/import/{{ $import->id }}/start-import" class="btn btn-success">Start Import <i class="fa fa-arrow-right"></i></a>
                    <a href="/jpanel/import/{{ $import->id }}/abort" class="btn btn-danger btn-outline"><i class="fa fa-ban"></i> Cancel</i></a>
                @endif
            </div>
        </h1>
    </div>
</div>

<h2>{{ $import->name }} ({{ $import->file_name }})</h2>

<pre>{{ $import->analysis_messages }}</pre>
@endsection
