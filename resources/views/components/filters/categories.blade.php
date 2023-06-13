@php
    $counter = 0;
    $parent = $loop->parent;

    while($parent !== null){
        $counter++;
        $parent = $parent->parent;
    }
@endphp

<option value="{{ $category->id }}" {{ volt_selected($category->id, explode(',', request('categories'))) }}>
    @for($i = 0; $i < $counter; $i++)
        {!! '&nbsp;' !!}
    @endfor
    {{ $category->name }}
</option>

@foreach($category->childCategories as $category)
    @include('components.filters.categories', $category)
@endforeach

