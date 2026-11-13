@props([
    'name' => '',
    'label' => '',
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
    'required' => false,
])

<div x-data="rumusTeks({{ json_encode((string) old($name, $value)) }})">
    <x-form-textarea
        :name="$name"
        :label="$label"
        :value="$value"
        :placeholder="$placeholder"
        :rows="$rows"
        :required="$required"
        x-model="teks"
    />
    <div class="-mt-2">
        <x-pratinjau-rumus value="teks" />
        <x-rumus-bantu value="teks" />
    </div>
</div>
