{{-- HEADING --}}
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix mb-0 flex w-full @if ($isNew) mb-4 @endif">
            <div class="flex-1 page-header-text">{{ $title }}</div>

            <div class="flex-none text-right">
                @if ($member->userCan('edit'))
                    @if ((dpo_is_enabled() && $member->donor_id) || $member->infusionsoft_contact_id)
                        <div class="btn-group">
                            <a onclick="$('#member_form').submit();" class="btn btn-success">
                                <i class="fa fas fa-check fa-fw"></i>
                                <span class="hidden-xs hidden-sm">Save</span>
                            </a>
                            <button
                                type="button"
                                class="btn
                                btn-success
                                dropdown-toggle"
                                data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a onclick="$('#member_form').submit();">Save</a>
                                </li>
                                @if (dpo_is_enabled() && $member->donor_id)
                                    <li>
                                        <a onclick="onSaveInDp();">
                                            Save &amp; Update DonorPerfect
                                        </a>
                                    </li>
                                @endif
                                @if ($member->infusionsoft_contact_id)
                                    <li>
                                        <a onclick="onSaveInInfusionsoft();">
                                            Save &amp; Update Infusionsoft
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @else
                        <a onclick="$('#member_form').submit();" class="btn btn-success">
                            <i class="fa fas fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span>
                        </a>
                    @endif
                @endif
            </div>
        </h1>
    </div>

    @if (! $isNew)
        <div class="col-lg-12 mb-4 flex flex-col md:flex-row text-gray-500">
            <div>
                <i class="fa fas fa-calendar fa-fw mr-1"></i> Supporter since
                <strong>{{ toLocalFormat($member->created_at, 'M Y') }}</strong>
            </div>

            <div class="mt-2 md:mt-0 md:ml-6">
                <i class="fa fas fa-cart-arrow-down fa-fw mr-1"></i>
                <strong>{{ $orderCount }}</strong>
                @plural('Contribution', $orderCount)
            </div>

            <div class="mt-2 md:mt-0 md:ml-6">
                <i class="fa fas fa-money-bill fa-fw mr-1"></i>
                <strong>{{ money($member->total_order_amount)->format('$0,0[.]00', [
                    '$0,0[.]0a' => $member->total_order_amount > 1000
                ]) }}</strong>
                Lifetime Total
            </div>

            @if ($fundraisingPagesCount)
                <div class="mt-2 md:mt-0 md:ml-6">
                    <a href="{{ route('backend.fundraising_pages.index', ['fundraiser'=> $member->id]) }}">
                        <i class="fa fas fa-user-friends fa-fw mr-1"></i>
                        <strong>{{ $fundraisingPagesCount }}</strong>
                        Fundraising @plural('Page', $fundraisingPagesCount)
                    </a>
                </div>
            @endif

            @if ($sponsorshipCount)
                <div class="mt-2 md:mt-0 md:ml-6">
                        <i class="fa fas fa-child fa-fw mr-1"></i>
                        <strong>{{ $sponsorshipCount }}</strong>
                        @plural('Sponsorship', $sponsorshipCount)
                </div>
            @endif
        </div>
    @endif
</div>

<div class="toastify hide">
    {{ app('flash')->output() }}
</div>

@if ($member->is_spam)
    <div class="alert alert-danger">
        <i class="fa-regular fa-shield-xmark"></i> This supporter has been marked as spam.
    </div>
@endif

@if ($member->is_active == 0 && $isNew == 0)
    <div class="alert alert-danger">
        <i class="fa fas fa-exclamation-triangle fa-fw"></i> This supporter has been archived.
        <a href="{{ route('backend.members.restore', $member->id) }}" class="btn btn-success btn-xs">
            Unarchive
        </a>
    </div>
@endif

