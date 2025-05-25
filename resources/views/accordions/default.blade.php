<div class="bg-white shadow rounded border border-gray-200  mt-6">
    <!-- Accordion header -->
    <div 
        class="px-4 py-3 bg-gray-50 border-b border-gray-200 collapse-header flex justify-between items-center {{ $show ?? false ? 'expanded' : '' }}"
        data-toggle="collapse" 
        data-target="#collapse-{{ Str::slug($name) }}"
        aria-expanded="{{ $show ?? false ? 'true' : 'false' }}"
    >
        <h5 class="font-medium text-gray-700">{{ $name }}</h5>
    </div>
    
    <!-- Accordion content -->
    <div id="collapse-{{ Str::slug($name) }}" class="{{ $show ?? false ? 'block' : 'hidden' }}">
        <div class="p-4">
            @if(count($urls))
                <form id="add-page">
                    <div class="mb-4">
                        @foreach($urls as $key => $item)
                            <div class="flex items-center mb-2">
                                <input 
                                    type="checkbox" 
                                    name="menu_id" 
                                    value="{{ $key }}" 
                                    class="mr-2" 
                                    data-icon="{{ $item['icon'] ?? '' }}" 
                                    data-label="{{ $item['label'] }}" 
                                    data-url="{{ $item['url'] }}"
                                >
                                <label>{{ $item['label'] }}</label>
                            </div>
                        @endforeach
                    </div>
                    
                    @if(config('menu.use_roles'))
                        <div class="mb-4">
                            <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('menu.role') }}</label>
                            <select name="role" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="0">{{ __('menu.select_role') }}</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->$role_pk }}">{{ ucfirst($role->$role_title_field) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    <button 
                        type="button" 
                        onclick="addItemMenu(this, 'custom');" 
                        class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded focus:outline-none focus:ring-2 focus:ring-blue-300"
                    >
                        {{ __('menu.add_to_menu') }}
                    </button>
                </form>
            @else
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                    {{ __('menu.no_items_available') }}
                </div>
            @endif
        </div>
    </div>
</div>