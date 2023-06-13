@extends('layouts.app')

@section('title', sys_get('syn_sponsorship_child') . ' Sponsorship')

@section('content')

    @inject('flash', 'flash')

    {{ $flash->output() }}

    <script>
        exportRecords = function () {
            var d = j.ui.datatable.filterValues('table.dataTable');
            window.location = '/jpanel/sponsorship.csv?' + $.param(d);
        }
    </script>

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header clearfix">
                {{ sys_get('syn_sponsorship_child') . ' Sponsorship' }}
                <div class="visible-xs-block"></div>

                <div class="pull-right">
                    @if(user()->can('sponsorship.add'))
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                                <span class="hidden-xs hidden-sm"> Add</span> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right">
                                <li><a href="/jpanel/sponsorship/add"><span class="hidden-xs hidden-sm"> Add a {{ sys_get('syn_sponsorship_child') }}</span></a></li>
                                @if(feature('flatfile_sponsorships_imports'))
                                    <li><a href="#" id="flatFileImport" data-refresh="{{ route('flatfile.token.sponsorships') }}" data-token="{{ $flatfileToken }}" data-custom-fields="{{ json_encode($customFields) }}" onclick="j.importer('{{ $flatfileToken }}'); return false;">Import {{ sys_get('syn_sponsorship_children') }}</a></li>
                                    <li><a href="{{ route('backend.import.template.download', 'Sponsorships') }}">Download Import Template</a></li>
                                @endif
                            </ul>
                        </div>

                    @endif

                    <a onclick="exportRecords(); return false;" class="btn btn-default"><i class="fa fa-download fa-fw"></i><span class="hidden-xs hidden-sm"> Export</span></a>
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
                        <input type="text" class="form-control delay-filter" name="search" value="" placeholder="Search..." data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter sponsorship records by:<br><i class='fa fa-check'></i> First &amp; Last Name<br><i class='fa fa-check'></i> Reference Number<br>" />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Reference</label>
                    <input type="text" class="form-control delay-filter" name="search_ref_num" value="" placeholder="Ref#..."/>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Birth Date</label>
                    <div class="input-group input-daterange">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                        <input type="text" class="form-control" name="birth_date_start" value="" placeholder="Birth Date..." />
                        <span class="input-group-addon">to</span>
                        <input type="text" class="form-control" name="birth_date_end" value="" />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Gender</label>
                    <select class="form-control selectize" name="gender">
                        <option value="">Gender...</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Sponsor Count</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="sponsor_count_start" value="" placeholder="Sponsor Count..." />
                        <span class="input-group-addon">to</span>
                        <input type="text" class="form-control" name="sponsor_count_end" value="" />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">On Web</label>
                    <select class="form-control selectize" name="is_enabled">
                        <option value="">On Web...</option>
                        <option value="1">Live on Web</option>
                        <option value="0">Hidden from Web</option>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Sponsored</label>
                    <select class="form-control selectize" name="is_sponsored">
                        <option value="">Sponsored...</option>
                        <option value="1">Sponsored</option>
                        <option value="0">Not Sponsored</option>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Payment Option</label>
                    <select class="form-control selectize" name="payment_option_group_id">
                        <option value="">Payment Option...</option>
                        <option value="0">[None]</option>
                        @foreach(\Ds\Domain\Sponsorship\Models\PaymentOptionGroup::all() as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if(user()->can('sponsor.mature'))
                    <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                        <label class="form-label">Maturity</label>
                        <select class="form-control selectize" name="is_mature">
                            <option value="">Maturity...</option>
                            <option value="1" @selected(request('is_mature') === 1)>Matured</option>
                            <option value="0" @selected(request('is_mature') === 0)>Not Matured</option>
                        </select>
                    </div>
                @endif

                @foreach(\Ds\Domain\Sponsorship\Models\Segment::with('items')->get() as $segment)
                    <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                        <label class="form-label">{{ $segment->name }}</label>
                        @if ($segment->type === 'multi-select' || $segment->type === 'advanced-multi-select')
                            <select name="segment_filters[{{ $segment->id }}][]" class="selectize form-control" multiple="multiple" size="1" placeholder="{{ $segment->name }}...">
                                <option></option>
                                @foreach($segment->items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name}}</option>
                                @endforeach
                            </select>
                        @elseif($segment->type === 'date')
                            <div class="input-group input-daterange">
                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                <input type="text" class="form-control" name="segment_filters[{{ $segment->id }}][]" value="" placeholder="{{ $segment->name}}..." />
                                <span class="input-group-addon">to</span>
                                <input type="text" class="form-control" name="segment_filters[{{ $segment->id }}][]" value="" />
                            </div>
                        @else
                            <input class="form-control delay-filter" name="segment_filters[{{ $segment->id }}]" placeholder="{{ $segment->name}}...">
                        @endif
                    </div>
                @endforeach

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Enrollment Date</label>
                    <div class="input-group input-daterange">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                        <input type="text" class="form-control" name="enrollment_date_start" value="" placeholder="Start Enrollment Date..." />
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                        <input type="text" class="form-control" name="enrollment_date_end" value="" placeholder="End Enrollment Date..." />
                    </div>

                </div>

                <div class="form-group pt-1 px-2">
                    <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table id="sponsorship-list" class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="16"></th>
                            <th style="width:120px;">Ref#</th>
                            <th>Name <small>Last, First</small></th>
                            <th>Gender</th>
                            <th>Birth Date</th>
                            <th style="text-align:center; width:60px;">Age</th>
                            <th style="text-align:center; width:60px;">Sponsors</th>
                            <th style="text-align:center; width:90px;">Sponsored</th>
                            <th style="text-align:center; width:90px;">On Web</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
