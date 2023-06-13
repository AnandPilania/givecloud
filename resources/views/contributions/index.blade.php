@extends('layouts.app')
@section('title', 'Contributions')

@section('content')
    @inject('flash', 'flash')
    {{ $flash->output() }}

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header clearfix">
                Contributions
                <div class="visible-xs-block"></div>

                <div class="pull-right">
                    @if(user()->can('order.add'))
                        <div class="btn-group">
                            <button title="Add" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-plus fa-fw"></i>
                                <span class="hidden-xs hidden-sm"> Add</span> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right">
                                <li><a href="/jpanel/pos">Add a contribution</a></li>
                                @if(feature('flatfile_contributions_imports'))
                                    <li><a href="#" id="flatFileImport" onclick="j.importer('{{ $flatfileToken }}'); return false;">Import Contributions</a></li>
                                    <li><a href="{{ route('backend.import.template.download', 'Contributions') }}">Download Import Template</a></li>
                                @endif
                            </ul>

                        </div>

                    @endif

                    @if (feature('givecloud_pro'))
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-outline dropdown-toggle bg-white" data-toggle="dropdown"><i class="fa fa-list-ul fa-fw"></i> Bulk... <span class="badge checkbox-counter"></span> <span class="caret"></span></button>
                        <ul class="dropdown-menu pull-right" role="menu">

                            <li class="dropdown-header"><span class="text-info"><i class="fa fa-check-square-o"></i> Use the checkboxes to the left of each<br>item to batch process multiple items.</span></li>

                            @php($canFulfillement = (sys_get('use_fulfillement') !== 'never') && user()->can(['order.edit','order.fullfill']))
                            @php($canRefundContribution = user()->can(['order.refund']))

                            @if($canFulfillement || $canRefundContribution)
                            <li class="divider"></li>
                            <li class="dropdown-header">Mark as...</li>

                                @if($canFulfillement)
                                <li><a onclick="_batchSelected('complete');"><i class="fa fa-fw fa-check"></i> Fulfilled</a></li>
                                <li><a onclick="_batchSelected('incomplete');"><i class="fa fa-fw fa-times"></i> Unfulfilled</a></li>
                                @endif

                                @if($canRefundContribution)
                                <li><a onclick="_batchSelected('spam_and_refund');"><i class="fa-regular fa-bug"></i> Spam & Refund</a></li>
                                @endif

                                @if($canFulfillement)
                                <li class="divider"></li>
                                <li class="dropdown-header">Print...</li>
                                <li><a onclick="_exportSelected('{{ route('backend.orders.packing_slip') }}');"><i class="fa fa-fw fa-print"></i> Packing Slips</span></a></li>
                                @endif

                            @endif
                           </ul>
                    </div>
                    @endif
                </div>
            </h1>
        </div>
    </div>

    @include('contributions._alerts.legacy-report')

    @if (request('c') !== '3' && $unsynced_count > 0 && dpo_is_enabled())
        @include('contributions._alerts.unsynced')
    @endif

    <div class="row">
        <form class="datatable-filters">
            <input type="hidden" name="fU" value="{{ request('fU') }}">

            <div class="datatable-filters-fields flex flex-wrap items-start -mx-2">
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-search"></i></div>
                        <input type="text" class="form-control" name="fO" id="fO" value="{{ request('fO') }}" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter contributions by:<br><i class='fa fa-check'></i> Contribution Number<br><i class='fa fa-check'></i> Bill-To name &amp; email<br><i class='fa fa-check'></i> Ship-To name &amp; email." />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                    <label class="form-label">Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                        <input type="text" data-date-range-picker autocomplete="off" class="form-control" name="fd0" value="{{ request('fd0') }}" placeholder="Date..." />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                    <label class="form-label">Contribution Type</label>
                    <select onchange="$('#withInitial').toggleClass('hide', this.value !== '2');" class="form-control selectize" name="fct" id="fct" placeholder="All Contributions">
                        <option value>All contributions</option>
                        <option value="1" @selected(request('fct') === '1')>One-Time only</option>
                        <option value="2" @selected(request('fct') === '2')>Recurring only</option>
                    </select>
                    <div id="withInitial" class="{{ request('fct') !== '2' ? 'hide' : '' }} mt-1 ml-0.5">
                        <input type="checkbox" name="fct-initial" value="1" />
                        Include initial contributions
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                    <label class="form-label">Status</label>
                    <select class="form-control selectize" name="c" id="c" placeholder="Any Contribution Status">
                        <option value="">All</option>
                        <option value="7" @selected(request('c') === '7')>Approved & Pending</option>
                        <option value="4" @selected(request('c') === '4')>Approved</option>
                        <option value="5" @selected(request('c') === '5')>Pending</option>
                        <option value="6" @selected(request('c') === '6')>Failed</option>
                        <option value="2" @selected(request('c') === '2')>Refunded</option>
                        @if (dpo_is_enabled())
                            <option value="3" @selected(request('c') === '3')>Unsynced</option>
                        @endif
                        @if (feature('givecloud_pro'))
                            <option value="1" @selected(request('c') === '1')>Fulfilled</option>
                            <option value="0" @selected(request('c') === '0')>Unfulfilled</option>
                        @endif

                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Risk & Spam</label>
                    <select class="form-control selectize" name="rsk" id="rsk" placeholder="Without Spam">
                        <option value="">All</option>
                        <option value="1" @selected(request('rsk') === '1')>Marked as Spam/Fraud</option>
                        <option value="2" @selected(request('rsk') === '2')>Risk Warning</option>
                    </select>
                </div>

                @if (feature('givecloud_pro'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Source</label>
                    <select class="form-control selectize" name="fs" id="fs" size="1" multiple placeholder="Any Source" data-allow-empty="true">
                        <option></option>
                        @foreach(collect(sys_get('list:pos_sources'))->merge(\Ds\Models\Order::getSystemSources())->sort() as $source)
                        <option value="{{ $source }}" {{ volt_selected($source, request('fs')) }}>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if (feature('givecloud_pro'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Item Types</label>
                    <select class="form-control selectize" size="1" multiple name="fit" id="fit" placeholder="Any Items">
                        <option value="">Any Items</option>
                        <option {{ volt_selected('f', request('fit')) }} value="f">Fundraising Items</option>
                        <option {{ volt_selected('s', request('fit')) }} value="s">Shippable Items</option>
                        <option {{ volt_selected('d', request('fit')) }} value="d">Downloadable Items</option>
                        <option {{ volt_selected('r', request('fit')) }} value="r">Recurring Items</option>
                        @if(feature('sponsorship'))
                            <option {{ volt_selected('sp', request('fit')) }} value="sp">Sponsorships</option>
                        @endif
                        <option {{ volt_selected('nf', request('fit')) }} value="nf">No Fundraising Items</option>
                        <option {{ volt_selected('ns', request('fit')) }} value="ns">No Shippable Items</option>
                        <option {{ volt_selected('nd', request('fit')) }} value="nd">No Downloadable Items</option>
                        <option {{ volt_selected('nr', request('fit')) }} value="nr">No Recurring Items</option>
                        @if(feature('sponsorship'))
                            <option {{ volt_selected('nsp', request('fit')) }} value="nsp">No Sponsorships</option>
                        @endif
                    </select>
                </div>
                @endif

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Payment Method</label>
                    <select class="form-control selectize" multiple name="fp" size="1" id="fp" placeholder="Any Payment Method">
                        <option></option>
                        @foreach(['Apple Pay', 'Google Pay', 'Visa','MasterCard','Discover','American Express','ACH','Secure Account','Check','Cash','PayPal','Other'] as $payment_type)
                        <option value="{{ $payment_type }}" {{ volt_selected($payment_type, request('fp')) }}>{{ $payment_type }}</option>
                        @endforeach
                    </select>
                </div>

                @if (sys_get('referral_sources_isactive'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Referral Source</label>
                    <select class="form-control selectize" multiple name="fR" id="fR" placeholder="Any Referral Source">
                        @foreach(explode(',',sys_get('referral_sources_options')) as $source)
                        <option value="{{ $source }}" {{ volt_selected($source, request('fR')) }}>{{ $source }}</option>
                        @endforeach
                        @if (sys_get('referral_sources_other'))
                        <option value="Other" {{ volt_selected('Other', request('fR')) }}>Other</option>
                        @endif
                    </select>
                </div>
                @endif

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Tracking Source</label>
                    <select class="form-control selectize-tag" placeholder="Any Tracking Source..." name="fots">
                        <option></option>
                        @foreach(\Ds\Models\Contribution::getDistinctValuesOf('tracking_source') as $g)
                            <option value="{{ $g }}" @selected(in_array($g, (array) request('fots')))>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>

                @if (feature('givecloud_pro'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Supporter Type</label>
                    <select class="selectize form-control" name="fat" multiple placeholder="Any Supporter Type..." size="1" data-allow-empty="true">
                        <option></option>
                        @foreach (\Ds\Models\AccountType::all() as $accountType)
                        <option value="{{ $accountType->id }}" {{ volt_selected($accountType->id, request('fat')) }}>{{ $accountType->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Billing Country</label>
                    <select class="selectize form-control" name="fc" placeholder="Billing Country..." size="1">
                        <option></option>
                        @foreach(\Ds\Models\Order::whereNotNull('confirmationdatetime')->whereNotNull('billingcountry')->select('billingcountry')->distinct()->orderBy('billingcountry')->pluck('billingcountry') as $country)
                            <option value="{{ $country }}">{{ cart_countries()[$country] ?? $country }}</option>
                        @endforeach
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
                <div class="overflow-hidden relative shadow bg-gray-50 ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table id="contributions" data-tailwinded class="min-w-full table-hover">
                        <thead class="bg-gray-50 border-b border-gray-300">
                        <tr>
                            <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                <input type="checkbox" value="1" name="selectedids_master" class="master absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 sm:left-6">
                            </th>
                            <th scope="col"></th>
                            <th scope="col" class="py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Supporter</th>
                            <th scope="col" class="py-3.5 pr-3 text-center text-sm font-semibold text-gray-900">
                                <span class="inline-flex items-center rounded-full bg-gray-50 p-0.5 text-xs font-medium text-gray-600"
                                      data-placement="bottom" data-toggle="popover" data-trigger="hover" data-content="Recurring">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                </span>
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Payment @if(isGivecloudExpress())(Fees)@endif</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Net Amount</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Status</span>
                            </th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">View</span>
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
        _exportAll = function (url) {
            ids = j.ui.datatable.values('#contributions');
            if (ids.length > 0) {
                return _exportSelected(url);
            }

            var d = j.ui.datatable.filterValues('table.dataTable');
            window.open(url + '?' + $.param(d), '_blank');
        }
        _exportSelected = function (url) {
            ids = j.ui.datatable.values('#contributions');
            if (ids.length === 0) {
                return $.alert('<strong>You have no items selected.</strong><br><br>Use the checkboxes on the left side of the table to select items to batch process.', 'danger', 'fa-exclamation-triangle');
            }

            window.open(url + '?' + $.param({'ids':ids.join(',')}), '_blank');
        }
        _batchSelected = function (action) {
            ids = j.ui.datatable.values('#contributions');
            if (ids.length === 0) {
                return $.alert('<strong>You have no items selected.</strong><br><br>Use the checkboxes on the left side of the table to select items to batch process.', 'danger', 'fa-exclamation-triangle');
            }

            var type = 'warning';
            var icon = 'fa-question-circle';
            var message = 'Are you sure you want to mark <span class="badge">'+ids.length+'</span> item(s) as '+action+'?'

            if (action === 'spam_and_refund') {
                type = 'danger';
                icon = 'fa-shield-xmark'
                message = 'Are you sure you want to refund and mark <span class="badge">'+ids.length+'</span> item(s) as spam? Any associated recurring profile will be cancelled and supports marked as spam.';
            }

            $.confirm(message, function () {
                window.location = '{{ route('backend.orders.batch') }}?' + $.param({'action':action,'ids':ids.join(',')});
            }, type, icon);
        }
    </script>
@endsection

