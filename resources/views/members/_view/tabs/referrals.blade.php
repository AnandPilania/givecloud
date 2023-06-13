<div role="tabpanel" class="tab-pane fade in" id="referrals">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-history fa-fw"></i> Referrals
        </div>

        <div class="panel-body">
            <div class="table-responsive">
                <table id="memberReferrals" class="table table-striped table-hover responsive">
                    <thead>
                        <tr>
                            <th colspan="2">Name</th>
                            <th>Supporter Type</th>
                            <th>Email</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($member->referrals as $referral)
                            <tr>
                                <td width="16">
                                    <a href="{{ route('backend.member.edit', $referral) }}">
                                        <i class="fa fas fa-search"></i>
                                    </a>
                                </td>
                                <td>{{ $referral->display_name }}</td>
                                <td>{{ $referral->accountType->name }}</td>
                                <td>
                                    <a href="mailto:{{ $referral->email }}">
                                        {{ $referral->email }}
                                    </a>
                                </td>
                                <td>
                                    {{ toLocalFormat($referral->created_at) }}
                                    <small class="text-muted">
                                        {{ toLocal($referral->created_at)->format('g:iA') }}
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
