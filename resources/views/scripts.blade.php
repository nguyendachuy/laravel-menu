@php
    $prefix = config('menu.prefix', 'ndhuy-menu-');
    $jsNamespace = config('menu.js_namespace', 'NDHuyMenu');
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Set up URL routes for AJAX operations
    var URL_CREATE_ITEM_MENU = "{{ route('h-menu.add-item') }}";
    var URL_DELETE_ITEM_MENU = "{{ route('h-menu.delete-item') }}";
    var URL_UPDATE_ITEM_MENU = "{{ route('h-menu.update-item') }}";
    var URL_REORDER_ITEMS = "{{ route('h-menu.reorder-items') }}";
    var URL_CREATE_MENU = "{{ route('h-menu.create-menu') }}";
    var URL_UPDATE_ITEMS_AND_MENU = "{{ route('h-menu.update-menu-and-items') }}";
    var URL_DELETE_MENU = "{{ route('h-menu.delete-menu') }}";
    var URL_CURRENT = "{{ url()->current() }}";
    var URL_FULL = "{{ request()->fullUrl() }}";

    // Set up AJAX with CSRF protection
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
</script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/nestable2@1.6.0/jquery.nestable.min.js"></script>
<script type="text/javascript" src="{{ nguyendachuy_menu_asset('menu.js') }}"></script>