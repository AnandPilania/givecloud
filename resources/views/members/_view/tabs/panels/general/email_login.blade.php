<div class="col-xs-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-lock fa-fw"></i> Email &amp; Login
        </div>

        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label for="email">Email <small>(also used to login)</small></label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fas fa-envelope-o fa-fw"></i>
                            </div>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="{{ $member->email }}"
                                placeholder="email@address.com"
                                autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="email_opt_in" value="0">
                        <input
                            id="email_opt_in"
                            name="email_opt_in"
                            type="checkbox"
                            class="custom-control-input"
                            value="1"
                            {{ volt_checked($member->email_opt_in, 1) }}>
                        <label for="email_opt_in" class="custom-control-label">
                            Send me emails and updates
                        </label>
                    </div>
                </div>

                @if ($isNew && feature('givecloud_pro'))
                    <div class="form-group col-xs-12">
                        <label>Password</label>
                        <input
                            class="form-control"
                            autocomplete="off"
                            type="password"
                            name="password"
                            id="password">
                    </div>
                @elseif (feature('givecloud_pro'))
                    <div class="form-group col-xs-12">
                        <label>Password</label>
                        <div class="input-group" id="reset_password">
                            <a
                                class="btn btn-info"
                                onclick="
                                    $('#reset_password_form').removeClass('hidden');
                                    $('#reset_password').addClass('hidden');
                                    $('#password').focus();
                                    return false;">
                                Click to Reset
                            </a>
                        </div>
                        <div class="input-group hidden" id="reset_password_form">
                            <input
                                class="form-control"
                                autocomplete="off"
                                type="password"
                                name="password"
                                id="password">
                            <div
                                class="input-group-addon btn"
                                onclick="
                                    $('#reset_password_form').addClass('hidden');
                                    $('#reset_password').removeClass('hidden');
                                    $('#password').val('');
                                    return false;">
                                Cancel
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
