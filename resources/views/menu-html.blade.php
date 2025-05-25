@php
	$currentUrl = url()->current();
@endphp

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link href="{{ nguyendachuy_menu_asset('style.css') }}" rel="stylesheet">

<!-- Translations for JavaScript -->
<script id="menu-translations" type="application/json" data-translations='@json(__('menu'))'></script>

<!-- Top loading bar -->
<div id="ajax_loader" class="fixed top-0 left-0 right-0 h-1 bg-blue-500 transform scale-x-0 origin-left transition-transform duration-300 z-50"></div>

<div id="nguyen-huy" class="bg-white shadow rounded-lg border border-gray-200 mb-6">
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center">
        <form method="GET" action="{{ $currentUrl }}" class="flex flex-wrap items-center">
            <label for="menu-selector" class="mr-2">{{ __('menu.select_menu') }}: </label>
            {!! Menu::select('menu', $menulist, ['class' => 'border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500', 'id' => 'menu-selector']) !!}
            <button type="submit" class="ml-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-300">{{ __('menu.save_menu') }}</button>
            <div class="ml-4">
                or <a href="{{ $currentUrl }}?action=edit&menu=0" class="text-blue-600 hover:underline">{{ __('menu.create_new_menu') }}</a>
            </div>
        </form>
    </div>
    
    @if(isset($error))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 m-3 rounded">
            {{ $error }}
        </div>
    @endif

    <div class="p-5">
        <input type="hidden" id="idmenu" value="{{$indmenu->id ?? null}}"/>
        <div class="flex flex-wrap -mx-4">
            <div class="w-full md:w-1/3 px-4">
                @include('nguyendachuy-menu::partials.left')
            </div>
            <div class="w-full md:w-2/3 px-4">
                @if(isset($indmenu))
                    @include('nguyendachuy-menu::partials.right')
                @elseif(!isset($error))
                    <div class="bg-white shadow rounded border border-gray-200">
                        <div class="p-5">
                            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
                                {{ __('menu.select_menu') }} {{ __('menu.or') }} {{ __('menu.create_new_menu') }}.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>