@if (! $isNew)
    <div class="flex lg:hidden mb-7">
        <select id="mobileTabSelection" class="form-control">
            <option value="profile" selected>Profile</option>
            @if (user()->can('order'))
                <option value="orders">Contributions</option>
            @endif
            @if (feature('sponsorship') && user()->can('sponsor.view'))
                <option value="sponsorships">Sponsorships</option>
            @endif
            @if (!sys_get('rpp_donorperfect'))
                @if (user()->can('recurringpaymentprofile'))
                    <option value="recurring">Recurring</option>
                @endif
                @if (user()->can('transaction'))
                    <option value="transactions">Recurring History</option>
                @endif
            @endif
            @if (sys_get('tax_receipt_pdfs') && user()->can('taxreceipt.view'))
                <option value="taxreceipts">Tax Receipts</option>
            @endif
            @if (feature('givecloud_pro'))
            <option value="referrals">Referrals</option>
            @endif
            @if (feature('account_notes'))
                <option value="comments-app">Notes</option>
            @endif
            @if (feature('givecloud_pro'))
            <option value="history">History</option>
            @endif
        </select>
        <div class="ml-2 flex-none btn-group">
            <button
                type="button"
                class="btn btn-neutral dropdown-toggle"
                data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu pull-right">
                @if ($member->exists && ($member->userCan('login') || $member->userCan('merge')))
                    @if (feature('givecloud_pro') && $member->userCan('login'))
                        <li>
                            <a href="{{ route('backend.members.login', $member) }}" class="mb-2" target="_blank">
                                <i class="fa fas fa-sign-in fa-fw mr-1"></i>
                                Login as {{ trim($member->first_name) != '' ? $member->first_name : 'Supporter' }}
                            </a>
                        </li>
                    @endif

                    @if ($member->userCan('taxreceipt.edit'))
                        <li>
                            <a
                                href="{{ route('backend.tax_receipts.new', [
                                    'type' => 'consolidated',
                                    'account' => $member->id
                                ]) }}"
                                class="mb-2"
                                target="_blank">
                                <i class="fa fas fa-university fa-fw mr-1"></i> Create a Tax Receipt
                            </a>
                        </li>
                    @endif

                    @if ($member->userCan('merge'))
                        <li>
                            <a href="#" class="mb-2" data-toggle="modal" data-target="#mergeAccount">
                                <i class="fa fas fa-code-branch fa-fw mr-1"></i> Merge Into&hellip;
                            </a>
                        </li>
                    @endif
                @endif

                @if ($member->exists && $member->is_active)
                    <li>
                        <a
                            title="Archive"
                            onclick="onDelete();"
                            class="mb-2 text-danger @if ($isNew == 1) hidden @endif">
                            <i class="fa fas fa-trash fa-fw mr-1"></i> Archive
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
@endif

