@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header clearfix">
                {{ $pageTitle }}

                <div class="visible-xs-block"></div>

                <div class="pull-right">
                   @if(user()->can('posttype.add'))
                        <a href="{{ route('backend.feeds.add') }}" class="btn btn-success"><i class="fa fa-plus fa-fw"></i> Add</a>
                    @endif
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
                            <th>Name</th>
                            <th>Posts</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($postTypes as $postType)
                        <tr>
                            <td width="16"><a href="{{ route('backend.post.index', ['i' => $postType->id]) }}"><i class="fa fa-search"></i></a></td>
                            <td>{{ $postType->name }}</td>
                            <td>{{ $postType->posts_count }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
