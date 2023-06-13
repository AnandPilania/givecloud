<div class="panel panel-basic">
    <div class="panel-body">
        <div class="panel-sub-title mb-0">Risk @if($order->warning_count <= 0)<span class="ml-1 font-bold text-sm text-gray-300">No Warnings</span>@endif</div>

        @if($order->warning_count > 0)
            <ul class="p-0 mt-4 ml-4 ">
                @foreach($warnings as $warning)
                <li class="flex mb-3.5" data-popover-bottom="{{ $warning['tooltip'] }}">
                    <div class="flex-shrink-0 self-center">
                        <span class="inline-flex items-center rounded-full border border-transparent bg-orange-400 p-1 text-white shadow-sm">
                            <i class="fa fa-shield"></i>
                        </span>
                    </div>

                    <div class="flex-grow pl-3 font-semibold leading-5">{{ $warning['title'] }}</div>
                </li>
                @endforeach
            </ul>

            <div class="flex justify-end my-4">
                <a href="https://help.givecloud.com/en/articles/3050926-avs-cvc-and-geoip-risk-indicators"
                   target="_blank"
                        class="inline-flex items-center rounded-md border border-brand-blue bg-white px-4 py-2 text-base text-brand-blue hover:bg-gcb-50 focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2">
                    Learn More
                </a>
            </div>
        @endif
    </div>
</div>
