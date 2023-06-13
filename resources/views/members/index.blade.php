@extends('layouts.app')
@section('title', 'Supporters')

@section('content')

    @inject('flash', 'flash')

    {{ $flash->output() }}

    <script>
        exportRecordsEmail = function () {
            var d = j.ui.datatable.filterValues('table.dataTable');
            window.location = '/jpanel/supporters/export/emails?' + $.param(d);
        }
        exportRecordsCsv = function () {
            var d = j.ui.datatable.filterValues('table.dataTable');
            window.location = '/jpanel/supporters/export/all?' + $.param(d);
        }
    </script>

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header clearfix">
                Supporters
                <div class="visible-xs-block"></div>

                <div class="pull-right">
                    @if (user()->can('member.add'))
                        <div class="btn-group">
                            <button title="Export account data." type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-plus fa-fw"></i>
                                <span class="hidden-xs hidden-sm"> Add</span> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right">
                                <li><a href="{{ route('backend.member.add')}}" title="Add a new supporter."><span class="hidden-xs hidden-sm"> Add a supporter</span></a></li>
                                @if(feature('flatfile_supporter_imports'))
                                    <li><a href="#" id="flatFileImport" onclick="j.importer('{{ $flatfileToken }}'); return false;">Import Supporters</a></li>
                                    <li><a href="{{ route('backend.import.template.download', 'Supporters') }}">Download Import Template</a></li>
                                @endif
                            </ul>

                        </div>
                    @endif

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-outline dropdown-toggle bg-white" data-toggle="dropdown"><i class="fa fa-list-ul fa-fw"></i> Bulk... <span class="badge checkbox-counter"></span> <span class="caret"></span></button>
                        <ul class="dropdown-menu pull-right" role="menu">
                            <li class="dropdown-header"><span class="text-info"><i class="fa fa-check-square-o"></i> Use the checkboxes to the left of each<br>supporter to batch process multiple supporters.</span></li>

                            <li class="divider"></li>
                            <li class="dropdown-header">Mark as...</li>
                            <li><a onclick="_batchSelected('archived');"><i class="fa-solid fa-inbox-in"></i> Archive</a></li>
                            <li><a onclick="_batchSelected('spam');"><i class="fa-regular fa-bug"></i> Spam</a></li>
                        </ul>
                    </div>

                    <div class="btn-group">
                        <button title="Export account data." type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-download fa-fw"></i><span class="hidden-xs hidden-sm"> Export</span> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu pull-right">
                            <li><a onclick="exportRecordsEmail(); return false;">Export Emails Only</a></li>
                            <li><a onclick="exportRecordsCsv(); return false;">Export All Data</a></li>
                        </ul>
                    </div>
                </div>
            </h1>
        </div>
    </div>

    <div class="row">
        <form class="datatable-filters">

            <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-search"></i></div>
                        <input type="text" class="form-control delay-filter" name="fB" id="fB" value="{{ request('fB') }}" placeholder="Search" data-placement="bottom" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter supporters by:<br><i class='fa fa-check'></i> Name<br><i class='fa fa-check'></i> Email<br><i class='fa fa-check'></i> Phone<br>" />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Created Date</label>
                    <div class="input-group input-daterange">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                        <input type="text" class="form-control" name="fd1" value="{{ request('fd1') }}" placeholder="Created..." />
                        <span class="input-group-addon">to</span>
                        <input type="text" class="form-control" name="fd2" value="{{ request('fd2') }}" />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">First payment date</label>
                    <div class="input-group input-daterange">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                        <input type="text" class="form-control" name="firstPaymentAfter" value="<?= e(request('firstPaymentAfter')); ?>" placeholder="First payment..." />
                        <span class="input-group-addon">to</span>
                        <input type="text" class="form-control" name="firstPaymentBefore" value="<?= e(request('firstPaymentBefore')); ?>" />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Last payment date</label>
                    <div class="input-group input-daterange">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                        <input type="text" class="form-control" name="lastPaymentAfter" value="{{ request('lastPaymentAfter') }}" placeholder="Last payment..." />
                        <span class="input-group-addon">to</span>
                        <input type="text" class="form-control" name="lastPaymentBefore" value="{{ request('lastPaymentBefore') }}" />
                    </div>
                </div>

                @if (feature('membership'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">{{ sys_get('syn_groups') }}</label>
                    <select class="selectize form-control" name="membership_id" multiple placeholder="{{ sys_get('syn_groups') }}..." size="1">
                        @foreach (\Ds\Models\Membership::all() as $membership)
                        <option value="{{ $membership->id }}" {{ volt_selected($membership->id, explode(',', request('membership_id'))) }} >{{ $membership->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if (sys_get('referral_sources_isactive'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Referral Sources</label>
                    <select class="form-control selectize" name="fr" id="fr" placeholder="Referral Sources..." multiple size="1">
                        <option></option>
                        @foreach (explode(',', sys_get('referral_sources_options')) as $source)
                        <option value="{{ $source }}" {{ volt_selected($source, explode(',', request('fr'))) }}>{{ $source }}</option>
                        @endforeach
                        @if (sys_get('referral_sources_other'))
                        <option value="Other" {{ volt_selected('Other', explode(',', request('fr'))) }}>Other</option>
                        @endif
                    </select>
                </div>
                @endif

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Email Opt-in</label>
                    <select class="form-control selectize" name="fe" id="fe" placeholder="Email Opt-in..." multiple size="1">
                        <option></option>
                        <option value="1" {{ volt_selected('1', explode(',', request('fe'))) }}>Yes</option>
                        <option value="0" {{ volt_selected('0', explode(',', request('fe', '*'))) }}>No</option>
                    </select>
                </div>

               @if (feature('fundraising_pages'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Fundraisers</label>
                    <select class="form-control selectize auto-height" name="fundraisers" id="fundraisers" placeholder="Fundraisers" multiple size="1">
                        <option></option>
                        <option value="active" {{ volt_selected('active', explode(',', request('fundraisers'))) }}>Has an Active Fundraising Page</option>
                        <option value="closed" {{ volt_selected('closed', explode(',', request('fundraisers'))) }}>Had Active Fundraising Pages in the Past</option>
                        <option value="never" {{ volt_selected('never', explode(',', request('fundraisers'))) }}>Never Created a Fundraising Page</option>
                    </select>
                </div>
                @endif

                @if (feature('fundraising_forms'))
                    <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                        <label class="form-label">Fundraising Forms</label>
                        <select class="form-control selectize" name="donationForms" id="donationForms" placeholder="Any Fundraising Forms" multiple size="1">
                            <option>Any Fundraising form</option>
                            @foreach($donationForms as $form)
                                <option value="{{ $form->hashid }}"
                                    {{ volt_selected($form->hashid, explode(',', request('donationForms')))}}
                                >{{ $form->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (sys_get('fundraising_pages_requires_verify'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Verified Fundraising Supporters</label>
                    <select class="form-control" name="verified_status" placeholder="Verified Supporter">
                        <option value="">Any Status</option>
                        <option value="1">Verified</option>
                        <option value="0">Pending</option>
                        <option value="2">Unverified</option>
                        <option value="-1">Denied</option>
                    </select>
                </div>
                @endif

                @if(sys_get('nps_enabled'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Net Promoter Score</label>
                    <select class="form-control selectize" name="fn" id="fn" placeholder="Net Promoter Score...">
                        <option></option>
                        <option value=">9" {{ (request('fn') == '>9') ? 'selected' : '' }}>Promoter</option>
                        <option value="6:8" {{ (request('fn') == '6:8') ? 'selected' : '' }}>Passive</option>
                        <option value="<5" {{ (request('fn') == '<5') ? 'selected' : '' }}>Dectractor</option>
                    </select>
                </div>
                @endif

                @if (feature('givecloud_pro'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Supporter Types</label>
                    <select class="form-control selectize" name="ft" id="ft" placeholder="Supporter Types..." multiple size="1">
                        <option></option>
                        @foreach (\Ds\Models\AccountType::all() as $type)
                        <option value="{{ $type->id }}" {{ volt_selected($type->id, explode(',', request('ft')))}}>
                            {{ $type->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if (user()->can('recurringpaymentprofile'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Recurring Payment Profile</label>
                    <select class="form-control selectize" name="rpp" id="rpp" placeholder="Recurring Payment Profile..." size="1">
                        <option></option>
                        @foreach ($recurringPaymentProfileStatuses as $profile)
                        <option value="{{ $profile }}" {{ volt_selected(strtolower(request('rpp')), strtolower($profile))}}>{{ $profile }} Recurring Profile</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Supporter status</label>
                    <select class="form-control" name="fA" id="fA" placeholder="Supporter status" size="1">
                        @foreach (\Ds\Enums\Supporters\SupporterStatus::all() as $key => $label)
                        <option
                            value="{{ $key === \Ds\Enums\Supporters\SupporterStatus::ACTIVE ? '' : $key }}"
                            {{ volt_selected($key, request('fA', 1))}}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                @if (feature('messenger') && user()->can('messenger'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Used Text-to-Give</label>
                    <select class="form-control selectize" name="used_text_to_give" id="used_text_to_give" placeholder="Used Text-to-Give?" size="1">
                        <option></option>
                        <option value="1" {{ volt_selected('1', request('used_text_to_give')) }}>Has Used Text-to-Give</option>
                        <option value="0" {{ volt_selected('0', request('used_text_to_give')) }}>Never Used Text-to-Give</option>
                    </select>
                </div>
                @endif

                @if (feature('givecloud_pro'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Payment Method</label>
                    <select class="form-control selectize" name="payment_method" id="payment_method" placeholder="Payment Method" size="1">
                        <option></option>
                        <option value="valid" {{ volt_selected('valid', request('payment_method')) }}>Valid</option>
                        <option value="expiring" {{ volt_selected('expiring', request('payment_method')) }}>Expiring Soon</option>
                        <option value="expired" {{ volt_selected('expired', request('payment_method')) }}>Expired</option>
                        <option value="none" {{ volt_selected('none', request('payment_method')) }}>No Method</option>
                    </select>
                </div>
                @endif

                @if (feature('givecloud_pro'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Portal User</label>
                    <select class="form-control selectize" name="has_login" id="has_login" placeholder="Portal User?" size="1">
                        <option></option>
                        <option value="1" {{ volt_selected('1', request('has_login')) }}>Portal User</option>
                        <option value="0" {{ volt_selected('0', request('has_login')) }}>Not Portal User</option>
                    </select>
                </div>
                @endif

                @if (feature('givecloud_pro'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Portal User Activity</label>
                    <select class="form-control selectize" name="has_logged_in" id="has_logged_in" placeholder="Portal User Activity" size="1">
                        <option></option>
                        <option value="1" {{ volt_selected('1', request('has_logged_in')) }}>Has Logged in</option>
                        <option value="0" {{ volt_selected('0', request('has_logged_in')) }}>Has Not Logged in</option>
                    </select>
                </div>
                @endif

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Supporter Slipping</label>
                    <select class="form-control selectize" name="is_slipping" id="is_slipping" placeholder="Supporter Slipping?" size="1">
                        <option></option>
                        <option value="1" {{ volt_selected('1', request('is_slipping')) }}>Slipping</option>
                        <option value="0" {{ volt_selected('0', request('is_slipping')) }}>Not Slipping</option>
                    </select>
                </div>

                <div class="form-group pt-1 px-2">
                    <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
                </div>

            </div>
        </form>
    </div>

    <div class="mt-2 mb-24 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow bg-gray-50 ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table id="supporters" data-tailwinded class="min-w-full table-hover">
                        <thead class="bg-gray-50 border-b border-gray-300">
                        <tr>
                            <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                <input type="checkbox" value="1" name="selectedids_master" class="master absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 sm:left-6">
                            </th>
                            <th scope="col" class="py-3.5 pr-3 text-left text-sm font-semibold text-gray-900 pl-6">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Source</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Recurring</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">NPS</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Has Login</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Payments</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created at</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Edit</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 border-b border-gray-200">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window._batchSelected = function (action) {
            var ids = j.ui.datatable.values('#supporters');
            if (ids.length === 0) {
                return $.alert('<strong>You have no supporters selected.</strong><br><br>Use the checkboxes on the left side of the table to select supporters to batch process.', 'danger', 'fa-exclamation-triangle');
            }

            $.confirm('Are you sure you want to mark <span class="badge">'+ids.length+'</span> supporter(s) as '+action+'?', function () {
                window.location = '{{ route('backend.member.batch') }}?' + $.param({'action':action,'ids':ids.join(',')});
            }, 'warning', 'fa-question-circle');
        }
    </script>
@endsection
