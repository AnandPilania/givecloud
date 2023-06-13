@props([
    'title' => null,
    'class' => '',
])

<div class="{{ 'rounded-md bg-red-50 p-4 ' . $class }}">
  <div class="flex">
    <div class="shrink-0">
      <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div class="ml-3">
      @unless (empty($title))
        <h3 class="m-0 text-sm leading-5 font-medium text-red-800">
          {{ $title }}
        </h3>
      @endunless
      @unless (empty($slot->toHtml()))
        <div class="mt-2 text-sm leading-5 text-red-700">
            {{ $slot }}
        </div>
      @endunless
    </div>
  </div>
</div>
