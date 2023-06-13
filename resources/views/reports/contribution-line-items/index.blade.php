@extends('layouts.app')
@section('title', $pageTitle)

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Contribution Line Items

                <div class="pull-right">
                    <a href="#" class="datatable-export btn btn-default"><i class="fa fa-fw fa-download"></i> Export</a>
                </div>
            </h1>
        </div>
    </div>

    @inject('flash', 'flash')

    {{ $flash->output() }}

    <div class="row">
        <form class="datatable-filters">
            <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-search"></i></div>
                        <input type="text" class="form-control" name="search" id="search" value="{{ request('search') }}" placeholder="Search"
                               data-placement="top" data-toggle="popover" data-trigger="focus"
                               data-content="Use <i class='fa fa-search'></i> Search to filter payments by:
                                <br><i class='fa fa-check'></i> Order number
                                <br><i class='fa fa-check'></i> Transaction name
                                <br><i class='fa fa-check'></i> Supporter name
                                <br><i class='fa fa-check'></i> Line item description.
                            " />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                    <label class="form-label">Captured</label>
                    <div class="input-group input-daterange">
                        <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                        <input type="text" class="form-control" name="captured_after" value="{{ request('captured_after') }}" placeholder="Captured on..." />
                        <span class="input-group-addon">to</span>
                        <input type="text" class="form-control" name="captured_before" value="{{ request('captured_before') }}" />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                    <label class="form-label">Line Item Type</label>
                    <select class="form-control selectize" placeholder="Line item type" name="line_item_type" multiple>
                        <option></option>
                        @foreach (\Ds\Enums\LedgerEntryType::all() as $item)
                            <option value="{{ $item }}" {{ volt_selected($item, explode(',', request('line_item_type'))) }}>{{ \Ds\Enums\LedgerEntryType::labels()[$item]}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Related to Items</label>
                    <select id="items" class="form-control selectize togglesInfoBox" size="1" placeholder="Items" name="items" multiple>
                        <option value="*" {{ volt_selected('*', request('items'))}}>Any</option>
                        <option disabled>------</option>
                        @foreach ($items as $item)
                            <option value="{{ $item['id'] }}" {{ volt_selected($item['id'], explode(',', request('items'))) }}>{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                @if (feature('fundraising_forms'))
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Related to Fundraising Forms</label>
                    <select class="form-control selectize" multiple name="fundraising_forms" size="1" id="fundraising_forms" placeholder="Any Fundraising Form">
                        <option value="">Any Fundraising Form</option>
                        @foreach ($donation_forms as $form)
                        <option value="{{ $form->hashid }}" @selected(in_array($form->hashid, explode(',', request('fundraising_forms'))))> {{ $form->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if (feature('membership'))
                    <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                        <label class="form-label">Related to {{ sys_get('syn_group') }}</label>
                        <select class="selectize form-control togglesInfoBox" name="membership" placeholder="Any {{ sys_get('syn_group') }}..." multiple>
                            <option value="*" {{ volt_selected('*', request('membership')) }}>Any</option>
                            <option disabled>------</option>
                            @foreach ($memberships as $membership)
                                <option value="{{ $membership->id }}" {{ volt_selected($membership->id, request('membership')) }}>{{ $membership->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (sys_get('gift_aid'))
                    <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                        <label class="form-label">Gift Aid Status</label>
                        <select class="form-control" name="gift_aid">
                            <option value="">Any Gift Aid Status</option>
                            <option {{ volt_selected('1', request('gift_aid')) }} value="1">Gift Aid eligible</option>
                            <option {{ volt_selected('0', request('gift_aid')) }} value="0">Gift Aid ineligible</option>
                        </select>
                    </div>
                @endif

                @if (feature('sponsorship'))
                    <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                        <label class="form-label">Related to Sponsorships</label>
                        <select class="form-control selectize togglesInfoBox" placeholder="Sponsorships" name="sponsorship" multiple="multiple">
                            <option value="*" {{ volt_selected('*', request('sponsorship')) }}>Any</option>
                            <option disabled>------</option>
                            @foreach ($sponsorships as $sponsorship)
                                <option value="{{ $sponsorship->id}}" {{ volt_selected($sponsorship->id, request('sponsorship'))}}>
                                    {{ $sponsorship->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Billing Country</label>
                    <select class="form-control" name="billing_country">
                        <option value>Any Billing Country</option>
                        @foreach ($billingCountries as $country)
                            <option value="{{ $country['code'] }}" {{ volt_selected($country['code'], request('billing_country')) }}>
                                {{ $country['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">IP Country</label>
                    <select class="form-control" name="ip_country">
                        <option value>Any IP Country</option>
                        @foreach ($ipCountries as $country)
                            <option value="{{ $country['code'] }}" {{ volt_selected($country['code'], request('ip_country')) }}>
                                {{ $country['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Categories</label>
                    <select id="categories" size="1" multiple class="locally-selectize form-control" placeholder="Choose Category(s)..." name="categories">
                        @foreach ($categories as $category)
                            @include('components.filters.categories', $category)
                        @endforeach
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Gateway</label>
                    <select class="form-control" name="gateway">
                        <option value>Any Gateway</option>
                        @foreach ($gateways as $gateway)
                            <option value="{{ $gateway }}" {{ volt_selected($gateway, request('gateway')) }}>{{ ucfirst($gateway) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Supporter Type</label>
                    <select class="selectize form-control" name="account_type" placeholder="Any Supporter Type...">
                        <option></option>
                        @foreach ($account_types as $account_type)
                            <option value="{{ $account_type->id }}" {{ volt_selected($account_type->id, request('account_type')) }}>
                                {{ $account_type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>



                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Payment method</label>
                    <select class="form-control selectize" size="1" placeholder="Payment method" name="payment_method">
                        <option></option>
                        @foreach (\Ds\Enums\PaymentType::all() as $item)
                            <option value="{{ $item }}" {{ volt_selected($item, request('payment_method'))}}>{{ ucfirst($item) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">One-Time & Recurring</label>
                    <select class="form-control selectize" name="recurring">
                        <option value="">One-Time &amp; Recurring</option>
                        <option value="onetime" {{ volt_selected('onetime', request('recurring')) }}>One-Time Only</option>
                        <option value="recurring" {{ volt_selected('recurring', request('recurring')) }}>Recurring Only</option>
                    </select>
                </div>

                @foreach ($segments as $segment)
                    <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                        <label class="form-label">{{ $segment->name }}</label>
                        @if (in_array($segment->type, ['text', 'date']))
                            <input type="text" class="form-control" name="segment[{{ $segment->id }}]"
                                   value="{{ ! empty(request('segment')[$segment->id]) ? request('segment')[$segment->id] : '' }}"
                                   placeholder="Sponsorship Custom Field : {{ $segment->name }}" />
                        @else
                            <select class="selectize form-control" name="segment[{{ $segment->id }}]" placeholder="Sponsorship Custom Field : {{ $segment->name }}">
                                <option></option>
                                @foreach ($segment->items as $item)
                                    <option value="{{ $item->id }}" {{  ! empty(request('segment')[$segment->id]) ? volt_selected($item->id, request('segment')[$segment->id]) : '' }} >{{ $item->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                @endforeach

                <div class="form-group pt-1 px-2">
                    <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
                </div>

            </div>
        </form>
    </div>

    <div id="showInfoBox" class="rounded-md bg-blue-50 p-4 mb-4 hide">
        <div class="flex">
            <div class="shrink-0">
                <!-- Heroicon name: solid/information-circle -->
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1 md:flex md:justify-between">
                <p class="text-sm text-blue-500">
                    Tax and shipping line items relate to the whole contribution and will not show up when filtering for specific items.
                </p>
            </div>
        </div>
    </div>

    <div class="mt-2 mb-24 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow bg-gray-50 ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table id="contributions-line-items" data-tailwinded class="condensed min-w-full table-hover">
                        <thead class="bg-gray-50 border-b border-gray-300">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 ">Captured At</th>
                                <th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Supporter</th>
                                <th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Reference</th>
                                <th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                                <th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>
                                <th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">GL Account</th>
                                <th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Qty</th>
                                <th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Line Item Amount</th>
                                <th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Payment Amount</th>
                                <th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Currency</th>
                                <th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Method</th>
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
        function toggleInfoBox() {
            const $infoBox = $('#showInfoBox');
            let shouldShow = false;

            $('.selectized.togglesInfoBox').each(function() {
                if($(this).data('selectize').getValue().length > 0) {
                    shouldShow = true;
                }
            })
            if (shouldShow) {
                return $infoBox.removeClass('hide');
            }

            $infoBox.addClass('hide');
        }

        spaContentReady(function() {
            toggleInfoBox();

            $('.selectized.togglesInfoBox').each(function() {
               $(this).data('selectize').on('change', function(e) {
                   toggleInfoBox();
               });
            });

            $('select#categories').selectize({
                persist : true,
                plugins: ['remove_button'],
                onChange: function (values) {
                    // ensure that an empty value is passed through when nothing is selected
                    if (values.length === 0 && ! this.$input.data('allow-empty')) {
                        this.$input.append($('<option selected="selected" value=""></option>'))
                    }
                },
                render : {
                    item : function (item, escape) {
                        return '<div class="item">' + item.text.trim() + '</div>';
                    }
                }
            });

            var details_table = $('#contributions-line-items').DataTable({
                dom: 'rtpi',
                sErrMode: 'throw',
                iDisplayLength : 50,
                autoWidth: false,
                processing: true,
                serverSide: true,
                order: [[ 0, "desc" ]],
                columnDefs: [
                    { orderable: true, targets: 0, class : "text-left" },
                    { orderable: true, targets: 1, class : "text-left" },
                    { orderable: true, targets: 2, class : "text-left" },
                    { orderable: true, targets: 3, class : "text-left" },
                    { orderable: false, targets: 4, class : "text-left" },
                    { orderable: true, targets: 5, class : "text-left" },
                    { orderable: true, targets: 6, class : "text-center" },
                    { orderable: true, targets: 7, class : "text-left" },
                    { orderable: true, targets: 8, class : "text-left" },
                    { orderable: false, targets: 9, class : "text-left" },
                    { orderable: false, targets: 10, class : "text-left" },
                ],
                stateSave: false,
                ajax: {
                    url: "{{ route('backend.reports.contribution-line-items.listing') }}",
                    type: "POST",
                    data: function (d) {
                        var filters = {};
                        _.forEach($('.datatable-filters').serializeArray(), function(field) {
                            filters[field.name] = filters[field.name] ? filters[field.name] + ',' + field.value : field.value;
                        });
                        _.forEach(filters, function(value, key) {
                            d[key] = value;
                        });

                        j.filtersToQueryString(filters);
                    }
                },
                // colors/styles
                fnRowCallback: function( nRow, aData ) {
                    return nRow;
                },
                drawCallback : function(){
                    j.ui.datatable.formatRows($('#payments-details'));
                    return true;
                },
                initComplete : function(){
                    j.ui.datatable.formatTable($('#payments-details'));
                }
            });

            $('.datatable-filters input, .datatable-filters select').each(function(i, input){

                if ($(input).data('datepicker')){
                    $(input).on('clearDate changeDate', function () {
                        details_table.draw();
                    });
                }
                else
                    $(input).change(function(){
                        details_table.draw();
                    });
            });

            $('form.datatable-filters').on('submit', function(ev){
                ev.preventDefault();
            });

            $('.datatable-export').on('click', function(ev){
                ev.preventDefault();

                var data = j.ui.datatable.filterValues('#contributions-line-items');
                window.location = '{{ route('backend.reports.contribution-line-items.export') }}?'+$.param(data);
            });

            j.ui.datatable.enableFilters(details_table);
        });

    </script>
@endsection
