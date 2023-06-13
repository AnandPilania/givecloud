
@extends('layouts.app')
@section('title', 'Messenger Conversations')

@section('content')

@if($conversations->count() == 0)

<div class="feature-highlight">
    <img class="feature-img" src="/jpanel/assets/images/icons/chat-payment.svg">
    <h2 class="feature-title">Fundraise Using SMS &amp; Instant Messaging</h2>
    <p>Connect your favorite instant messenger and start accepting donations through conversation.</p>

    @if ($recipients->isNotEmpty())
        <p id="provisionT2G--wrapper" class="text-success text-bold">
            @if ($recipients->count() === 1)
                Your number:
            @else
                Your numbers:
            @endif
            <br>

            @foreach ($recipients as $recipient)
                <div class="text-success text-bold provisionT2G--number">
                    {{ $recipient->identifier_us_formatted }}
                    @if (is_super_user())
                        <a href="{{ route('messenger.phone_numbers.destroy', $recipient) }}" onclick="j.t2gNumbers.delete(this, '{{ $recipient->identifier }}'); return false">
                            <i class="fa fa-times-circle text-danger"></i>
                        </a>
                    @endif
                </div>
            @endforeach
        </p>
    @endif

    <div class="feature-actions">
        @if ($recipients->isNotEmpty())
            <a href="/jpanel/messenger/conversations/add" class="btn btn-lg btn-success btn-pill"><i class="fa fa-gear"></i> Setup a Conversation</a>
        @elseif (is_super_user())
            <a href="#" onclick="j.t2gNumbers.show(); return false" class="btn btn-lg btn-success btn-pill">
                <i class="fa fa-plus"></i> Provision a Phone Number
            </a>
        @elseif (user()->can_live_chat)
            <a href="javascript:Intercom('showNewMessage','Can you please provision a phone number for my instant messaging feature?');" class="btn btn-lg btn-success btn-pill">
                <i class="fa fa-plus"></i> Request a Phone Number
            </a>
        @endif
        <a href="https://help.givecloud.com/en/articles/2845454-collecting-contributions-using-sms-messages" target="_blank" class="btn btn-lg btn-outline btn-primary btn-pill" rel="noreferrer">
            <i class="fa fa-book"></i> Learn More
        </a>
    </div>
</div>

@else

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Messenger

            <div class="pull-right">
                <a href="/jpanel/messenger/conversations/add" class="btn btn-success">
                    <i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span>
                </a>

                <a href="https://help.givecloud.com/en/articles/2845454-collecting-contributions-using-sms-messages" target="_blank" class="btn btn-default" rel="noreferrer"><i class="fa fa-book"></i> Learn More</a>
            </div>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<div class="row">
    <div class="col-sm-8 col-lg-9">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Listening For</th>
                        <th>Messaging Client(s)</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($conversations as $conversation)
                    <tr @unless($conversation->enabled) class="text-danger" @endunless>
                        <td width="16"><a href="/jpanel/messenger/conversations/{{ $conversation->id }}"><i class="fa fa-search"></i></a></td>
                        <td>
                            {{ $conversation->command }}
                            @unless($conversation->enabled) <span class="label label-danger label-xs">DISABLED</span> @endunless
                        </td>
                        <td>
                            @if ($conversation->recipients->isNotEmpty())
                                {{ $conversation->recipients->pluck('identifier')->implode(', ') }}
                            @else
                                ALL
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-sm-4 col-lg-3">
        <div class="panel panel-basic">
            <div class="panel-heading">
                <i class="fa fa-comments fa-fw"></i> Messaging Clients
            </div>
            <div class="panel-body">
                <strong><i class="fa fa-mobile"></i> SMS Text Messaging</strong><br>
                @if($recipients->count() > 0)
                    @foreach($recipients as $recipient)
                        <div class="text-lg">
                            {{ $recipient->identifier_us_formatted }}
                            @if (is_super_user())
                                <a href="{{ route('messenger.phone_numbers.destroy', $recipient) }}" onclick="j.t2gNumbers.delete(this, '{{ $recipient->identifier }}'); return false">
                                    <i class="fa fa-times-circle text-danger"></i>
                                </a>
                            @endif
                        </div>
                    @endforeach
                    @if (is_super_user())
                        <a href="#" onclick="j.t2gNumbers.show(); return false">Provision Another Number</a>
                    @elseif (user()->can_live_chat)
                        <a href="javascript:Intercom('showNewMessage','I\'d like to request a number for the messenger service.');">Request Another Number</a>
                    @endif
                @elseif (is_super_user())
                    <a href="#" onclick="j.t2gNumbers.show(); return false">Provision Another Number</a>
                @elseif (user()->can_live_chat)
                    <a href="javascript:Intercom('showNewMessage','I\'d like to request a number for the messenger service.');">Request A Number</a>
                @endif
            </div>
        </div>
        <div class="panel panel-basic">
            <div class="panel-body">
                Test your conversations using the <a href="/jpanel/messenger/console" target="_blank">Messaging Console</a>.
            </div>
        </div>
    </div>
