<div class="text-gray-700">
    @if($member->accountType && $member->accountType->is_organization && $member->bill_organization_name)
        {{ $member->first_name . ' ' . $member->last_name }}
    @endif
</div>
<div class="">
    @if(!$member->email)
        <span class="italic text-gray-400 text-sm">No email provided</span>
    @else
        <a href="mailto:{{ $member->email }}">{{$member->email}}</a>
    @endif
</div>
