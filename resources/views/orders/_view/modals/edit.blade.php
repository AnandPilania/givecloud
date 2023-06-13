@if(!$order->trashed() && $order->userCan(['edit','fullfill']))
<div class="modal fade modal-info" id="edit-order-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Contribution</h4>
            </div>
            <form name="order" id="OrderForm" method="post" action="{{ route('backend.orders.update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $order->id }}" />

                <div class="modal-body">

                    <ul class="nav nav-tabs {{ feature('shipping') || feature('account_notes') ? '' : 'hide' }}" role="tablist">
                        <li role="presentation" class="active"><a href="#pos-bill-address-tab" aria-controls="pos-bill-address-tab" role="tab" data-toggle="tab"><i class="fa fa-envelope"></i> Billing Address</a></li>
                        @if (feature('shipping'))
                        <li role="presentation"><a href="#pos-ship-address-tab" aria-controls="pos-ship-address-tab" role="tab" data-toggle="tab"><i class="fa fa-truck"></i> Shipping Address</a></li>
                        @endif
                        @if (feature('account_notes'))
                           <li role="presentation"><a href="#pos-comments-tab" aria-controls="pos-comments-tab" role="tab" data-toggle="tab"><i class="fa fa-comments"></i> Notes</a></li>
                        @endif
                    </ul>

                    <div class="tab-content">
                        <br>

                        <div role="tabpanel" class="tab-pane active" id="pos-bill-address-tab">

                            <div class="row row-padding-sm">

                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12 {{  feature('givecloud_pro') ? '' : 'hide' }}">
                                    <label>Supporter Type</label>
                                    <select name="account_type_id" id="accounttypeid" class="form-control" {{ !$order->userCan('edit') ? 'disabled' : '' }}>
                                        @foreach (\Ds\Models\AccountType::all() as $type)
                                        <option value="{{ $type->id }}" data-organization="{{ $type->is_organization }}" @selected($type->id === $order->account_type_id)>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row"></div>

                                @if (sys_get('donor_title') !== "hidden")
                                <div class="form-group col-xs-3">
                                    <label>Title</label>
                                    @if (sys_get('donor_title_options') === "")
                                        <input type="text" name="billing_title" id="billingtitle" class="form-control" value="{{ $order->billing_title }}">
                                    @else
                                        <select name="billing_title" id="billingtitle" class="form-control">
                                            <option value="">Mr/Mrs</option>
                                            @foreach (explode(",",sys_get('donor_title_options')) as $option)
                                            <option value="{{ $option }}" @selected($option === $order->billing_title)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                @endif

                                <div class="form-group {{ sys_get('donor_title') !== "hidden" ? "col-xs-4" : "col-xs-7" }}">
                                    <label>First Name</label>
                                    <input type="text" class="form-control" name="billing_first_name" id="billing_first_name" value="{{ $order->billing_first_name }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>

                                <div class="form-group col-xs-5">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control" name="billing_last_name" id="billing_last_name" value="{{ $order->billing_last_name }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>

                                @if (feature('givecloud_pro'))
                                <div class="form-group organization-name col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label>Organization Name</label>
                                    <input type="text" class="form-control" name="billing_organization_name" value="{{ $order->billing_organization_name }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                @endif

                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="billingemail">Email:</label>
                                    <input type="text" class="form-control" name="billingemail" id="billingemail" value="{{ $order->billingemail }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="billingaddress1">Address:</label>
                                    <input type="text" class="form-control" name="billingaddress1" id="billingaddress1" value="{{ $order->billingaddress1 }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="billingaddress2">Address 2:</label>
                                    <input type="text" class="form-control" name="billingaddress2" id="billingaddress2" value="{{ $order->billingaddress2 }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="billingcity">City:</label>
                                    <input type="text" class="form-control" name="billingcity" id="billingcity" value="{{ $order->billingcity }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-lg-8 col-md-8 col-sm-8 col-xs-12">
                                    <label for="billingstate">{{ $billingSubdivisions['subdivision_type'] }}:</label>
                                    <select type="text" class="form-control" name="billingstate" id="billingstate" {{ !$order->userCan('edit') ? 'readonly' : '' }}>
                                        <option value="" class="text-placeholder">Select {{ $billingSubdivisions['subdivision_type'] }}</option>
                                        @foreach($billingSubdivisions['subdivisions'] as $stateCode => $stateName)
                                        <option {{ volt_selected($order->billingstate, $stateCode) }} value="{{ $stateCode }}">
                                                {{ $stateName }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                    <label for="billingzip">Zip/Postal Code:</label>
                                    <input type="text" class="form-control" name="billingzip" id="billingzip" value="{{ $order->billingzip }}" {{ (!$order->userCan('edit')) ? 'readonly' : '' }} />
                                </div>
                                <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <label for="billingcountry">Country:</label>
                                    <select class="form-control" name="billingcountry" id="billingcountry" data-country-state="billingstate" {{ (!$order->userCan('edit')) ? 'readonly' : '' }}>
                                        <option value="" class="text-placeholder">Select Country</option>
                                        @foreach($countries as $countryCode => $countryName)
                                        <option {{ volt_selected($order->billingcountry, $countryCode) }} value="{{ $countryCode }}">
                                                {{ $countryName }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <label for="billingphone">Phone:</label>
                                    <input type="text" class="form-control" name="billingphone" id="billingphone" value="{{ $order->billingphone }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>

                            </div>

                        </div>

                        <div role="tabpanel" class="tab-pane" id="pos-ship-address-tab">

                            <div class="row row-padding-sm">

                                @if (sys_get('donor_title') !== "hidden")
                                <div class="form-group col-xs-3">
                                    <label>Title</label>
                                    @if (sys_get('donor_title_options') === "")
                                    <input type="text" name="shipping_title" id="shippingtitle" class="form-control" value="{{ $order->shipping_title }}">
                                    @else
                                    <select name="shipping_title" id="shippingtitle" class="form-control">
                                        <option value="">Mr/Mrs</option>
                                        @foreach (explode(",",sys_get('donor_title_options')) as $option)
                                            <option value="{{ $option }}" @selected($option === $order->shipping_title)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                                @endif

                                <div class="form-group {{ sys_get('donor_title') !== "hidden" ? "col-xs-4" : "col-xs-7" }}">
                                    <label for="shipping_first_name">First Name:</label>
                                    <input type="text" class="form-control" name="shipping_first_name" id="shipping_first_name" value="{{ $order->shipping_first_name }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-xs-5">
                                    <label for="shipping_last_name">Last Name:</label>
                                    <input type="text" class="form-control" name="shipping_last_name" id="shipping_last_name" value="{{ $order->shipping_last_name }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group organization-name col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label>Organization Name</label>
                                    <input type="text" class="form-control" name="shipping_organization_name" value="{{ $order->shipping_organization_name }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="shipemail">Email:</label>
                                    <input type="text" class="form-control" name="shipemail" id="shipemail" value="{{ $order->shipemail }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="shipaddress1">Address:</label>
                                    <input type="text" class="form-control" name="shipaddress1" id="shipaddress1" value="{{ $order->shipaddress1 }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="shipaddress2">Address 2:</label>
                                    <input type="text" class="form-control" name="shipaddress2" id="shipaddress2" value="{{ $order->shipaddress2 }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="shipcity">City:</label>
                                    <input type="text" class="form-control" name="shipcity" id="shipcity" value="{{ $order->shipcity }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-lg-8 col-md-8 col-sm-8 col-xs-12">
                                    <label for="shipstate">{{ $shippingSubdivisions['subdivision_type'] }}:</label>
                                    <select type="text" class="form-control" name="shipstate" id="shipstate" {{ (!$order->userCan('edit'))?'readonly':'' }}>
                                        <option value="" class="text-placeholder">Select {{ $shippingSubdivisions['subdivision_type'] }}</option>
                                        @foreach($shippingSubdivisions['subdivisions'] as $stateCode => $stateName)
                                            <option {{ volt_selected($order->shipstate, $stateCode) }} value="{{ $stateCode }}">
                                                {{ $stateName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                    <label for="shipzip">Zip/Postal Code:</label>
                                    <input type="text" class="form-control" name="shipzip" id="shipzip" value="{{ $order->shipzip }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                                <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <label for="shipcountry">Country:</label>
                                    <select class="form-control" name="shipcountry" id="shipcountry" data-country-state="shipstate" {{ (!$order->userCan('edit'))?'readonly':'' }}>
                                        <option value="" class="text-placeholder">Select Country</option>
                                        @foreach($countries as $countryCode => $countryName)
                                        <option {{ volt_selected($countryCode, $order->shipcountry) }} value="{{ $countryCode }}">
                                                {{ $countryName }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <label for="shipphone">Phone:</label>
                                    <input type="text" class="form-control" name="shipphone" id="shipphone" value="{{ $order->shipphone }}" {{ (!$order->userCan('edit'))?'readonly':'' }} />
                                </div>
                            </div>
                        </div>


                        <div role="tabpanel" class="tab-pane" id="pos-comments-tab">
                            <div class="form-group">
                                <label for="shipaddress1">Special Notes:</label>
                                <p>These are the notes that were left by the customer.</p>
                                <textarea class="form-control" name="comments" style="height:80px;">{{ $order->comments }}</textarea>
                                <div class="checkbox">
                                    <label for="inputIsAnonymous">
                                        <input id="inputIsAnonymous" type="checkbox" name="is_anonymous" value="1" {{ $order->is_anonymous ? 'checked' : '' }}>
                                        Keep me anonymous
                                    </label>
                                </div>
                            </div>

                            <div class="form-group mt-6">
                                <label for="shipaddress1">Note to Customer:</label>
                                <p>This message will appear on the customer's receipt.</p>
                                <textarea class="form-control simple-html" name="customer_notes" style="height:80px;">{{ $order->customer_notes }}</textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info">Update</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
