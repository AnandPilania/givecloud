
@extends('layouts.app')
@section('title', 'Messenger')

@section('content')
<div id="messenger-app" v-cloak>
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Messenger Testing
            </h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <messenger-console site-name="{{ site()->name }}"></messenger-console>
        </div>
    </div>
</div>

<script>
spaContentReady(function($) {
    new Vue({
        el: '#messenger-app',
        delimiters: ['${', '}'],
    });
});
</script>
@endsection
