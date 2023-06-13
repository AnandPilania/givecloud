
@extends('layouts.app')
@section('title', 'Media Force Download')

@section('content')

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Media Force Download
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12">

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<form action="{{ route('backend.utilities.media_force_download.update') }}" method="post" class="row">
    @csrf

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6 col-lg-4">
                    <div class="panel-sub-desc mt-3">
                        <p>
                            Select the Media you want to update. Start by typing part of the filename/URL below and
                            then select the Media you want to update from the options provided.
                        </p>
                        <p class="mt-2">For example: <mark>grace-and-peace-e-book.pdf</mark></p>
                    </div>
                    <div class="form-group mt-3">
                        <select id="input_media_id" name="media_id" class="form-control" placeholder="Start typing to search for Media" required></select>
                    </div>
                    <div class="form-group mt-3">
                        <div class="checkbox">
                            <label for="input_force_download">
                                <input id="input_force_download" type="checkbox" name="force_download" value="1" checked>
                                Force media to download automatically
                            </label>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gcb-700 bg-gcb-200 focus:outline-none focus:border-gcb-300 focus:ring focus:ring-gcb-200 disabled:opacity-50 transition ease-in-out duration-150 font-bold">
                            Update media
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>

</div></div>

<script>
spaContentReady(function($) {

    $('#input_media_id').selectize({
        labelField: 'filename',
        valueField: 'id',
        sortField: 'filename',
        searchField: 'filename',
        create: false,
        load: function(query, callback) {
            if (!query.length) return callback();
            axios.post('{{ route('backend.utilities.media_force_download.autocomplete') }}', { query: query })
                .then(function(res) {
                    callback(res.data);
                }).catch(function(err) {
                    callback();
                });
        },
        render: {
            option: function(item, escape) {
                return [
                    '<div class="py-1 px-2 leading-snug">',
                        '<span class="font-medium">' + escape(item.filename) + '</span>',
                        '<small class="text-muted ml-2">(ID: ' + escape(item.id) + ')</small>',
                    '</div>'
                ].join('');
            }
        }
    });

});
</script>

@endsection
