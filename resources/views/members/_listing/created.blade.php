<div class="text-gray-500 text-sm">
    Created
    <time class="underline decoration-dotted decoration-gray-800 decoration-1 underline-offset-1"
      datetime="{{ toLocalFormat($member->created_at, 'date:c') }}"
      title="{{ toLocalFormat($member->created_at, 'M j, Y \a\t g:iA') }}">
        {{ toLocalFormat($member->created_at, 'humans') }}
    </time>
</div>
