<div class="@if (feature('givecloud_pro')) col-sm-6 @else col-sm-12 @endif">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-credit-card fa-fw"></i> Billing
        </div>

        <div class="panel-body">
            <div class="row row-padding-sm">
                @if (sys_get('donor_title') != 'hidden')
                    <div class="col-lg-2 col-md-2 col-sm-3 col-xs-12">
                        <div class="form-group">
                            @if (sys_get('donor_title_options') == '')
                                <input
                                    type="text"
                                    name="bill_title"
                                    id="billtitle"
                                    class="form-control"
                                    placeholder="Mr/Mrs"
                                    value="{{ $member->bill_title }}">
                            @else
                                <select name="bill_title" id="billtitle" class="form-control">
                                    <option value="">--</option>
                                    @foreach (explode(',', sys_get('donor_title_options')) as $option)
                                        <option
                                            value="{{ $option }}"
                                            {{ volt_selected($option, $member->bill_title) }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                    @if ($member->bill_title && !in_array($member->bill_title, explode(',', sys_get('donor_title_options'))))
                                        <option value="{{ $member->bill_title }}" selected>
                                            {{ $member->bill_title }}
                                        </option>
                                    @endif
                                </select>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="{{ sys_get('donor_title') != 'hidden' ? 'col-lg-5 col-md-5 col-sm-4' : 'col-sm-6' }} col-xs-12">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="bill_first_name"
                            name="bill_first_name"
                            value="{{ $member->bill_first_name }}"
                            placeholder="First Name">
                    </div>
                </div>

                <div class="{{ (sys_get('donor_title') != 'hidden') ? 'col-lg-5 col-md-5 col-sm-5' : 'col-sm-6' }} col-xs-12">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="bill_last_name"
                            name="bill_last_name"
                            value="{{ $member->bill_last_name }}"
                            placeholder="Last Name">
                    </div>
                </div>

                @if (feature('givecloud_pro'))
                <div class="col-xs-12">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="bill_organization_name"
                            value="{{ $member->bill_organization_name }}"
                            placeholder="Organization Name"
                            readonly>
                    </div>
                </div>
                @endif

                <div class="col-xs-12">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="bill_address_01"
                            name="bill_address_01"
                            value="{{ $member->bill_address_01 }}"
                            placeholder="Address Line 1">
                    </div>
                </div>

                <div class="col-xs-12">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="bill_address_02"
                            name="bill_address_02"
                            value="{{ $member->bill_address_02 }}"
                            placeholder="Address Line 2">
                    </div>
                </div>

                <div class="col-xs-8">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="bill_city"
                            name="bill_city"
                            value="{{ $member->bill_city }}"
                            placeholder="City">
                    </div>
                </div>

                <div class="col-xs-4">
                    <div class="form-group">
                        <select
                            class="form-control"
                            id="bill_state"
                            name="bill_state"
                        >
                            <option value="" class="text-placeholder">Select {{ $billingSubdivisions['subdivision_type'] }}</option>
                            @foreach($billingSubdivisions['subdivisions'] as $stateCode => $stateName)
                                <option {{ volt_selected($member->bill_state, $stateCode) }} value="{{ $stateCode }}">{{ $stateName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-xs-4">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="bill_zip"
                            name="bill_zip"
                            value="{{ $member->bill_zip }}"
                            placeholder="Zip/Postal Code">
                    </div>
                </div>

                <div class="col-xs-8">
                    <div class="form-group">
                        <select name="bill_country" class="form-control" data-country-state="bill_state">
                            <option value="" class="text-placeholder">Select Country</option>
                            @foreach($countries as $countryCode => $countryName)
                                <option
                                    value="{{ $countryCode }}"
                                    {{ volt_selected($countryCode, $member->bill_country) }}>
                                    {{ $countryName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fas fa-phone fa-fw"></i>
                            </div>
                            <input
                                type="tel"
                                class="form-control"
                                id="bill_phone"
                                name="bill_phone"
                                value="{{ $member->bill_phone }}"
                                placeholder="555-555-5555">
                        </div>
                    </div>
                </div>

                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fas fa-envelope-o fa-fw"></i>
                            </div>
                            <input
                                type="email"
                                class="form-control"
                                id="bill_email"
                                name="bill_email"
                                value="{{ $member->bill_email }}"
                                placeholder="email@address.com">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
