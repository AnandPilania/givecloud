@if($member->nps_status === 'detractor')
    <span class="text-danger">{{ number_format($member->nps, 1) }}</span>
@elseif($member->nps_status === 'passive')
    <span class="text-warning">{{ number_format($member->nps, 1) }}</span>
@elseif($member->nps_status === 'promoter')
    <span class="text-success">{{ number_format($member->nps, 1) }}</span>
@endif
