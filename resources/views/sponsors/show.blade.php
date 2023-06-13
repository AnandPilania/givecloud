@php
    use Ds\Enums\RecurringPaymentProfileStatus;
@endphp

<form>
    <input type="hidden" name="sponsor_id" value="{{ $sponsor->id }}">
    <input type="hidden" name="sponsorship_id" value="{{ $sponsor->sponsorship->id }}">

    <div class="modal-body">

        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label class="control-label">{{ sys_get('syn_sponsorship_child') }}</label><br>

                    <div class="pull-left avatar-xl" style="margin-right:14px; background-size:cover; background-position:center center; background-image:url('{{ ($sponsor->sponsorship->is_image_valid) ? $sponsor->sponsorship->image_thumbnail : '/jpanel/assets/images/avatar.png' }}');"></div>

                    <div class="text-lg">
                        <a href="/jpanel/sponsorship/edit?i={{ $sponsor->sponsorship->id }}">{{ $sponsor->sponsorship->display_name }}</a><br>
                    </div>
                    <small class="text-muted">Reference: {{ $sponsor->sponsorship->reference_number }}</small>
                </div>
            </div>
        </div>

        <div class="row">

            @if ($sponsor->exists)
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Sponsor</label><br>

                        <div class="pull-left avatar-xl" style="margin-right:14px; background-size:cover; background-position:center center; background-image:url('/jpanel/assets/images/avatar.png');"></div>
                        @if ($sponsor->member)
                            <div class="text-lg">
                                <a href="{{ route('backend.member.edit', $sponsor->member->id) }}">{{ $sponsor->member->display_name }}</a><br>
                            </div>
                            @if ($sponsor->member->created_at)
                                <small class="text-muted">{{ $sponsor->member->created_at }} ({{ $sponsor->member->created_at->diffForHumans() }})</small>
                            @endif
                        @endif
                    </div>
                </div>
            @else
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="control-label">Sponsor</label>
                        <select class="form-control ds-members" name="member_id" placeholder="Find a supporter...">
                        @if ($sponsor->member)
                            <option value="{{ $sponsor->member->id }}" data-email="{{ $sponsor->member->email }}" selected>{{ $sponsor->member->display_name }}</option>
                        @endif
                        </select>
                        <small class="text-muted">Sponsors must be account holders. If you can't find the individual you are looking for, make sure they have a supporter account.</small>
                    </div>
                </div>
            @endif

            @if ($sponsor->orderItem)
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label">From Contribution</label>
                        <div class="text-lg">
                            <a href="{{ route('backend.orders.edit', $sponsor->orderItem->order) }}"><i class="fa fa-shopping-cart"></i> {{ $sponsor->orderItem->order->invoicenumber }}</a>
                        </div>
                        <small class="text-muted">{{ $sponsor->orderItem->payment_string }}
                            @if ($sponsor->getRecurringPaymentProfile())
                                - Recurring Profile <a href="/jpanel/recurring_payments/{{ $sponsor->getRecurringPaymentProfile()->profile_id }}">{{ $sponsor->getRecurringPaymentProfile()->profile_id }}</a>
                                <br>
                                @if ($sponsor->getRecurringPaymentProfile()->status == RecurringPaymentProfileStatus::ACTIVE)
                                    <small class="text-muted text-success"><i class="fa fa-check-circle"></i>
                                @elseif ($sponsor->getRecurringPaymentProfile()->status == RecurringPaymentProfileStatus::EXPIRED)
                                    <small class="text-muted"><i class="fa fa-check-circle"></i>
                                @elseif ($sponsor->getRecurringPaymentProfile()->status == RecurringPaymentProfileStatus::SUSPENDED)
                                    <small class="text-muted text-warning"><i class="fa fa-exclamation-circle"></i>
                                @else
                                    <small class="text-muted text-danger"><i class="fa fa-times-circle"></i>
                                @endif

                                    {{ $sponsor->getRecurringPaymentProfile()->status }}</small>

                            @endif
                        </small>

                    </div>
                </div>
            @endif


        </div>

        <div class="row">

            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label">Started on</label>
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                        <input type="text" class="form-control datePretty" name="started_at" value="{{ ($sponsor->started_at) ? $sponsor->started_at : today() }}" {{ ($sponsor->exists) ? 'disabled' : '' }} >
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label">Source</label>
                    <select name="source" class="form-control" {{ ($sponsor->exists) ? 'disabled' : '' }} >
                        <option value="">Choose a Source</option>
                        @foreach (sys_get('list:sponsorship_sources') as $source)
                            <option value="{{ $source }}" @selected($source == $sponsor->source)>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if (!$sponsor->exists)
                <div class="col-sm-12">
                <br>
                    <div class="form-group text-center">
                        <input type="checkbox" name="send_sponsor_start_email" value="1"> Notify sponsor that their sponsorship has started
                    </div>
                </div>
            @endif
        </div>

        @if ($sponsor->exists)

            <hr>

            @if (!$sponsor->ended_at)
                <div class="text-center end-sponsorship-btn">
                    <button type="button" class="btn btn-danger ds-end-sponsorship"><i class="fa fa-times"></i> End Sponsorship</button>
                </div>
            @endif

            <div class="end-sponsorship end-sponsorship-fields {{ !($sponsor->ended_at) ? 'hide' : '' }}">

                <div class="row">

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Ended on</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                                <input type="text" class="form-control datePretty" name="ended_at" value="{{ ($sponsor->ended_at) ? $sponsor->ended_at : today() }}"
                                @if($sponsor->ended_at){{ ($sponsor->can_restore_sponsorship) ? '' : 'disabled' }}@endif>
                            </div>
                            <small class="text-muted">@if($sponsor->endedBy)Ended by {{ $sponsor->endedBy->full_name }}.@endif</small>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Ended Reason</label>
                            <select name="ended_reason" class="form-control">
                                <option value="">Choose a Reason</option>
                                @foreach (sys_get('list:sponsorship_end_reasons') as $reason)
                                    <option value="{{ $reason }}" @selected($reason == $sponsor->ended_reason)>{{ $reason }}</option>
                                @endforeach
                                <option value="Recurring payment suspended">Recurring payment suspended</option>
                                <option value="Recurring payment cancelled">Recurring payment cancelled</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="control-label">Additional Notes</label>
                            <textarea class="form-control" name="ended_note" style="height:100px;">{{ $sponsor->ended_note }}</textarea>
                        </div>
                    </div>

                    @if (!$sponsor->ended_at)
                        <div class="col-sm-12">
                        <br>
                            <div class="form-group text-center">
                                <input type="checkbox" name="send_sponsor_end_email" value="1"> Notify sponsor that their sponsorship has ended
                            </div>
                        </div>
                    @endif
                </div>

            </div>

        @endif

    </div>

    <div class="end-sponsorship {{ ($sponsor->exists and !$sponsor->ended_at)  ? 'hide' : '' }}">

        <div class="modal-footer">

            @if ($sponsor->exists and !$sponsor->orderItem and $sponsor->userCan('edit'))
            <div class="pull-left">
                <button type="button" class="btn btn-danger ds-sponsor-delete"><i class="fa fa-times"></i> Delete</button>
            </div>
            @endif

            @if ($sponsor->userCan('edit'))
                <button type="button" class="btn btn-success ds-sponsor-save"><i class="fa fa-check"></i> Save</button>
            @endif

            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

        </div>

    </div>

</form>
