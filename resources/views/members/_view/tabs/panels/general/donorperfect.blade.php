<div class="col-sm-6 @if (!dpo_is_enabled()) hidden @endif">
    <div class="panel panel-info">
        <div class="panel-heading">
            <img src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline">
            DonorPerfect Integration
        </div>

        <div class="panel-body">
            <div class="row row-padding-sm">
                <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <label>Donor ID</label>
                    @if (user()->can('admin.dpo'))
                        <div class="input-group">
                            <input
                                class="form-control"
                                type="text"
                                name="donor_id"
                                id="donor_id"
                                value="{{ $member->donor_id }}"
                                maxlength="9">
                            <div class="input-group-btn">
                                <a
                                    href="#"
                                    class="btn btn-info dp-donor"
                                    data-input="donor_id"
                                    data-first-name="{{ $member->first_name }}"
                                    data-last-name="{{ $member->last_name }}"
                                    data-email="{{ $member->email }}">
                                    <i class="fa fas fa-external-link fa-fw"></i> View
                                </a>
                            </div>
                        </div>
                    @else
                        <input
                            class="form-control"
                            type="text"
                            name="donor_id"
                            id="donor_id"
                            value="{{ $member->donor_id }}"
                            maxlength="9"
                            readonly>
                    @endif
                </div>

                <div class="form-group col-lg-8 col-md-8 col-sm-8 col-xs-12">
                    <label>Membership Sync Status</label>
                    <select
                        class="form-control"
                        id="sync_status"
                        name="sync_status"
                        @if (!user()->can('admin.dpo')) readonly @endif>
                        <option value="-1">
                            Use Global Setting
                            ({{ sys_get('keep_memberships_synced_with_dpo') == 1
                                ? 'Always Sync with DPO'
                                : 'Never Sync with DPO'
                            }})
                        </option>
                        <option
                            value="1"
                            {{ volt_selected($member->sync_status, '1') }}>
                            Always Sync with DPO
                        </option>
                        <option
                            value="0"
                            {{ volt_selected($member->sync_status, '0') }}>
                            Never Sync with DPO
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
