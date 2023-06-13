
@extends('layouts.app')
@section('title', 'Import Messages')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <span class="page-header-text">Import Messages</span>
        </h1>
    </div>
</div>

<h2>{{ $import->name }} ({{ $import->file_name }})</h2>

@if ($import->import_messages)
    <h3 class="mt-8 font-bold text-lg">Import Messages</h3>
    <pre>{{ $import->import_messages }}</pre>
@endif

@if ($import->analysis_messages)
    <h3 class="mt-8 font-bold text-lg">Analysis Messages</h3>
    <pre>{{ $import->analysis_messages }}</pre>
@endif

@endsection