{{-- CONTENT --}}
<div class="flex">
    @if (! $isNew)
        <div class="flex-none pr-7 non-bootstrap-hidden lg:block w-60">
            <div class="list-group member-tabs" id="member-tabs" role="tablist">
                <a
                    href="#profile"
                    aria-controls="profile"
                    role="tab"
                    data-toggle="tab"
                    class="list-group-item active">
                    Profile
                </a>

                @if (user()->can('order'))
                    <a
                        href="#orders"
                        aria-controls="orders"
                        role="tab"
                        data-toggle="tab"
                        class="list-group-item">
                        Contributions
                    </a>
                @endif

                @if (feature('sponsorship') && user()->can('sponsor.view'))
                    <a
                        href="#sponsorships"
                        aria-controls="sponsorships"
                        role="tab"
                        data-toggle="tab"
                        class="list-group-item">
                        Sponsorships
                    </a>
                @endif

                @if (!sys_get('rpp_donorperfect'))
                    @if (user()->can('recurringpaymentprofile'))
                        <a
                            href="#recurring"
                            aria-controls="recurring"
                            role="tab"
                            data-toggle="tab"
                            class="list-group-item">
                            Recurring
                        </a>
                    @endif

                    @if (user()->can('transaction'))
                        <a
                            href="#transactions"
                            aria-controls="transactions"
                            role="tab"
                            data-toggle="tab"
                            class="list-group-item">
                            Recurring History
                        </a>
                    @endif
                @endif

                @if (sys_get('tax_receipt_pdfs') && user()->can('taxreceipt.view'))
                    <a
                        href="#taxreceipts"
                        aria-controls="taxreceipts"
                        role="tab"
                        data-toggle="tab"
                        class="list-group-item">
                        Tax Receipts
                    </a>
                @endif

                @if (feature('givecloud_pro'))
                <a
                    href="#referrals"
                    aria-controls="referrals"
                    role="tab"
                    data-toggle="tab"
                    class="list-group-item">
                    Referrals
                </a>
                @endif

                @if (feature('account_notes'))
                    <a
                        href="#comments-app"
                        aria-controls="notes"
                        role="tab"
                        data-toggle="tab"
                        class="list-group-item">
                        Notes
                    </a>
                @endif

                @if (feature('givecloud_pro'))
                <a
                    href="#history"
                    aria-controls="history"
                    role="tab"
                    data-toggle="tab"
                    class="list-group-item">
                    History
                </a>
                @endif
            </div>

            <div class="flex flex-col">
                <div class="text-gray-400 text-xs text-bold my-2">
                    ACTIONS
                </div>

                @if ($member->exists && ($member->userCan('login') || $member->userCan('merge')))
                    @if (feature('givecloud_pro') && $member->userCan('login'))
                        <a href="{{ route('backend.members.login', $member) }}" class="mb-2" target="_blank">
                            <i class="fa fas fa-sign-in fa-fw mr-1"></i>
                            Login as {{ trim($member->first_name) != '' ? $member->first_name : 'Supporter' }}
                        </a>
                    @endif

                    @if ($member->userCan('taxreceipt.edit'))
                        <a
                            href="{{ route('backend.tax_receipts.new', [
                                'type' => 'consolidated',
                                'account' => $member->id
                            ]) }}"
                            class="mb-2"
                            target="_blank">
                            <i class="fa fas fa-university fa-fw mr-1"></i> Create a Tax Receipt
                        </a>
                    @endif

                    @if ($member->userCan('merge'))
                        <a href="#" class="mb-2" data-toggle="modal" data-target="#mergeAccount">
                            <i class="fa fas fa-code-branch fa-fw mr-1"></i> Merge Into&hellip;
                        </a>
                    @endif
                @endif

                @if ($member->exists && $member->is_active)
                    <a
                        title="Archive"
                        onclick="onDelete();"
                        class="mb-2 text-danger @if ($isNew == 1) hidden @endif">
                        <i class="fa fas fa-trash fa-fw mr-1"></i> Archive
                    </a>
                @endif
            </div>
        </div>
    @endif

    <div class="flex-grow">
        <div class="tab-content">
            @include('members._view.tabs.general')

            @if ($isNew == 0)
                @include('members._view.tabs.orders')

                @if (feature('sponsorship') && user()->can('sponsor.view'))
                    @include('members._view.tabs.sponsorships')
                @endif

                @if(!sys_get('rpp_donorperfect'))
                    @include('members._view.tabs.recurring')
                    @include('members._view.tabs.transactions')
                @endif

                @if (sys_get('tax_receipt_pdfs') && user()->can('taxreceipt.view'))
                    @include('members._view.tabs.tax_receipts')
                @endif

                @if (feature('givecloud_pro'))
                    @include('members._view.tabs.referrals')
                @endif

                @if (feature('account_notes'))
                    @include('members._view.tabs.comments')
                @endif

                @if (feature('givecloud_pro'))
                    @include('members._view.tabs.history')
                @endif
            @endif
        </div>
    </div>
</div>

@include('members._view.modals.merge_account')

@if (feature('add_from_vault') && $member->id)
    @include('members._view.modals.add_from_vault')
@endif

