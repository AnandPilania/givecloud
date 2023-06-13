
@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Users

            <div class="pull-right">
                <a href="/jpanel/users/add" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-sm hidden-xs"> Add</span></a>
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
                        <th>Email</th>
                        <th width="56">2FA</th>
                        <th width="175">Last Login</th>
                        <th width="175">Created</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td width="16"><a href="/jpanel/users/edit?i={{ $user->id }}"><i class="fa fa-search"></i></a></a></td>
                        <td>
                            {{ $user->firstname }} {{ $user->lastname }}
                            @if ($user->is_account_admin)
                                <div class="small text-muted pull-right"><i class="fa fa-shield"></i> Owner</div>
                            @endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td class="text-center">
                            @if ($user->two_factor_secret)
                                <i class="text-base fa fa-check-circle-o text-green-400" aria-hidden="true"></i>
                            @else
                                <i class="text-base fa fa-times-circle-o text-gray-400 opacity-50" aria-hidden="true"></i>
                            @endif
                        </td>
                        <td data-order="{{ toLocalFormat($user->last_login_at, 'U') }}">
                            @if($user->last_login_at)
                                {{ toLocalFormat($user->last_login_at, 'M j, Y') }} <small>{{ toLocal($user->last_login_at)->diffForHumans() }}</small>
                            @endif
                        </td>
                        <td data-order="{{ toLocalFormat($user->createddatetime, 'U') }}">
                            {{ toLocalFormat($user->createddatetime, 'M j, Y') }} <small>{{ toLocal($user->createddatetime)->diffForHumans() }}</small>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
