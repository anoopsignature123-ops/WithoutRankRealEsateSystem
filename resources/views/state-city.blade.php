@php
    $stateName = $stateName ?? 'state';
    $cityName = $cityName ?? 'city';
    $stateId = $stateId ?? 'state_id';
    $cityId = $cityId ?? 'city_id';
@endphp

<div class="col-md-6">
    <label class="form-label fw-semibold">
        State <span class="text-danger">*</span>
    </label>

    <select name="{{ $stateName }}" id="{{ $stateId }}"
        class="form-select @error($stateName) is-invalid @enderror">
        <option value="">Select State</option>

        @foreach ($states as $state)
            <option value="{{ $state->id_state }}"
                {{ (string) ($selectedState ?? '') === (string) $state->id_state ? 'selected' : '' }}>
                {{ $state->state }}
            </option>
        @endforeach
    </select>

    @error($stateName)
        <div class="invalid-feedback">{{ $message }}</div>
    @else
        <div class="invalid-feedback state-error"></div>
    @enderror
</div>

<div class="col-md-6">
    <label class="form-label fw-semibold">
        City <span class="text-danger">*</span>
    </label>

    <select name="{{ $cityName }}" id="{{ $cityId }}"
        class="form-select @error($cityName) is-invalid @enderror">
        <option value="">Select City</option>
    </select>

    @error($cityName)
        <div class="invalid-feedback">{{ $message }}</div>
    @else
        <div class="invalid-feedback city-error"></div>
    @enderror
</div>

@push('scripts')
    <script>
        $(function() {
            const $stateSelect = $('#{{ $stateId }}');
            const $citySelect = $('#{{ $cityId }}');
            const cityEndpointBase = "{{ url('/get-cities') }}";

            const selectedState = "{{ $selectedState ?? '' }}";
            const selectedCity = "{{ $selectedCity ?? '' }}";

            function loadCities(stateId, cityToSelect = '') {
                if (!stateId) {
                    $citySelect.html('<option value="">Select City</option>');
                    return;
                }

                $.get(`${cityEndpointBase}/${stateId}`, function(response) {
                    let options = '<option value="">Select City</option>';

                    response.forEach(city => {
                        const isSelected = String(city.city) === String(cityToSelect) ? 'selected' :
                            '';
                        options +=
                            `<option value="${city.city}" ${isSelected}>${city.city}</option>`;
                    });

                    $citySelect.html(options);
                });
            }

            $stateSelect.on('change', function() {
                loadCities($(this).val());
            });

            if (selectedState) {
                loadCities(selectedState, selectedCity);
            }
        });
    </script>
@endpush
