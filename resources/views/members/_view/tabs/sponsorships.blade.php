<div role="tabpanel" class="tab-pane fade in" id="sponsorships">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-child fa-fw"></i> Sponsorships
        </div>

        <div class="panel-body">
            <div class="table-responsive">
                <table id="account-sponsorships" class="table table-striped table-hover responsive">
                    <thead>
                        <tr>
                            <th colspan="2">{{ sys_get('syn_sponsorship_child') }}</th>
                            <th>Ref #</th>
                            <th>Contribution</th>
                            <th>Source</th>
                            <th>Started</th>
                            <th>Ended</th>
                            <th>Ended Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($member->sponsors)
                            @foreach($member->sponsors as $sponsor)
                                <tr class="@if ($sponsor->is_ended) text-danger @endif">
                                    <td width="16">
                                        <a href="#" class="ds-sponsor" data-sponsor-id="{{ $sponsor->getKey() }}">
                                            <i class="fa fas fa-search"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('backend.sponsorship.view', $sponsor->sponsorship) }}">
                                            {{ $sponsor->sponsorship->display_name }}
                                        </a>
                                    </td>
                                    <td>{{ $sponsor->sponsorship->reference_number }}</td>
                                    <td>
                                        @if ($sponsor->orderItem)
                                            <a href="{{ route('backend.orders.edit', $sponsor->orderItem->order) }}">
                                                {{ $sponsor->orderItem->order->invoicenumber }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $sponsor->source }}</td>
                                    <td>
                                        {{ $sponsor->started_at }}
                                        <small class="text-muted">
                                            {{ $sponsor->started_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        @if ($sponsor->ended_at)
                                            {{ $sponsor->ended_at }}
                                            <small class="text-muted">
                                                {{ $sponsor->ended_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $sponsor->ended_reason }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
