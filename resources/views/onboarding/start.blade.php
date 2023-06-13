
@extends('layouts.guest')

@section('title', 'Setup')
@section('body_classes', '')

@section('content')
    <x-guest.container title="Finish Your Setup">

        <form action="/jpanel/onboard/finish" method="post" enctype="multipart/form-data" class="w-full">
            @csrf

            <div>
                <label for="currency" class="block text-sm font-medium leading-5 text-gray-700">
                    Select your currency
                </label>
                <div class="max-w-xs rounded-md shadow-sm">
                    <select id="currency" name="currency" class="block form-select w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                        <option value="" disabled selected hidden>Please Choose...</option>
                        @foreach ($pinned_currencies as $currency)
                            <option value="<?= e($currency['code']) ?>" <?= e(volt_selected(sys_get('dpo_currency'), $currency['code'])); ?>>(<?= e($currency['code']) ?>) <?= e($currency['name']) ?></option>
                        @endforeach
                        <option value="" disabled>-------</option>
                        @foreach ($other_currencies as $currency)
                            <option value="<?= e($currency['code']) ?>" <?= e(volt_selected(sys_get('dpo_currency'), $currency['code'])); ?>>(<?= e($currency['code']) ?>) <?= e($currency['name']) ?></option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label for="timezone" class="block text-sm font-medium leading-5 text-gray-700">
                    What timezone are you in?
                </label>
                <div class="max-w-xs rounded-md shadow-sm">
                    <select id="timezone" name="timezone" class="block form-select w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                        <option value="" disabled selected hidden>Please Choose...</option>
                        @foreach (config('timezone.zones') as $zone => $id)
                            <option value="{{ $id }}" @if($id == sys_get('timezone')){{ 'selected' }}@endif>{{ $zone }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label for="number_of_employees" class="block text-sm font-medium leading-5 text-gray-700">
                    How many staff members do you have?
                </label>
                <div class="max-w-xs rounded-md shadow-sm">
                    <select id="number_of_employees" name="number_of_employees" class="block form-select w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                        <option value="" disabled selected hidden>Please Choose...</option>
                        <option>1-5</option>
                        <option>6-10</option>
                        <option>10-25</option>
                        <option>25+</option>
                        <option>Volunteers Only (No paid staff)</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label for="annual_fundraising_goal" class="block text-sm font-medium leading-5 text-gray-700">
                    What's your annual fundraising goal?
                </label>
                <div class="max-w-xs rounded-md shadow-sm">
                    <select id="annual_fundraising_goal" name="annual_fundraising_goal" class="block form-select w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                        <option value="" disabled selected hidden>Please Choose...</option>
                        <option>&lt; $100K</option>
                        <option>$100K to 250K</option>
                        <option>$250K to $750K</option>
                        <option>$750K to $1.25M</option>
                        <option>$1.25M to $3M</option>
                        <option>$5M+</option>
                        <option>Don't know</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label for="market_category" class="block text-sm font-medium leading-5 text-gray-700">
                    Which category best describes your organization?
                </label>
                <div class="max-w-xs rounded-md shadow-sm">
                    <select id="market_category" name="market_category" class="block form-select w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                        <option value="" disabled selected hidden>Please Choose...</option>
                        @foreach ($market_groups as $group => $markets)
                            <optgroup label="{{ $group }}">
                                @foreach ($markets as $market)
                                    <option value="{{ $group }} > {{ $market }}">{{ $market }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-12 text-center">
                <span class="rounded-md shadow-sm">
                    <button type="submit" class="py-3 inline-flex items-center px-6 border border-transparent text-lg font-medium rounded-full text-white bg-brand-pink hover:bg-pink-500 focus:outline-none focus:border-brand-pink focus:ring focus:ring-pink-200 active:bg-pink-700 transition duration-150 ease-in-out">
                        Let's Go <x-icons.arrow-right class="inline ml-2" />
                    </button>
                </span>
            </div>

        </form>
    </x-guest.container>

@endsection
