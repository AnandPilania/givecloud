<div class="col-xs-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-user fa-fw"></i> General
        </div>
        <div class="panel-body">
            <div class="row row-padding-sm">
                @if (sys_get('donor_title') != "hidden")
                    <div class="form-group col-md-2 col-sm-3 col-xs-5">
                        <label>Title</label>
                        @if (sys_get('donor_title_options') == "")
                            <input type="text" name="title" id="title" class="form-control" placeholder="Mr/Mrs" value="{{ $member->title }}">
                        @else
                            <select name="title" id="title" class="form-control">
                                <option value="">--</option>
                                @foreach (explode(',', sys_get('donor_title_options')) as $option)
                                    <option
                                        value="{{ $option }}"
                                        {{ volt_selected($option, $member->title) }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                                @if ($member->title && !in_array($member->title, explode(',', sys_get('donor_title_options'))))
                                    <option
                                        value="{{ $member->title }}"
                                        selected>
                                        {{ $member->title }}
                                    </option>
                                @endif
                            </select>
                        @endif
                    </div>
                @endif

                <div class="form-group {{ sys_get('donor_title') === 'hidden' ? 'col-sm-6' : 'col-md-5 col-sm-4 col-xs-7' }}">
                    <label>First Name</label>
                    <input
                        class="form-control"
                        type="text"
                        name="first_name"
                        id="first_name"
                        value="{{ $member->first_name }}">
                </div>
                <div class="form-group {{ sys_get('donor_title') === 'hidden' ? 'col-sm-6' : 'col-md-5 col-sm-5 col-xs-12' }}">
                    <label>Last Name</label>
                    <input
                        class="form-control"
                        type="text"
                        name="last_name"
                        id="last_name"
                        value="{{ $member->last_name }}">
                </div>
            </div>

            <div class="row row-padding-sm">
                @if (feature('givecloud_pro'))
                <div class="form-group col-md-6 col-sm-4 col-xs-12">
                    <label>Organization Name</label>
                    <input
                        type="text"
                        class="form-control"
                        id="bill_organization_name"
                        name="bill_organization_name"
                        value="{{ $member->bill_organization_name }}"
                        placeholder="Organization Name">
                </div>
                @endif

                @if (feature('givecloud_pro'))
                <div class="form-group col-md-3 col-sm-4 col-xs-12">
                    <label>Supporter Type</label>
                    <select name="account_type_id" id="accounttypeid" class="form-control">
                        @foreach ($account_types as $type)
                            <option
                                value="{{ $type->id }}"
                                data-organization="{{ $type->is_organization }}"
                                {{ volt_selected($type->id, $member->account_type_id) }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if (sys_get('referral_sources_isactive'))
                    <div
                        class="form-group col-md-3 col-sm-4 col-xs-12"
                        data-popover-bottom="<strong>Referral Source</strong><br><i>'How did you hear about us?'</i><br>Referral source helps you understand your most successful means of raising awareness.">
                        <label>Referral Source</label>
                        <input
                            class="form-control"
                            autocomplete="off"
                            type="text"
                            name="referral_source"
                            id="referral_source"
                            value="{{ $member->referral_source }}">
                    </div>
                @endif

                @if (feature('givecloud_pro'))
                <div
                    class="form-group col-md-6 col-sm-4 col-xs-12"
                    data-popover-top="<strong>Referred By</strong><br><i>This is the supporter that referred {{ $member->display_name }} to your organization. This association is one of the factors that drives Secondary Impact.">
                    <label>Referred By</label>
                    <select class="form-control ds-members" name="referred_by" placeholder="Find a supporter&hellip;">
                    @if ($member->referrer)
                        <option
                            value="{{ $member->referrer->id }}"
                            data-data="{{ json_encode([ 'icon' => $member->referrer->fa_icon, 'email' => $member->referrer->email ]) }}"
                            selected>
                            {{ $member->referrer->display_name }}
                        </option>
                    @endif
                    </select>
                </div>
                @endif

                @if (sys_get('fundraising_pages_requires_verify'))
                    <div class="form-group col-md-6 col-sm-4 col-xs-12">
                        <label>Verified Fundraiser Status</label>
                        <select class="form-control" name="verified_status" placeholder="Verified Fundraiser Status...">
                            <option value="">Unverified</option>
                            @foreach(\Ds\Enums\Supporters\SupporterVerifiedStatus::all() as $status)
                                <option value="{{ $status }}" {{ volt_selected($status, $member->verified_status)}}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (sys_get('nps_enabled'))
                    <div
                        class="form-group col-md-3 col-sm-4 col-xs-12"
                        data-popover-bottom="<strong>Net Promoter Score (NPS)</strong><br><i>'How likely are you to recommend us?'</i><br>Net Promoter Score is a gauge of your donor's satisfaction and the likelihood they'll promote your cause.">
                        <label>NPS <small>(1-10)</small></label>
                        <div style="margin:0px 10px 7px 10px;">
                            <input class="form-control slider" type="text" autocomplete="off" name="nps" id="nps"
                                value="{{ $member->nps ?: 0 }}"
                                data-provide="slider"
                                data-slider-min="0"
                                data-slider-max="10"
                                data-slider-step="1"
                                data-slider-value="{{ $member->nps ?: 0 }}"
                                data-slider-rangeHighlights='[{ "start": 0, "end": 1, "class": "bg-default" },
                                    { "start": 1, "end": 6, "class": "bg-danger" },
                                    { "start": 6, "end": 9, "class": "bg-warning" },
                                    { "start": 9, "end": 10, "class": "bg-success" }]'>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
