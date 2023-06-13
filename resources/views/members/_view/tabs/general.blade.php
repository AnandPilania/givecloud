<style>
    #___email, #___password {
        width:0px;
        height:0px;
        position:absolute;
        top:-3000px;
        left:-3000px;
    }
</style>

<div role="tabpanel" class="tab-pane fade in active" id="profile">
    <form id="member_form" role="form" name="member" method="post" action="{{ route('backend.member.save') }}" autocomplete="off">
        @csrf
        <input type="hidden" name="id" value="{{ $member->id }}">
        <input type="email" name="___email" id="___email" value="" tabindex="-1">
        <input type="password" name="___password" id="___password" value="" tabindex="-2">

        <div class="row">@include('members._view.tabs.panels.general.general')</div>
        <div class="row">@include('members._view.tabs.panels.general.email_login')</div>
        <div class="row">
            @include('members._view.tabs.panels.general.billing')
            @include('members._view.tabs.panels.general.shipping')
        </div>

        <div class="row">
            @include('members._view.tabs.panels.general.memberships')
            @include('members._view.tabs.panels.general.donorperfect')
            @include('members._view.tabs.panels.general.infusionsoft')
            @includeWhen(sys_get('salesforce_enabled'), 'members._view.tabs.panels.general.salesforce')
        </div>

        @if (dpo_is_enabled() && $member->donor_id)
            <div class="modal fade modal-primary" id="save-to-dp">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">
                                <i class="fa fas fa-plus fa-fw"></i> Push Updates to DonorPerfect
                            </h4>
                        </div>

                        <div class="modal-body">
                            <p>Are you sure you want to update DonorPerfect with the data you changed in Givecloud?</p>

                            <div style="width:50%; margin:20px auto; text-align: center;">
                                <strong>Currently In DonorPerfect:</strong><br><br>
                                <strong>Donor ID: {{ $member->donor_id }}</strong><br>
                                <div class="donor-info">
                                    <div style="padding:20px; text-align:center;">
                                        <i class="fa fas fa-spinner fa-spin"></i> Loading from DonorPerfect&hellip;
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" name="update_dpo" value="1" class="btn btn-primary">
                                <i class="fa fas fa-check fa-fw"></i> Push Updates to DP
                            </button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </form>
</div>
