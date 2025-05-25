<div class="bg-white shadow rounded border border-gray-200 mb-4">
    <!-- Accordion header -->
    <div 
        class="px-4 py-3 bg-gray-50 border-b border-gray-200 collapse-header"
        data-toggle="collapse" 
        data-target="#collapse-{{ Str::slug($name) }}"
        aria-expanded="false"
    >
        <h5 class="font-medium text-gray-700">{{ $name }}</h5>
    </div>
    
    <!-- Accordion content -->
    <div id="collapse-{{ Str::slug($name) }}" class="hidden">
        <div class="p-4">
            <form id="add-custom-link">
                <div class="mb-4">
                    <label for="custom-label" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.navigation_label') }}</label>
                    <input type="text" id="custom-label" name="label" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label for="custom-url" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.url') }}</label>
                    <input type="text" id="custom-url" name="url" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="http://">
                </div>
                <div class="mb-4">
                    <label for="custom-icon" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.custom_class') }} ({{ __('menu.none') }})</label>
                    <input type="text" id="custom-icon" name="icon" placeholder="fa fa-example" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                @if(config('menu.use_roles'))
                <div class="mb-4">
                    <label for="custom-role" class="block mb-2 text-sm font-medium text-gray-700">{{ __('menu.role') }}</label>
                    <select id="custom-role" name="role" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="0">{{ __('menu.select_role') }}</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->$role_pk }}">{{ ucfirst($role->$role_title_field) }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <button 
                    type="button" 
                    onclick="addItemMenu(this, 'default');" 
                    class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded focus:outline-none focus:ring-2 focus:ring-blue-300"
                >
                    {{ __('menu.add_to_menu') }}
                </button>
            </form>
        </div>
    </div>
</div>