</div>

@endif

@endsection

@section('scripts')
    @parent

    <script type="text/x-lodash-template" id="t2gNumbersModalTmpl">
        <div class="modal fade" id="provisionT2G">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">
                            Provision a Phone Number
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div id="provisionT2G--search">
                            <form action="{{ route('messenger.phone_numbers_search.index', [], false) }}" method="GET">
                                <div class="flex flex-col justify-between sm:flex-row">
                                    <div class="mb-4 sm:mb-0">
                                        <label class="block h-6" for="provisionT2G--search--country">Country:</label>
                                        <div>
                                            <select
                                                id="provisionT2G--search--country"
                                                name="country"
                                                class="form-control"
                                                required>
                                                @php
                                                    $countries = array_map('mb_strtolower', array_values(cart_countries()));
                                                    $clientCountry = mb_strtolower(site('client')->country);
                                                    if (!in_array($clientCountry, $countries, true)) {
                                                        $clientCountry = 'united states';
                                                    }
                                                @endphp
                                                @foreach (cart_countries() as $code => $name)
                                                    <option value="{{ $code }}" {{ volt_selected(mb_strtolower($name), $clientCountry) }}>
                                                        {{ flagUnicode($code) }}&nbsp;&nbsp;{{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-4 sm:mb-0">
                                        <label class="h-6">Type:</label>
                                        <div class="sm:pt-2">
                                            <div class="radio inline">
                                                <label>
                                                    <input type="radio" name="type" value="toll-free" onclick="j.t2gNumbers.hideAreaCode()" checked> Toll-free
                                                </label>
                                            </div>
                                            <div class="radio inline sm:ml-2">
                                                <label>
                                                    <input type="radio" name="type" value="local" onclick="j.t2gNumbers.showAreaCode()"> Local
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-4 sm:mb-0">
                                        <label class="h-6" for="provisionT2G--search--area_code">Area Code:</label>
                                        <input
                                            type="input"
                                            name="area_code"
                                            id="provisionT2G--search--area_code"
                                            class="form-control"
                                            value=""
                                            placeholder="(optional)"
                                            disabled>
                                    </div>
                                    <div class="flex items-end">
                                        <button type="submit" class="btn btn-success">Search</button>
                                    </div>
                                </div>
                                <hr>
                                <div
                                    id="provisionT2G--search--results"
                                    data-choose-url="{{ route('messenger.phone_numbers.store', [], false) }}">
                                </div>
                            </form>
                        </div>
                        <div class="pt-4">
                            <span class="mr-2">👋</span>
                            Oy CX! If you're provisioning a number outside of Canada / US check <a href="https://support.twilio.com/hc/en-us/articles/223182908-How-much-does-a-phone-number-cost-" title="Phone number cost in Twilio">twilio</a> for pricing to ensure you're not buying a mega-expensive number.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>
@endsection
