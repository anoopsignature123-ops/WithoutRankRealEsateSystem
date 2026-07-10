<div class="card border-0 shadow-sm mb-4">

    <div class="card-body p-4">

        <h5 class="fw-bold mb-4 border-bottom pb-2">
            Primary Applicant Details
        </h5>

        <div class="row">

            {{-- Name --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Name
                </label>

                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    placeholder="Enter full name" value="{{ old('name', $primary?->name) }}">

                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            {{-- Title --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Title
                </label>

                <select name="title" class="form-select @error('title') is-invalid @enderror">

                    <option value="">
                        Select relation
                    </option>

                    <option value="s/o" {{ old('title', $primary?->title) == 's/o' ? 'selected' : '' }}>
                        S/O
                    </option>

                    <option value="w/o" {{ old('title', $primary?->title) == 'w/o' ? 'selected' : '' }}>
                        W/O
                    </option>

                    <option value="d/o" {{ old('title', $primary?->title) == 'd/o' ? 'selected' : '' }}>
                        D/O
                    </option>

                    <option value="c/o" {{ old('title', $primary?->title) == 'c/o' ? 'selected' : '' }}>
                        C/O
                    </option>

                </select>

                @error('title')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>



            {{-- Relation Name --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Relation Name
                </label>

                <input type="text" name="relation_name"
                    class="form-control @error('relation_name') is-invalid @enderror" placeholder="Enter relation name"
                    value="{{ old('relation_name', $primary?->relation_name) }}">

                @error('relation_name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>



            {{-- DOB --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Date Of Birth
                </label>

                <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror"
                    value="{{ old('dob', $primary?->dob) }}">

                @error('dob')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>



            {{-- Gender --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Gender
                </label>

                <select name="gender" class="form-select @error('gender') is-invalid @enderror">

                    <option value="">
                        Select gender
                    </option>

                    <option value="male" {{ old('gender', $primary?->gender) == 'male' ? 'selected' : '' }}>
                        Male
                    </option>

                    <option value="female" {{ old('gender', $primary?->gender) == 'female' ? 'selected' : '' }}>
                        Female
                    </option>

                </select>

                @error('gender')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>



            {{-- Pin --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Pin Code
                </label>

                <input type="text" id="primaryPin" name="pin_code"
                    class="form-control @error('pin_code') is-invalid @enderror" placeholder="Enter pin code"
                    value="{{ old('pin_code', $primary?->pin_code) }}">
                @error('pin_code')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            @include('state-city', [
                'states' => $states,
                'stateId' => 'primaryState',
                'cityId' => 'primaryCity',
                'stateName' => 'state',
                'cityName' => 'city',
                'selectedState' => old('state', $primary->state ?? ''),
                'selectedCity' => old('city', $primary->city ?? ''),
            ])

            {{-- Address --}}
            <div class="col-md-12 mb-3 mt-3">
                <label class="form-label">
                    Permanent Address
                </label>

                <textarea name="permanent_address" id="primaryAddress" rows="3"
                    class="form-control @error('permanent_address') is-invalid @enderror" placeholder="Enter full address">{{ old('permanent_address', $primary?->permanent_address) }}</textarea>

                @error('permanent_address')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>
    </div>
</div>
