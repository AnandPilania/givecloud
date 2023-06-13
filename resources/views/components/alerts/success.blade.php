@props([
    'title' => null,
    'class' => '',
])

<div class="{{ 'rounded-md bg-green-100 p-4 ' . $class }}">
  <div class="flex">
      <x-icons.check-circle class="text-green-400" size="5"/>
    <div class="shrink-0">
    </div>
    <div class="ml-3">
      @unless (empty($title))
        <h3 class="m-0 text-sm leading-5 font-medium text-green-800">
          {{ $title }}
        </h3>
      @endunless
      @unless (empty($slot->toHtml()))
        <div class="mt-2 text-sm leading-5 text-green-700">
            {{ $slot }}
        </div>
      @endunless
    </div>
  </div>
</div>
