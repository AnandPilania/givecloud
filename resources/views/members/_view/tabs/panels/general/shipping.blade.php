<div class="col-sm-6 @if (!feature('shipping')) hidden @endif">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-truck fa-fw"></i> Shipping
        </div>

        <div class="panel-body">
            <div class="row row-padding-sm">
                @if (sys_get('donor_title') != 'hidden')
                    <div class="col-lg-2 col-md-2 col-sm-3 col-xs-12">
                        <div class="form-group">
                            @if (sys_get('donor_title_options') == '')
                                <input
                                    type="text"
                                    name="ship_title"
                                    id="shiptitle"
                                    class="form-control"
                                    placeholder="Mr/Mrs"
                                    value="{{ $member->ship_title }}">
                            @else
                                <select name="ship_title" id="shiptitle" class="form-control">
                                    <option value="">--</option>
                                    @foreach (explode(',', sys_get('donor_title_options')) as $option)
                                        <option
                                            value="{{ $option }}"
                                            {{ volt_selected($option, $member->ship_title) }}>
                                        {{ $option }}
                                    </option>
                                    @endforeach
                                    @if ($member->ship_title && !in_array($member->ship_title, explode(',', sys_get('donor_title_options'))))
                                        <option value="{{ $member->ship_title }}" selected>
                                            {{ $member->ship_title }}
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
                            id="ship_first_name"
                            name="ship_first_name"
                            value="{{ $member->ship_first_name }}"
                            placeholder="First Name">
                    </div>
                </div>

                <div class="{{ sys_get('donor_title') != 'hidden' ? 'col-lg-5 col-md-5 col-sm-5' : 'col-sm-6' }} col-xs-12">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="ship_last_name"
                            name="ship_last_name"
                            value="{{ $member->ship_last_name }}"
                            placeholder="Last Name">
                    </div>
                </div>

                <div class="col-xs-12">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="ship_organization_name"
                            name="ship_organization_name"
                            value="{{ $member->ship_organization_name }}"
                            placeholder="Organization Name">
                    </div>
                </div>

                <div class="col-xs-12">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="ship_address_01"
                            name="ship_address_01"
                            value="{{ $member->ship_address_01 }}"
                            placeholder="Address Line 1">
                    </div>
                </div>

                <div class="col-xs-12">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="ship_address_02"
                            name="ship_address_02"
                            value="{{ $member->ship_address_02 }}"
                            placeholder="Address Line 2">
                    </div>
                </div>

                <div class="col-xs-8">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="ship_city"
                            name="ship_city"
                            value="{{ $member->ship_city }}"
                            placeholder="City">
                    </div>
                </div>

                <div class="col-xs-4">
                    <div class="form-group">
                        <select
                            class="form-control"
                            id="ship_state"
                            name="ship_state"
                        >
                            <option value="" class="text-placeholder">Select {{ $shippingSubdivisions['subdivision_type'] }}</option>
                            @foreach($shippingSubdivisions['subdivisions'] as $stateCode => $stateName)
                                <option {{ volt_selected($member->ship_state, $stateCode) }} value="{{ $stateCode }}">{{ $stateName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-xs-4">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="ship_zip"
                            name="ship_zip"
                            value="{{ $member->ship_zip }}"
                            placeholder="Zip/Postal Code">
                    </div>
                </div>

                <div class="col-xs-8">
                    <div class="form-group">
                        <select name="ship_country" class="form-control" data-country-state="ship_state">
                            <option value="" class="text-placeholder">Select Country</option>
                            @foreach($countries as $countryCode => $countryName)
                                <option
                                    value="{{ $countryCode }}"
                                    {{ volt_selected($countryCode, $member->ship_country) }}>
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
                                id="ship_phone"
                                name="ship_phone"
                                value="{{ $member->ship_phone }}"
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
                                id="ship_email"
                                name="ship_email"
                                value="{{ $member->ship_email }}"
                                placeholder="email@address.com">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
