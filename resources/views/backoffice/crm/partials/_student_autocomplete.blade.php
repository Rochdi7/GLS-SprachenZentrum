{{--
    Wires every .crm-student-ac widget on the current page to the JSON student
    search endpoint. Included once per page that uses the autocomplete.

    The partial is self-protecting with @once so it's safe to include from
    multiple parents.

    CSS and JS extracted to public/assets/{css,js}/backoffice/. The endpoint
    URL still needs Blade resolution, so it's injected via a window global
    before the external script loads.
--}}
@once
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice/crm-student-autocomplete.css') }}">
    <script>
        window.CRM_STUDENT_SEARCH_URL = @json(route('backoffice.crm.api.students-search'));
    </script>
    <script src="{{ asset('assets/js/backoffice/crm-student-autocomplete.js') }}" defer></script>
@endonce
