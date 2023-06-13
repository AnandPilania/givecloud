
@extends('layouts.app')
@section('title', $title)

@section('content')

    <script>
        function onDelete () {
            var f = confirm('Are you sure you want to delete this Promotion?');
            if (f) {
                document.posting.action = '/jpanel/promotions/destroy';
                document.posting.submit();
            }
        }
    </script>

    <form name="posting" id="promo-code-form" method="post" action="/jpanel/promotions/save">
        @csrf
        <input type="hidden" name="id" value="{{ $promocode->id }}" />

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">
                    <span class="page-header-text">{{ $pageTitle }}</span>

                    <div class="pull-right">
                        @if($promocode->exists)
                            <a href="#duplicate-promo-modal" data-toggle="modal" class="btn btn-default" title="Duplicate Promo Code"><i class="fa fa-copy"></i></a>
                        @endif
                        <button class="btn btn-success" type="submit"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
                        @if($promocode->exists)
                            <a onclick="onDelete();" class="btn btn-danger"><i class="fa fa-trash fa-fw"></i></a>
                            <a href="{{ route('backend.orders.index', ['promo' => $promocode->code]) }}" class="btn btn-info"><i class="fa fa-bar-chart-o"></i><span class="hidden-sm hidden-xs"> Sales</span></a>
                        @endif
                    </div>
                </h1>
            </div>
        </div>

        @if($errors->first('new_code'))
            <div class="alert alert-danger">
                <i class="fa fa-times"></i> {{ $errors->first('new_code') }}
            </div>
        @endif

        {{ app('flash')->output() }}

        <?php if($promocode->exists && $promocode->memberships->count() > 0): ?>
            <div class="alert alert-warning">
                <i class="fa fa-lock"></i> This promo code is locked to membership levels: {{ $promocode->memberships->pluck('name')->implode(', ') }}.
            </div>
        <?php endif; ?>

        <div class="panel panel-default">
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-pencil"></i> General</div>
                    <div class="panel-sub-desc">
                        Identify the name and description of this promotion code. Optionally, you can supply a valid start and end date for the promo.
                    </div>
                </div>

                <div class="form-horizontal">

                    <div class="form-group">
                        <label for="code" class="col-sm-3 control-label">Code</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="code" id="code" value="{{ $promocode->code }}" maxlength="15" required />
                            <small class="text-muted">The code that must be entered in order to apply this discount.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" style="height:70px;" name="description" id="description" >{{ $promocode->description }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="" class="col-sm-3 control-label">Usage Limits</label>
                        <div class="col-sm-9 form-inline">
                            <select id="" name="total_limit" class="form-control" style="padding-right:20px;">
                                <option value="unlimited">Unlimited Total Uses</option>
                                <option value="limit" @if ($promocode->usage_limit) selected @endif >Limited Uses</option>
                            </select>

                            <div class="input-group hide">
                                <input type="numeric" class="text-right form-control" style="width:70px;" name="usage_limit" value="{{ $promocode->usage_limit }}">
                                <div class="input-group-addon">
                                    uses
                                </div>
                            </div>

                            @if ($promocode->exists)
                                <div>
                                    <small>Used <a href="{{ route('backend.orders.index', ['promo' => $promocode->code]) }}" class="btn btn-info btn-xs"><span>{{ $promocode->usage_count }}</span></a> times. <a href="{{ route('backend.promotions.calculate_usage', [$promocode]) }}">Recalculate</a></small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="" class="col-sm-3 control-label">&nbsp;</label>
                        <div class="col-sm-9 form-inline">
                            <select id="" name="account_limit" class="form-control" style="padding-right:20px;">
                                <option value="unlimited">Unlimited Uses Per Person</option>
                                <option value="limit" @if ($promocode->usage_limit_per_account) selected="selected" @endif >Limit Per Supporter/Email</option>
                            </select>

                            <div class="input-group hide">
                                <input type="numeric" class="text-right form-control" style="width:70px;" name="usage_limit_per_account" value="{{ $promocode->usage_limit_per_account }}">
                                <div class="input-group-addon">
                                    per Supporter/Email
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="startdate" class="col-sm-3 control-label">Active Dates</label>
                        <div class="col-sm-4 col-lg-3">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                <input type="text" class="form-control date" name="startdate" id="startdate" value="{{ $promocode->startdate == null ? '' : toLocalFormat($promocode->startdate, 'Y-m-d') }}" />
                            </div>
                        </div>
                        <div class="col-sm-1 col-lg-1">
                            <div class="form-control-static text-center">to</div>
                        </div>
                        <div class="col-sm-4 col-lg-3">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                <input type="text" class="form-control date" name="enddate" id="enddate" value="{{ $promocode->enddate == null ? '' : toLocalFormat($promocode->enddate, 'Y-m-d') }}" />
                            </div>
                        </div>
                        <div class="col-sm-3">&nbsp;</div>
                        <div class="col-sm-9">
                            <small class="text-muted">Optionally choose a start and end date for this promotion. If you supply a start and end date, this promotion can only be used within this window of time.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="default_promo_code" class="col-sm-3 control-label"><i class="fa fa-lock"></i> Membership(s)</label>
                        <div class="col-sm-9">
                            <select id="membership_ids" name="membership_ids[]" class="form-control selectize" multiple placeholder="Choose membership(s)...">
                                <option value=""></option>
                                @foreach ($allMemberships as $membership)
                                    <option value="{{ $membership->id }}" @if($promocode->memberships && in_array($membership->id, $promocode->memberships->pluck('id')->toArray())) selected="selected" @endif>{{ $membership->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Optionally restrict this promocode to a membership level.</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-money"></i> Discount</div>
                    <div class="panel-sub-desc">
                        Select the type of discount and the amount of the discount.
                    </div>
                </div>

                <div class="form-horizontal">

                    <div class="form-group">
                        <label for="code" class="col-sm-3 control-label">Discount</label>
                        <div class="col-sm-4">

                            <div class="radio">
                                <label>
                                    <input type="radio" name="discount_type" value="percent" @if($promocode->discount_type == 'percent') checked="checked" @endif > <i class="fa fa-percent"></i> Percent Discount
                                </label><br>
                                <small class="text-muted">X% off the regular price of an item.</small>
                            </div>

                            <div class="radio">
                                <label>
                                    <input type="radio" name="discount_type" value="dollar" @if($promocode->discount_type == 'dollar') checked="checked" @endif > <i class="fa fa-dollar"></i> {{ currency()->name }} Discount
                                </label><br>
                                <small class="text-muted">{{ currency()->symbol}}X off the regular price of an item.</small>
                            </div>

                            <div class="radio">
                                <label>
                                    <input type="radio" name="discount_type" value="bxgy_dollar" @if($promocode->discount_type == 'bxgy_dollar') checked="checked" @endif > Buy X Get <i class="fa fa-dollar"></i> {{ currency()->name }} Discount
                                </label><br>
                                <small class="text-muted">Buy X Get {{ currency()->symbol}}Y off the entire purchase.</small>
                            </div>

                            <div class="radio">
                                <label>
                                    <input type="radio" name="discount_type" value="bxgy_percent" @if($promocode->discount_type == 'bxgy_percent') checked="checked" @endif > Buy X Get <i class="fa fa-percent"></i> Percent Discount
                                </label><br>
                                <small class="text-muted">Buy X Get Y% off the entire purchase.</small>
                            </div>

                        </div>
                    </div>

                    <div class="form-group bxgy-only">
                        <label for="buy_quantity" class="col-sm-3 control-label">Customer Buys</label>
                        <div class="col-sm-4">
                            <input type="number" class="form-control" name="buy_quantity" id="buy_quantity" value="{{ $promocode->buy_quantity }}" style="width:120px">
                            <small class="text-muted">Buy X quantity.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="code" class="col-sm-3 control-label">Savings</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="text" class="form-control text-right" name="discount" id="discount" value="{{ $promocode->discount }}" maxlength="5" />
                                <div class="input-group-addon discount-desc">{{ currency()->symbol }} off each item</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group bxgy-only">
                        <label for="allocation_limit" class="col-sm-3 control-label">Limit</label>
                        <div class="col-sm-4">
                            <input type="number" class="form-control" name="allocation_limit" id="allocation_limit" value="{{ $promocode->allocation_limit }}" style="width:220px" />
                            <small class="text-muted">Maximum number of uses per purchase.</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-truck"></i> Free Shipping</div>
                    <div class="panel-sub-desc">
                        Allow this promocode to eliminate any shipping charges on the purchase it's applied to.<br><span class="text-info"><strong><i class="fa fa-info-circle"></i> Note - </strong>Free shipping will be applied to the entire purchase regardless of the items in the purchase.</span>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        <label for="is_free_shipping" class="col-sm-3 control-label">Free Shipping</label>
                        <div class="col-sm-4">
                            <input type="checkbox" class="switch" value="1" name="is_free_shipping" @if($promocode->is_free_shipping)  checked="checked" @endif onchange="if ($(this).is(':checked')) $('.free-shipping-only').removeClass('hide'); else $('.free-shipping-only').addClass('hide');">
                        </div>
                    </div>

                    <div class="form-group free-shipping-only @if(!$promocode->is_free_shipping) hide @endif">
                        <label for="free_shipping_label" class="col-sm-3 control-label">Free Shipping Description</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="free_shipping_label" id="free_shipping_label" value="{{ $promocode->free_shipping_label }}" placeholder="Free Shipping" />
                            <small class="text-muted">Optionally provide a description for the free shipping.  Ex: "Free Standard Shipping" or "Free UPS Shipping"</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-tags"></i> Applies To...</div>
                    <div class="panel-sub-desc">
                        Choose which products and/or categories this promotion applies to.
                    </div>
                </div>

                <div class="form-horizontal">

                    <div class="form-group">
                        <label for="categoryids" class="col-sm-3 control-label">
                            Categories<br />
                            <a href="javascript:void(0);" class="btn btn-info btn-xs" onclick="$('#categoryids option').attr('selected','selected');">All</a> <a href="javascript:void(0);" class="btn btn-info btn-xs" onclick="$('#categoryids')[0].selectedIndex = -1;">None</a>
                        </label>
                        <div class="col-sm-9">
                            <select multiple="multiple" id="categoryids" name="categoryids[]" size="10" class="form-control">
                                @foreach ($allCategories as $category)
                                    <option value="{{ $category->id }}" @if($promocode->categories && in_array($category->id, $promocode->categories->pluck('id')->toArray())) selected="selected" @endif>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <small>Hold CTRL (Command on Mac) key to select multiple options.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="code" class="col-sm-3 control-label">
                            Products<br />
                            <a href="javascript:void(0);" class="btn btn-info btn-xs" onclick="$('#productids option').attr('selected','selected');">All</a> <a href="javascript:void(0);" class="btn btn-info btn-xs" onclick="$('#productids')[0].selectedIndex = -1;">None</a>
                        </label>
                        <div class="col-sm-9">
                            <select multiple="multiple" id="productids" name="productids[]" size="10" class="form-control">
                                @foreach ($allProducts as $product)
                                    <option value="{{ $product->id }}" {{ volt_selected($product->id, $selectedProductIds) }}>{{ $product->name }}@if($product->author), {{ $product->author }}@endif @if($product->code)({{ $product->code }})@endif</option>
                                @endforeach
                            </select>
                            <small>Hold CTRL (Command on Mac) key to select multiple options.</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </form>

    <script>
        spaContentReady(function() {
            onUsageOptionChange = function(ev){
                var total_limit  = $("select[name=total_limit]").val(),
                    account_limit = $("select[name=account_limit]").val(),
                    $usage_limit = $("input[name=usage_limit]"),
                    $usage_limit_per_account = $("input[name=usage_limit_per_account]");

                if (total_limit == 'unlimited') {
                    $usage_limit.parent().addClass('hide');
                    $usage_limit.prop('required',false);
                    $usage_limit.val('');
                } else if (total_limit == 'limit') {
                    $usage_limit.parent().removeClass('hide');
                    $usage_limit.prop('required',true);
                    if (!$usage_limit.val()) {
                        $usage_limit.val(1);
                    }
                    $usage_limit.focus();
                }

                if (account_limit == 'unlimited') {
                    $usage_limit_per_account.parent().addClass('hide');
                    $usage_limit_per_account.prop('required',false);
                    $usage_limit_per_account.val('');
                } else if (account_limit == 'limit') {
                    $usage_limit_per_account.parent().removeClass('hide');
                    $usage_limit_per_account.prop('required',true);
                    if (!$usage_limit_per_account.val()) {
                        $usage_limit_per_account.val(1);
                    }
                    $usage_limit_per_account.focus();
                }
            };
            $("select[name=account_limit], select[name=total_limit]").bind('change', onUsageOptionChange);
            onUsageOptionChange();
        });
    </script>

    @if($promocode->exists)

        <small class="text-muted">
            Created by {{ $promocode->createdBy->full_name }} on {{ $promocode->createddatetime }} ({{ $promocode->createddatetime->diffForHumans() }}).
            @if($promocode->updatedBy)
                <br />Last modified by {{ $promocode->updatedBy->full_name }} on {{ $promocode->modifieddatetime }} ({{ $promocode->modifieddatetime->diffForHumans()}}).
            @endif
        </small>

        <div class="modal fade modal-info" id="duplicate-promo-modal" tabindex="-1" role="dialog" aria-labelledby="duplicate-promo-modal-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="duplicate-promo-modal-label">Duplicate Promotion</h4>
                    </div>
                    <form action="{{ route('backend.promotions.duplicate', [$promocode]) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <p class="mb-4">Enter the new promo code you would like to use.</p>

                            <div class="form-group">
                                <label for="new_code" class="control-label">New Promotional Code</label>
                                <input type="text" class="form-control" id="new_code" name="new_code" value="" placeholder="15OFF" maxlength="15" required>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-info">Duplicate</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection
