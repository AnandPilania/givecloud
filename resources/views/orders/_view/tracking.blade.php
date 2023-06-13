<div class="panel panel-basic">
    <div class="panel-body">
        <div class="bottom-gutter-sm">
            <div class="panel-sub-title">Tracking</div>
        </div>

        <div class="row">

            @if (sys_get('referral_sources_isactive'))
                @if ($order->referral_source)
                    <div class="col-xs-6 stat">
                        <div class="stat-value-xs text-ellipsis">{{ $order->referral_source }}</div>
                        <div class="stat-label">"How Did You Hear About Us"</diV>
                    </div>
                @else
                    <div class="col-xs-6 stat">
                        <div class="stat-value-xs text-muted">N/A</div>
                        <div class="stat-label">Referral Source</diV>
                    </div>
                 @endif
            @endif

            @if ($order->http_referer)
                @php
                $http_referer_domain = parse_url($order->http_referer)['host'];
                $http_referer_domain = str_replace('www.', '', $http_referer_domain);
                @endphp
            <div class="col-xs-6 stat">
                <div class="stat-value-xs text-ellipsis" title="{{ $order->http_referer }}">
                    <a target="_blank" href="{{ $order->http_referer }}">
                        <i class="fa {{ fa_social_icon($http_referer_domain) }}"></i> {{ $http_referer_domain }}</a>
                </div>
                <div class="stat-label">Referring Website</diV>
            </div>
            @else
            <div class="col-xs-6 stat">
                <div class="stat-value-xs text-muted">Direct</div>
                <div class="stat-label">Referring Website</diV>
            </div>
            @endif
            <div class="clearfix"></div>

            @if(!$order->is_pos && $order->client_browser)
                @php($ua = $order->ua())
                <div class="col-xs-6 stat">
                    <div class="stat-value-xs text-ellipsis">
                            @if (fa_ua_icon($ua->os->family))
                                <i class="fa {{ fa_ua_icon($ua->os->family) }}"></i>
                            @endif
                            {{ $ua->os->family }} <small class="text-muted">{{ $ua->os->toVersion() }}</small>
                    </div>
                    <div class="stat-label">Operating System</diV>
                </div>
                <div class="col-xs-6 stat">
                    <div class="stat-value-xs text-ellipsis">
                            @if (fa_ua_icon($ua->ua->family))
                                <i class="fa {{ fa_ua_icon($ua->ua->family) }}"></i>
                            @endif
                            {{ $ua->ua->family }} <small class="text-muted">{{ $ua->ua->toVersion() }}</small>
                    </div>
                    <div class="stat-label">Browser</diV>
                </div>
            @else
                <div class="col-xs-6 stat">
                    <div class="stat-value-xs text-muted">N/A</div>
                    <div class="stat-label">Operating System</diV>
                </div>
                <div class="col-xs-6 stat">
                    <div class="stat-value-xs text-muted">N/A</div>
                    <div class="stat-label">Browser</diV>
                </div>
            @endif

            @if($order->client_ip)
                @php($order_count = \Ds\Models\Order::whereNotNull('confirmationdatetime')->where('client_ip', $order->client_ip)->count())
                <div class="col-xs-6 stat">
                    <div class="stat-value-xs text-ellipsis" data-popover-bottom="<strong>IP Address ({{ $order->client_ip }})</strong><br>This is the internet location from which this contribution was placed. Click to view other orders from the same IP address.<br><br>The flag indicates the country in which the IP is located to a 90% degree of accuracy.">
                        @if($order->ip_country)
                            <img src="{{ flag($order->ip_country) }}" style="margin-right:3px; width:16px; height:16px; vertical-align:middle;">
                       @endif
                        <a href="{{ route('backend.orders.index', ['fO' => $order->client_ip]) }}">{{ $order->client_ip }}</a>
                        @if($order_count > 1)
                            <span class="badge">{{ $order_count }}</span>
                        @endif
                    </div>
                    <div class="stat-label">IP Address</diV>
                </div>
            @endif

            @if($order->tracking_source)
                <div class="col-xs-6 stat">
                    <div class="stat-value-xs text-ellipsis">
                           {{ $order->tracking_source }}
                    </div>
                    <div class="stat-label">Source</diV>
                </div>
            @endif

            @if($order->tracking_medium)
                <div class="col-xs-6 stat">
                    <div class="stat-value-xs text-ellipsis">
                            {{ $order->tracking_medium }}
                    </div>
                    <div class="stat-label">Medium</diV>
                </div>
            @endif

            @if($order->tracking_campaign)
                <div class="col-xs-6 stat">
                    <div class="stat-value-xs text-ellipsis">
                        {{ $order->tracking_campaign }}
                    </div>
                    <div class="stat-label">Campaign</diV>
                </div>
            @endif

            @if($order->tracking_term)
                <div class="col-xs-6 stat">
                    <div class="stat-value-xs text-ellipsis">
                        {{ $order->tracking_term }}
                    </div>
                    <div class="stat-label">Term</diV>
                </div>
            @endif

            @if($order->tracking_content)
                <div class="col-xs-6 stat">
                    <div class="stat-value-xs text-ellipsis">
                        {{ $order->tracking_content }}
                    </div>
                    <div class="stat-label">Content</diV>
                </div>
            @endif
        </div>
    </div>
</div>
