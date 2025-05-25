<li class="dd-item" data-id="{{$item['id']}}" data-depth="{{isset($depth) ? $depth : 0}}" data-parent="{{$item['parent'] ?? 0}}">
    <div class="dd-handle flex items-center">
        <span class="menu-item-title">{{$item['label']}}</span>
        <span class="menu-item-link text-gray-500 ml-2 hidden md:inline">({{Str::limit($item['link'], 30)}})</span>
        <span class="menu-item-depth text-gray-500 text-xs">(Depth: {{ isset($depth) ? $depth : 0 }})</span>
        <div class="ml-auto dd-nodrag">
            <!-- Added dd-nodrag class to prevent drag behavior -->
            <button type="button" class="edit-button text-blue-500 hover:text-blue-700 font-medium focus:outline-none" data-item-id="{{$item['id']}}">
                <i class="fa fa-pencil"></i> {{ __('menu.edit') }}
            </button>
            <button type="button" onclick="deleteItem({{$item['id']}});" class="text-red-500 hover:text-red-700 font-medium ml-2 focus:outline-none">
                <i class="fa fa-times"></i> {{ __('menu.delete') }}
            </button>
        </div>
    </div>

    <div class="menu-item-settings bg-gray-50 border border-gray-200 p-4 mt-1 mb-3 rounded hidden" id="settings-{{$item['id']}}">
        <div class="mb-3">
            <label class="block mb-1 text-sm font-medium text-gray-700" for="label-menu-{{$item['id']}}">{{ __('menu.navigation_label') }}</label>
            <input type="text" id="label-menu-{{$item['id']}}" value="{{$item['label']}}" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 edit-menu-item-title">
        </div>

        <div class="mb-3">
            <label class="block mb-1 text-sm font-medium text-gray-700" for="url-menu-{{$item['id']}}">{{ __('menu.url') }}</label>
            <input type="text" id="url-menu-{{$item['id']}}" value="{{$item['link']}}" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 edit-menu-item-url">
        </div>

        <div class="mb-3">
            <label class="block mb-1 text-sm font-medium text-gray-700" for="clases-menu-{{$item['id']}}">{{ __('menu.css_class') }}</label>
            <input type="text" id="clases-menu-{{$item['id']}}" value="{{$item['class'] ?? ''}}" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 edit-menu-item-classes">
        </div>

        <div class="mb-3">
            <label class="block mb-1 text-sm font-medium text-gray-700" for="icon-menu-{{$item['id']}}">{{ __('menu.icon_class') }}</label>
            <input type="text" id="icon-menu-{{$item['id']}}" value="{{$item['icon'] ?? ''}}" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 edit-menu-item-icon">
        </div>

        <div class="mb-3">
            <label class="block mb-1 text-sm font-medium text-gray-700" for="target-menu-{{$item['id']}}">{{ __('menu.link_target') }}</label>
            <select id="target-menu-{{$item['id']}}" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 edit-menu-item-target">
                <option value="_self" {{($item['target'] ?? '_self') == '_self' ? 'selected' : ''}}>{{ __('menu.same_window') }}</option>
                <option value="_blank" {{($item['target'] ?? '_self') == '_blank' ? 'selected' : ''}}>{{ __('menu.new_window') }}</option>
            </select>
        </div>

        <div class="mb-3">
            <div class="flex items-center">
                <input type="checkbox" id="is-mega-menu-{{$item['id']}}" class="mr-2 edit-menu-item-mega" {{($item['is_mega_menu'] ?? false) ? 'checked' : ''}}>
                <label class="text-sm font-medium text-gray-700" for="is-mega-menu-{{$item['id']}}">{{ __('menu.enable_mega_menu') }}</label>
            </div>
            <p class="text-xs text-gray-500 mt-1">{{ __('menu.mega_menu_description') }}</p>
        </div>

        <div class="mb-3 mega-menu-content-container {{($item['is_mega_menu'] ?? false) ? '' : 'hidden'}}" id="mega-menu-content-{{$item['id']}}">
            <label class="block mb-1 text-sm font-medium text-gray-700" for="mega-menu-content-{{$item['id']}}">{{ __('menu.mega_menu_content') }}</label>
            <textarea id="mega-menu-content-{{$item['id']}}" rows="5" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 edit-menu-item-mega-content wysiwyg-editor">{{$item['mega_menu_content'] ?? ''}}</textarea>
            <p class="text-xs text-gray-500 mt-1">{{ __('menu.mega_menu_content_help') }}</p>
        </div>

        @if(config('menu.use_roles'))
        <div class="mb-3">
            <label class="block mb-1 text-sm font-medium text-gray-700" for="role_menu_{{$item['id']}}">Role</label>
            <select id="role_menu_{{$item['id']}}" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 edit-menu-item-role">
                <option value="0">Select Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->$role_pk }}" {{$item['role_id'] == $role->$role_pk ? 'selected' : ''}}>
                        {{ ucfirst($role->$role_title_field) }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <input type="hidden" class="edit-menu-item-depth" value="{{isset($depth) ? $depth : 0}}">
        <input type="hidden" class="edit-menu-item-parent" value="{{$item['parent'] ?? 0}}">
        <input type="hidden" id="id-menu-{{$item['id']}}" value="{{$item['id']}}" class="edit-menu-item-id">

        <div class="mt-4 text-right">
            <button type="button" onclick="updateItem({{$item['id']}});" class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded focus:outline-none focus:ring-2 focus:ring-blue-300">
                {{ __('menu.save_item') }}
            </button>
            <button type="button" class="cancel-button px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm rounded ml-2 focus:outline-none" data-item-id="{{$item['id']}}">
                Cancel
            </button>
        </div>
    </div>

    @if(isset($item['child']) && count($item['child']) > 0)
        <ol class="dd-list">
            @foreach($item['child'] as $child)
                @include('nguyendachuy-menu::partials.loop-item', ['item' => $child, 'depth' => (isset($depth) ? $depth + 1 : 1)])
            @endforeach
        </ol>
    @endif
</li>
