<a id="pinned"></a>
<div class="mt-10 sm:mt-0">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Pinned Menu Items</h3>
                <p class="mt-1 text-sm leading-5 text-gray-600">Manage your menu items.</p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form class="form-horizontal" action="{{ route('backend.pin_menu_items.store') }}" method="post">
                @csrf

                <div class="shadow sm:rounded-md mb-5">
                    <div class="px-4 py-5 bg-white sm:p-6">
                        <h4 class="text-md font-medium leading-6 text-gray-600 m-0 mb-4">Select menu items to pin to main menu</h4>

                        <div class="col-span-6 sm:col-span-4">
                            <label for="name" class="block text-sm font-medium leading-5 text-gray-700">Menu Items</label>
                            <select name="menuItems[]"
                                    class="selectize orderable auto-height form-control w-full max-w-md"
                                    multiple
                                    data-max-items="8"
                                    data-ordered="{{ json_encode($pinnedItems) }}">

                                @foreach($menuItems as $item)
                                    <optgroup label="{{ $item['label'] }}">
                                        @if(isset($item['new_link']))
                                            <option value="{{ $item['key'] }}"
                                                {{ volt_selected($item['key'], $pinnedItems) }}>
                                                    {{ $item['new_link']->label }}
                                            </option>
                                        @endif

                                        @if(array_key_exists('children', $item))
                                            @foreach($item['children'] as $child)
                                                @if((array_key_exists('url', $child)))
                                                <option value="{{ $child['key'] }}"
                                                    {{ volt_selected($child['key'], $pinnedItems) }}>
                                                        {{ $child['pinned_label'] ?? $child['label'] }}
                                                </option>
                                                @endif
                                            @endforeach
                                        @elseif(array_key_exists('url', $item))
                                            <option value="{{ $item['key'] }}"
                                                {{ volt_selected($item['key'], $pinnedItems) }}>
                                                    {{ $item['pinned_label'] ?? $item['label'] }}
                                            </option>
                                        @endif
                                    </optgroup>
                                @endforeach
                            </select>
                            <span class="text-muted">You can select up to 8 items.</span>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-gray-50 sm:px-6">
                        <input class="bg-gcb-300 btn font-bold rounded-md text-gcb-700" type="submit" value="Save">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    spaContentReady(function() {
        /*
        This is a workaround to scroll to the correct 
        part of the page. It looks like because the SPA 
        is loading in the page content after page 
        load, this default browser behaviour is not working.
        */

        if (window.location.hash === '#pinned') {
            location.href = "#pinned";
        }
    });
</script>