<script>
    spaContentReady(function($) {
        // Toggle active class for tabs
        // Not specific to this page, so ready to get extracted if necessary.
        $('.list-group[role="tablist"] a.list-group-item[role="tab"]').click(function(e) {
            $(e.target)
                .parent()
                .find('a.list-group-item.active')
                .removeClass('active');

            $(e.target)
                .addClass('active')
                .tab('show');
        });
        $('#mobileTabSelection').on('change', function (e) {
            $('a.list-group-item[role="tab"][href="#' + $(e.target).val() + '"]').click();
        })
    });

    function onDelete () {
        if (confirm('Are you sure you want to de-activate this supporter from your site?')) {
            $('#member_form').attr('action', '/jpanel/supporters/destroy').submit();
        }
    }

    @if ($member->donor_id)
        spaContentReady(function($) {
            var $modal = $('#save-to-dp').modal({ show: false });

            $modal.on('show.bs.modal', function() {
                $.getJSON('/jpanel/donors/<?= e($member->donor_id); ?>.json', function(donor) {
                    $modal.find('.donor-info').html(donor.preview);
                });
            });

            window.onSaveInDp = function() {
                $modal.modal('show');
            }
        });
    @endif

    @if ($member->infusionsoft_contact_id)
        function onSaveInInfusionsoft() {
            $('#member_form').append('<input type="hidden" name="update_infusionsoft" value="1">');
            $('#member_form').submit();
        }
    @endif

    spaContentReady(function() {
        function openModalForGroupAccount (ev) {
            ev.preventDefault();
            var group_account_id = $(this).data('group-account-id');

            openModal(
                '{{ route('backend.group_accounts.modal', 1234567890) }}'
                    .replace('1234567890', group_account_id)
            );
        }

        function initializeModal () {
            var $modalContainer = $(this);
            j.ui.formatSpecialFields();

            $modalContainer.find('[data-group-account-id]').click(openModalForGroupAccount);

            $modalContainer.find('[data-group-account-action=delete]').click(function(ev) {
                ev.preventDefault();

                if (confirm('Are you sure?')) {
                    $('#group-account-form')
                        .attr('action', '{{ route('backend.group_account.destroy') }}')
                        .submit();
                }
            });
        }

        function openModal (url) {
            var $modal = $('#group-account-modal');

            if ($modal.length) {
                $modal.find('.modal-content').html('<div class="text-center text-muted" style="margin:60px;"><i class="fa fas fa-4x fa-spin fa-spinner"></i></div>').load(url, initializeModal);
            } else {
                $('<div class="modal fade modal-primary" id="group-account-modal">' +
                        '<div class="modal-dialog">' +
                            '<div class="modal-content">' +
                                '<div class="text-center text-muted" style="margin:60px;">' +
                                    '<i class="fa fas fa-4x fa-spin fa-spinner"></i>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>')
                    .on('shown.bs.modal', function() {
                        $(this).find('.modal-content').load(url, initializeModal);
                    })
                    .on('hidden.bs.modal', function() {
                        $(this).remove();
                    })
                    .modal('show');
            }
        }

        $('[data-group-account-id]').click(openModalForGroupAccount);

        $('[data-group-account-action=add]').click(function(ev) {
            ev.preventDefault();
            var account_id = $(this).data('account-id');

            openModal('{{ route('backend.group_accounts.modal_add') }}?account_id=' + account_id);
        });

        $('[data-historical-changes]').click(function(ev) {
            ev.preventDefault();

            var changes = '',
                metadata = $(this).data('historical-changes');

            if (metadata.changes) {
                $.each(metadata.changes, function(col, val){
                    changes += '<div>' +
                            '<strong>' + col + '</strong> changed to ' +
                            '<strong>' + ((col=='password') ? '(hidden)' : _.escape(val)) + '</strong>.' +
                        '</div>';
                });
            }

            if (metadata.url_reference) {
                if (metadata.url_reference.indexOf('/jpanel') >= 0) {
                    changes += '<hr>Changes made from <strong>GC Admin Panel</strong> <small>(' + metadata.url_reference + ')</small>';
                } else {
                    changes += '<hr>Changes made from <strong>Website</strong> <small>(' + metadata.url_reference + ')</small>';
                }
            }

            $('<div class="modal fade modal-primary" id="group-account-modal">' +
                    '<div class="modal-dialog">' +
                        '<div class="modal-content">' +
                            '<div class="modal-body">' +
                                '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                                '<h3 style="margin-top:10px; margin-bottom:20px; color:#666; font-weight:light; ">Updates</h3>' +
                                '<div>' + changes + '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>')
                .append('body')
                .modal('show')
                .on('hidden.bs.modal', function() {
                    $(this).remove();
                });
        });
    });
</script>
