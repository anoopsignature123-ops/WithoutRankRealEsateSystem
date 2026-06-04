@if ($step == 3)
    @php
        $primary = $customer->primaryDetail;
    @endphp

    <form method="POST" action="{{ route('customer-booking.update', $customer->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <input type="hidden" name="step" value="3">

        @include('customer-booking.partials.document-form', [
            'prefix' => '',
            'title' => 'Primary Applicant Documents',
            'document' => $customer->primaryDocument ?? null,
        ])

        @if ($primary?->fill_secondary_detail == 'yes')
            @include('customer-booking.partials.document-form', [
                'prefix' => 'secondary_',
                'title' => 'Secondary Applicant Documents',
                'document' => $customer->secondaryDocument ?? null,
            ])
        @endif

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('customer-booking.edit', [$customer->id, 'step' => 2]) }}"
                class="btn btn-outline-secondary px-4">
                Previous
            </a>

            <button type="submit" class="btn btn-success ms-2 px-4">
                Save & Next
            </button>
        </div>
    </form>
@endif

@push('scripts')
    <script>
        $(document).ready(function() {

            function toggleDocumentBox(checkbox, animate = false) {
                let target = $(checkbox).data('target');
                let box = $('#' + target);
                let card = $(checkbox).closest('.document-card');

                if ($(checkbox).is(':checked')) {
                    card.removeClass('border-light').addClass('border-success shadow-sm');

                    if (animate) {
                        box.stop(true, true).slideDown(250);
                    } else {
                        box.show();
                    }
                } else {
                    card.removeClass('border-success shadow-sm').addClass('border-light');

                    if (animate) {
                        box.stop(true, true).slideUp(250);
                    } else {
                        box.hide();
                    }

                    box.find('input[type="file"]').val('');
                }
            }

            $('.doc-check').each(function() {
                toggleDocumentBox(this, false);
            });

            $(document).on('change', '.doc-check', function() {
                toggleDocumentBox(this, true);
            });

        });
    </script>
@endpush