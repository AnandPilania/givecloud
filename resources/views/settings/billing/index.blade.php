
@extends('layouts.app')
@section('title', 'Billing')

@section('content')

    @inject('flash', 'flash')

    {{ $flash->output() }}

    <form class="form-horizontal" action="/jpanel/settings/billing/save" method="post">
        @csrf

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">
                    Billing

                    <div class="pull-right">
                        @if (is_super_user())
                            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
                        @endif
                    </div>
                </h1>
            </div>
        </div>

        @includeWhen($shouldShowPlans, 'settings.billing.plans')

        @includeWhen($fromDonorPerfectWithoutSubscription, 'settings.billing.dp')

        @includeWhen($shouldShowCurrentBillingScreen, 'settings.billing.subscription')

        @includeWhen(is_super_user(), 'settings.billing.super-user')

    </form>

@endsection
