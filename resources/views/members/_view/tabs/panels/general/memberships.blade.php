<div class="col-sm-6 @if (!feature('membership') || !$member->exists) hidden @endif">
    <div class="panel panel-default">
        <div class="panel-heading">
            <a
                href="#"
                class="pull-right btn btn-info btn-xs"
                data-group-account-action="add"
                data-account-id="{{ $member->getKey() }}">
                <i class="fa fas fa-plus"></i> Add
            </a>
            <i class="fa fas fa-users fa-fw"></i> {{ sys_get('syn_groups') }}
        </div>

        <div class="panel-body">
            @if ($member->groupAccountTimespans->isNotEmpty())
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="2">{{ sys_get('syn_group') }}</th>
                            <th width="90">Starts</th>
                            <th width="90">Ends</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($member->groupAccountTimespans as $group)
                            @if ($group->pivot->groupAccounts->count())
                            <tr>
                                <td>
                                    <a href="#" data-group-account-id="{{ $group->pivot->groupAccounts->last()->getKey() }}">
                                        <i class="fa fas fa-search"></i>
                                    </a>
                                </td>
                                <td>{{ $group->name }}</td>
                                <td>
                                    @if ($group->pivot->start_date)
                                        {{ fromUtc($group->pivot->start_date, 'M j, Y') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($group->pivot->end_date)
                                        {{ fromUtc($group->pivot->end_date, 'M j, Y') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-muted text-center text-lg">
                    <i class="fa fas fa-users fa-2x"></i><br>
                    No Groups or Memberships
                </div>
            @endif
        </div>
    </div>
</div>
