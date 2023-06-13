@php
    use Ds\Enums\RecurringPaymentProfileStatus;
@endphp

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fas fa-refresh fa-fw"></i> Recurring Payments
    </div>

    <div class="panel-body">
        <div class="table-responsive">
            <table id="memberRecurringPaymentProfiles" class="table table-striped responsive">
                <thead>
                    <tr>
                        <th colspan="2">Description</th>
                        <th>Start date</th>
                        <th>Next billing date</th>
                        <th>Last billed amount</th>
                        <th>Lifetime total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($member->recurringPaymentProfiles as $profile)
                        <tr>
                            <td width="16">
                                <a href="{{ route('backend.recurring_payments.show', $profile->profile_id) }}">
                                    <i class="fa fas fa-search"></i>
                                </a>
                            </td>
                            <td>{{ $profile->description }}</td>
                            <td>{{ toLocalFormat($profile->profile_start_date, 'fdate') }}</td>
                            <td>
                                @if ($profile->status == RecurringPaymentProfileStatus::ACTIVE)
                                    {{ toLocalFormat($profile->next_billing_date, 'fdate') }}
                                @endif
                            </td>
                            <td>{{ money($profile->last_payment_amt, $profile->currency_code) }}</td>
                            <td>{{ money($profile->aggregate_amount, $profile->currency_code) }}</td>
                            <td>{{ $profile->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
