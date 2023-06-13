<div class="flex items-center">
    <div class="h-10 w-10 flex-shrink-0">
        <a href="{{ route('backend.member.edit', $member->id) }}">
            <span id="supporter:{{ $member->id }}" data-initials="{{ $member->initials }}" class="hidden avatar inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-500">
              <span class="font-bold leading-none text-white">{{ $member->initials }}</span>
            </span>
            <img class="h-10 w-10 rounded-full" src="{{ $member->avatar ?? $member->gravatar }}" alt="{{ $member->display_name }}"
                 onerror="this.classList.add('hidden'); document.getElementById('supporter:{{ $member->id }}').classList.remove('hidden')">
        </a>
    </div>
    <div class="ml-4">
        <div class="font-medium text-gray-900  font-bold">{{ $member->display_name }}</div>
        <div class="text-gray-500 text-sm">{{ $member->accountType->name }}</div>
    </div>
</div>
