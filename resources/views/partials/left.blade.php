@if (request()->has('menu') && request()->input('menu') == '0')
    <!-- Create Menu form -->
    <div class="bg-white shadow rounded border border-gray-200">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h5 class="font-medium text-gray-700">{{ __('menu.create_new_menu') }}</h5>
        </div>
        <div class="p-4">
            <div class="mb-4">
                <label for="menu-name" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.menu_name') }}</label>
                <input type="text" id="menu-name" name="menu-name"
                    class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="menu-class" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.menu_class') }}</label>
                <input type="text" id="menu-class" name="menu-class"
                    class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <button type="button" onclick="createNewMenu();"
                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded focus:outline-none focus:ring-2 focus:ring-blue-300">
                    {{ __('menu.create_menu') }}
                </button>
            </div>
        </div>
    </div>
@elseif(isset($indmenu))
    <!-- Add Items form -->
    <div class="bg-white shadow rounded border border-gray-200">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h5 class="font-medium text-gray-700">{{ __('menu.add_menu_items') }}</h5>
        </div>
        <div class="p-4">
            <form id="form-add-item" method="post">
                <div class="mb-4">
                    <label for="label" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.label') }}</label>
                    <input type="text" id="label" name="label"
                        class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label for="url" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.url') }}</label>
                    <input type="text" id="url" name="url"
                        class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        value="http://">
                </div>
                <div class="mb-4">
                    <label for="icon" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.icon_class') }}</label>
                    <input type="text" id="icon" name="icon" placeholder="fa fa-example"
                        class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                @if (config('menu.use_roles'))
                    <div class="mb-4">
                        <label for="role" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.role') }}</label>
                        <select id="role" name="role"
                            class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="0">{{ __('menu.select_role') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->$role_pk }}">{{ ucfirst($role->$role_title_field) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <button type="button" onclick="addItemMenu(this, 'default');"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded focus:outline-none focus:ring-2 focus:ring-blue-300">
                        {{ __('menu.add_to_menu') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="accordion-left">
        {{-- Display all registered menu sections --}}
        @if (isset($menuSections) && count($menuSections) > 0)
            @foreach ($menuSections as $section)
                @include('nguyendachuy-menu::accordions.default', [
                    'name' => $section['name'],
                    'urls' => $section['items'],
                    'show' => $section['show'] ?? false,
                ])
            @endforeach
        @endif

    </div>
@endif
