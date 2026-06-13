@props([
    'cityModel' => 'city',
    'countryModel' => 'country',
    'latitudeModel' => 'latitude',
    'longitudeModel' => 'longitude',
    'searchModel' => 'location_search',
    'city' => null,
    'country' => null,
    'label' => __('Kota domisili'),
    'placeholder' => __('Ketik nama kota'),
])

<div
    data-city-autocomplete
    data-search-url="{{ config('services.world_city.search_url') }}"
    data-city-model="{{ $cityModel }}"
    data-country-model="{{ $countryModel }}"
    data-latitude-model="{{ $latitudeModel }}"
    data-longitude-model="{{ $longitudeModel }}"
    data-search-model="{{ $searchModel }}"
    class="relative"
>
    <flux:field>
        <flux:label>{{ $label }}</flux:label>

        <flux:input
            data-city-autocomplete-input
            type="text"
            autocomplete="off"
            :value="collect([$city, $country])->filter()->join(', ')"
            :placeholder="$placeholder"
        />

        <flux:description data-city-autocomplete-status>
            {{ __('Pilih kota dari daftar hasil pencarian.') }}
        </flux:description>

        <flux:error :name="$cityModel" />
        <flux:error :name="$countryModel" />
        <flux:error :name="$latitudeModel" />
        <flux:error :name="$longitudeModel" />
    </flux:field>

    <input type="hidden" wire:model="{{ $cityModel }}" data-city-autocomplete-value="city">
    <input type="hidden" wire:model="{{ $countryModel }}" data-city-autocomplete-value="country">
    <input type="hidden" wire:model="{{ $latitudeModel }}" data-city-autocomplete-value="latitude">
    <input type="hidden" wire:model="{{ $longitudeModel }}" data-city-autocomplete-value="longitude">
    <input type="hidden" wire:model="{{ $searchModel }}" data-city-autocomplete-value="search">

    <div
        data-city-autocomplete-results
        class="absolute z-30 mt-2 hidden max-h-72 w-full overflow-y-auto rounded-lg border border-zinc-200 bg-white py-1 shadow-lg"
    ></div>
</div>
