<div class="col-sm-6 @if (!sys_get('infusionsoft_token')) hidden @endif">
    <div class="panel panel-info">
        <div class="panel-heading">
            Infusionsoft Integration
        </div>

        <div class="panel-body">
            <div class="row row-padding-sm">
                <div class="form-group col-sm-12">
                    <label>Contact ID</label>
                    <input
                        class="form-control"
                        type="text"
                        name="infusionsoft_contact_id" id="infusionsoft_contact_id"
                        value="{{ $member->infusionsoft_contact_id }}"
                        style="max-width: 320px">
                    @if ($member->infusionsoft_contact_id)
                        <div class="help-block">
                            <a
                                href="{{ sprintf('https://%s.infusionsoft.com/Contact/manageContact.jsp?view=edit&ID=%d',
                                    sys_get('infusionsoft_account'),
                                    $member->infusionsoft_contact_id
                                ) }}"
                                target="_blank">
                                <i class="fa fas fa-search"></i> View contact in Infusionsoft
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
