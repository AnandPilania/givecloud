<div role="tabpanel" class="tab-pane fade in" id="history">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-history fa-fw"></i> History
        </div>

        <div class="panel-body">
            <div class="table-responsive">
                <table
                    class="datatable table table-striped table-hover responsive"
                    data-searching="false"
                    data-page-length="250"
                    data-order="[[0, &quot;desc&quot;]]">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Event</th>
                            <th>Device</th>
                            <th>Approximate Location</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($member->getHistory() as $history)
                            @php
                                $ua = $history->ua();
                                try {
                                    $geo = app('geoip')->getLocationData($history->ip);
                                } catch (\Throwable $e) {
                                    $geo = null;
                                }
                            @endphp

                            <tr>
                                <td data-order="{{ toLocalFormat($history->occured_at, "U") }}">
                                    {{ toLocalFormat($history->occured_at, "M j, Y g:i:sa") }}
                                </td>
                                <td>
                                    @if ($history->metadata)
                                        <a
                                            href="#"
                                            data-historical-changes="{{ json_encode([
                                                "url_reference" => $history->url_reference,
                                                "changes" => json_decode($history->metadata)
                                            ]) }}">
                                            {{ $history->description }}
                                        </a>
                                    @else
                                        {{ $history->description }}
                                    @endif
                                </td>
                                <td>
                                    @if ($ua->device->brand)
                                        <i class="fa fas fa-mobile fa-fw"></i> {{ trim($ua->device->brand . ' ' . $ua->device->model) }}
                                    @else
                                        <i class="fa fas fa-desktop fa-fw"></i> Computer
                                    @endif
                                    <small>
                                        {{ $ua->ua->family }}
                                        {{ $ua->ua->toVersion() }}
                                        on {{ $ua->os->family }}
                                        {{ $ua->os->toVersion() }}
                                    </small>
                                </td>
                                <td data-order="{{ $history->ip }}">
                                    @if ($geo)
                                        <img
                                            src="{{ flag($geo->iso_code) }}"
                                            style="margin-right:3px; width:16px; height:16px; vertical-align:middle;">
                                        {{ $history->ip }}
                                        @if ($geo->city)
                                            <small>{{ $geo->city }}, {{ $geo->state }}</small>
                                        @endif
                                    @else
                                        {{ $history->ip }}
                                    @endif
                                </td>
                                <td>{{ $history->user_reference }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
