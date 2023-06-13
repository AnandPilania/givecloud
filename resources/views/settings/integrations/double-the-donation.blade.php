@extends('layouts.app')
@section('title', 'Double The Donation')

@section('content')
<form action="{{ route('backend.settings.integrations.double-the-donation.store') }}"
      method="post"
      x-data="{ is_enabled: {{ sys_get('double_the_donation_enabled')}}, public_key:'{{sys_get('double_the_donation_public_key')}}', private_key:'{{sys_get('double_the_donation_private_key')}}'}">
    @csrf

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Double The Donation

                <div class="pull-right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
                </div>
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-8 col-lg-offset-2">
            <div class="relative bg-brand-blue mb-7 rounded-lg shadow py-3 px-3 sm:px-6 lg:px-8 text-center">
                <p class="text-base font-medium text-white">
                    Our 360match integration only works with our
                      <a href="{{ route('backend.fundraising.forms') }}" class="hover:text-white hover:underline font-bold text-white underline">
                        new fundraising experiences
                      </a>
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-8 col-lg-offset-2">

            <div class="form-horizontal">

                {{ app('flash')->output() }}

                <div class="panel panel-default">
                    <div class="panel-heading visible-xs">
                        <i class="fa fa-gear"></i> Double the Donation
                    </div>
                    <div class="panel-body" style="padding-bottom:15px">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 hidden-xs">
                                <div class="panel-sub-title"><i class="fa fa-gear"></i> Double the Donation</div>
                                <div class="panel-sub-desc">
                                    <p>Enable your Double the Donation integration</p>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-8 pt-6">
                                <div class="form-group">
                                    <div class="flex items-center">
                                        <label for="double_the_donation_enabled" class="col-md-4 text-right mr-2 mb-0 ">Enable</label>
                                        <button type="button" class="relative ml-1 inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue" :class="is_enabled ? 'bg-blue-500 hover:bg-blue-600' : 'bg-gray-200 hover:bg-gray-300'" @click="is_enabled = !is_enabled" role="switch" aria-checked="false" aria-labelledby="enable-label">
                                            <span aria-hidden="true" :class="is_enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </button>
                                    </div>
                                </div>
                                <input type="checkbox" class="hidden" name="double_the_donation_enabled" :checked="is_enabled" x-model="is_enabled" value="1" />
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="is_enabled" class="panel panel-default">
                    <div class="panel-heading visible-xs">
                        <i class="fa fa-lock"></i> Consumer Keys
                    </div>
                    <div class="panel-body" style="padding-bottom:15px">

                        <div class="row">

                            <div class="col-sm-6 col-md-4 hidden-xs">
                                <div class="panel-sub-title"><i class="fa fa-lock"></i> Consumer Keys</div>
                                {{--
                                <div class="panel-sub-desc">
                                    <p>Provide your public and private keys.</p>
                                    <p class="mt-4 max-w-[340px]">If you don't already have a 360MatchPro account you can <a href="#">fill out this form</a> to request one.</p>
                                </div>
                                --}}
                            </div>

                            <div class="col-sm-6 col-md-8">
                                <div class="form-group">
                                    <label for="name" class="col-md-4 control-label">Public Key</label>
                                    <div class="col-md-8">
                                        <input x-model="public_key" type="text" class="form-control" id="double_the_donation_public_key" name="double_the_donation_public_key" value="{{ sys_get('double_the_donation_public_key') }}" />
                                    </div>
                                </div>

                                <div class="form-group has-feedback">
                                    <label for="name" class="col-md-4 control-label">Private Key</label>
                                    <div class="col-md-8">
                                        <input x-model="private_key" type="password" class="form-control password" class="form-control" id="double_the_donation_private_key" name="double_the_donation_private_key" value="{{ sys_get('double_the_donation_private_key') }}" />
                                        <i class="glyphicon glyphicon-eye-open form-control-feedback"></i>
                                    </div>
                                </div>

                                <button type="submit" formaction="{{ route('backend.settings.integrations.double-the-donation.test') }}" x-bind:disabled="!public_key || !private_key" class="inline-flex items-center pull-right btn btn-default"><i class="fa fa-exchange mr-2"></i> Save & Test</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="is_enabled" class="panel panel-default">
                    <div class="panel-heading visible-xs">
                        <i class="fa fa-sync"></i> Register All Donations
                    </div>
                    <div class="panel-body" style="padding-bottom:15px">

                        <div class="row">

                            <div class="col-sm-6 col-md-4 hidden-xs">
                                <div class="panel-sub-title"><i class="fa fa-sync"></i> Register All Donations</div>
                                <div class="panel-sub-desc">
                                    <p>Determines which contributions will be pushed to Double The Donation. </p>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-8">
                                <div class="form-group">
                                    <div class="col-md-4 text-right">
                                        <input @checked(sys_get('bool:double_the_donation_sync_all_contributions')) id="enable" value="1" name="double_the_donation_sync_all_contributions" type="radio" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <label for="enable" class="mt-0.5 font-bold text-gray-700">Enable<span class="inline-flex items-center ml-2 px-1.5 py-0 rounded text-xxs font-bold uppercase bg-cyan-100 text-gray-900">Recommended</span></label>
                                            <p class="text-gray-500">All Contributions will be pushed to Double The Donations</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <div class="col-md-4 text-right">
                                        <input @checked(!sys_get('bool:double_the_donation_sync_all_contributions')) id="disable" value="0" name="double_the_donation_sync_all_contributions" type="radio" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <label for="disable" class="mt-0.5 font-bold text-gray-700">Disable</label>
                                            <p class="text-gray-500">Only Contributions with matching company will be pushed to Double the Donation </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- /.form-horizontal -->
        </div>
    </div>
</form>
@endsection
