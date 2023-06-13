
@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            My Profile
        </h1>
    </div>
</div>

@inject('flash', 'flash')

{{ $flash->output() }}

@if (is_super_user())
    <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> This super user is locked and cannot be edited.</div>
@else

    <div>
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Profile</h3>
                    <p class="mt-1 text-sm leading-5 text-gray-600">
                        Update your account's profile information and email address.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form action="{{ route('user-profile-information.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="shadow overflow-hidden sm:rounded-md">
                        <div class="px-4 py-5 bg-white sm:p-6">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3">
                                    <label for="inputFirstName" class="block text-sm font-medium leading-5 text-gray-700">First name</label>
                                    <input id="inputFirstName" name="first_name" value="{{ old('first_name', $user->firstname) }}" class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('first_name', 'updateProfileInformation') border-red-500 @enderror">
                                    @error('first_name', 'updateProfileInformation') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="inputLastName" class="block text-sm font-medium leading-5 text-gray-700">Last name</label>
                                    <input id="inputLastName" name="last_name" value="{{ old('last_name', $user->lastname) }}" class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('last_name', 'updateProfileInformation') border-red-500 @enderror">
                                    @error('last_name', 'updateProfileInformation') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="col-span-6 sm:col-span-4">
                                    <label for="inputEmail" class="block text-sm font-medium leading-5 text-gray-700">Email address</label>
                                    <input id="inputEmail" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('email', 'updateProfileInformation') border-red-500 @enderror">
                                    @error('email', 'updateProfileInformation') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="col-span-6 sm:col-span-6 lg:col-span-2">
                                    <label for="inputPhone" class="block text-sm font-medium leading-5 text-gray-700">Phone</label>
                                    <input id="inputPhone" name="phone" value="{{ old('phone', $user->primaryphonenumber) }}" class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('phone', 'updateProfileInformation') border-red-500 @enderror">
                                    @error('phone', 'updateProfileInformation') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            @if (feature('givecloud_pro') && $user->is_account_admin)
                                <div class="mt-6">
                                    <div class="relative flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="inputNotifyRecurringBatchSummary" name="notify_recurring_batch_summary" type="checkbox" value="1" @checked($user->notify_recurring_batch_summary) class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out rounded focus:ring focus:ring-indigo-200">
                                        </div>
                                        <div class="ml-3 text-sm leading-5">
                                            <label for="inputNotifyRecurringBatchSummary" class="font-medium text-gray-700">Recurring Payment Summary</label>
                                            <p class="text-gray-500">Receive summary email from Givecloud after recurring payments have processed.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="px-4 py-3 bg-gray-50 sm:px-6">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gcb-700 bg-gcb-200 focus:outline-none focus:border-gcb-300 focus:ring focus:ring-gcb-200 disabled:opacity-50 transition ease-in-out duration-150 font-bold">
                                Save profile information
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="non-bootstrap-hidden sm:block">
        <div class="py-5">
            <div class="border-t border-gray-200"></div>
        </div>
    </div>

    @include('users.profile.password')
    @include('users.profile.connected-accounts')
    @include('users.profile.notifications')

    @if (feature('givecloud_pro'))
    <div class="non-bootstrap-hidden sm:block">
        <div class="py-5">
            <div class="border-t border-gray-200"></div>
        </div>
    </div>

    @include('users.profile.pinned-menu-items')

    <div class="non-bootstrap-hidden sm:block">
        <div class="py-5">
            <div class="border-t border-gray-200"></div>
        </div>
    </div>

    @include('users.profile.2fa')
    @endif

    @include('users.profile.api-key')

    <div class="non-bootstrap-hidden sm:block">
        <div class="py-5">
            <div class="border-t border-gray-200"></div>
        </div>
    </div>

    @if (feature('givecloud_pro'))
    @include('users.profile.personal-access-tokens')
    @endif

    <div class="mb-36"></div>

@endif
@endsection
