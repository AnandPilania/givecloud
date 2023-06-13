
@foreach ($item->fields ?? [] as $field)
    <div class="form-group">
        <input type="hidden" name="itemField[{{ $field->pivot->id }}][id]" value="{{ $field->pivot->id }}">
        <label>{{ $field->name }}</label>
        <input type="text" class="form-control" name="itemField[{{ $field->pivot->id }}][value]" value="{{ $field->pivot->value }}">
        @if ($field->pivot->value !== $field->pivot->original_value)
            <small class="text-muted">Original: "{{ $field->pivot->original_value }}"</small>
        @endif
    </div>
@endforeach
