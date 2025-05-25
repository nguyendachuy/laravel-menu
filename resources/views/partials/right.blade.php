<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                @if(request()->has('menu') && request()->input('menu') == '0')
                    <div class="bg-white shadow rounded border border-gray-200">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <h5 class="font-medium text-gray-700">{{ __('menu.menu_structure') }}</h5>
                        </div>
                        <div class="p-5">
                            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
                                {{ __('menu.please_create_new_menu') }}
                            </div>
                        </div>
                    </div>
                @elseif(isset($indmenu))
                    <div class="bg-white shadow rounded border border-gray-200">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                            <h5 class="font-medium text-gray-700">{{ __('menu.menu_structure') }}</h5>
                            
                            <button type="button" onclick="deleteMenu();" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-sm rounded focus:outline-none focus:ring-2 focus:ring-red-300">
                                {{ __('menu.delete_menu') }}
                            </button>
                        </div>
                        <div class="p-5">
                            <div class="mb-4 flex space-x-4">
                                <div class="w-1/2">
                                    <label for="menu-name" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.menu_name') }}</label>
                                    <input type="text" id="menu-name" name="menu-name" value="{{$indmenu->name}}" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="w-1/2">
                                    <label for="menu-class" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.menu_class') }}</label>
                                    <input type="text" id="menu-class" name="menu-class" value="{{$indmenu->class ?? ''}}" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="mt-6 mb-4">
                                <p class="text-sm text-gray-600 mb-4">{{ __('menu.drag_drop_instruction') }}</p>
                                
                                <div class="dd" id="nestable">
                                    <ol class="dd-list">
                                        @if(isset($menus) && count($menus) > 0)
                                            @foreach($menus as $m)
                                                @include('nguyendachuy-menu::partials.loop-item', ['item' => $m])
                                            @endforeach
                                        @else
                                            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                                                {{ __('menu.no_menu_items') }}
                                            </div>
                                        @endif
                                    </ol>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <button type="button" onclick="updateItem();" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded focus:outline-none focus:ring-2 focus:ring-green-300 mr-2">
                                    {{ __('menu.update_menu') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>