<?php

namespace NguyenHuy\Menu\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;
use NguyenHuy\Menu\Events\CreatedMenuEvent;
use NguyenHuy\Menu\Events\DestroyMenuEvent;
use NguyenHuy\Menu\Events\UpdatedMenuEvent;
use NguyenHuy\Menu\Http\Requests\CreateMenuRequest;
use NguyenHuy\Menu\Http\Requests\CreateMenuItemRequest;
use NguyenHuy\Menu\Http\Requests\UpdateMenuRequest;
use NguyenHuy\Menu\Http\Requests\UpdateMenuItemRequest;
use NguyenHuy\Menu\Repositories\MenuRepository;
use NguyenHuy\Menu\Models\Menus;
use NguyenHuy\Menu\Models\MenuItems;

class MenuController extends Controller
{
    protected $menuRepository;

    public function __construct(MenuRepository $menuRepository)
    {
        $this->menuRepository = $menuRepository;
    }

    /**
     * Create a new menu
     * 
     * @param CreateMenuRequest $request
     * @return JsonResponse
     */
    public function createNewMenu(CreateMenuRequest $request): JsonResponse
    {
        $menu = $this->menuRepository->createMenu($request->validated());
        
        event(new CreatedMenuEvent($menu));

        return response()->json([
            'resp' => $menu->id,
            'message' => Lang::get('menu.menu_created')
        ], 200);
    }

    /**
     * Delete a menu
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function destroyMenu(Request $request): JsonResponse
    {
        $menu = Menus::findOrFail($request->input('id'));
        $this->menuRepository->deleteMenu($menu);

        event(new DestroyMenuEvent($menu));

        return response()->json([
            'resp' => 'Menu deleted successfully'
        ], 200);
    }

    /**
     * Update menu and its items
     * 
     * @param UpdateMenuRequest $request
     * @return JsonResponse
     */
    public function generateMenuControl(UpdateMenuRequest $request): JsonResponse
    {
        $menu = $this->menuRepository->updateMenu($request->input('idMenu'), [
            'name' => $request->input('menuName'),
            'class' => $request->input('class')
        ]);
        
        if (is_array($request->input('data'))) {
            // Pass the complete data structure with depth information
            $this->menuRepository->updateMenuItems($request->input('data'));
        }
        
        event(new UpdatedMenuEvent($menu));
        
        return response()->json([
            'resp' => 1,
            'message' => 'Menu updated successfully'
        ], 200);
    }

    /**
     * Create menu items
     * 
     * @param CreateMenuItemRequest $request
     * @return JsonResponse
     */
    public function createItem(CreateMenuItemRequest $request): JsonResponse
    {
        if ($request->has('data')) {
            $this->menuRepository->createMenuItems($request->input('data'));
        }

        return response()->json([
            'resp' => 1,
            'message' => 'Menu items created successfully'
        ], 200);
    }

    /**
     * Update menu item
     * 
     * @param UpdateMenuItemRequest $request
     * @return JsonResponse
     */
    public function updateItem(UpdateMenuItemRequest $request): JsonResponse
    {
        $dataItem = $request->input('dataItem');
        
        // Check if menu information is provided
        if ($request->has('menuName') && $request->has('idMenu')) {
            // Update menu name and class
            $this->menuRepository->updateMenu($request->input('idMenu'), [
                'name' => $request->input('menuName'),
                'class' => $request->input('menuClass', '')
            ]);
        }
        
        // Update menu items
        if (is_array($dataItem)) {
            $this->menuRepository->bulkUpdateMenuItems($dataItem);
        } else {
            $this->menuRepository->updateMenuItem($request->except('_token'));
        }

        event(new UpdatedMenuEvent($dataItem ?? $request->except('_token')));

        return response()->json([
            'resp' => 1,
            'message' => Lang::get('menu.menu_updated')
        ], 200);
    }

    /**
     * Delete menu item
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function destroyItem(Request $request): JsonResponse
    {
        $this->menuRepository->deleteMenuItem($request->input('id'));

        return response()->json([
            'resp' => 1,
            'message' => 'Menu item deleted successfully'
        ], 200);
    }
    
    /**
     * Reorder menu items
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function reorderItems(Request $request): JsonResponse
    {
        if ($request->has('items') && is_array($request->input('items'))) {
            $items = $request->input('items');
            
            // Process each item to update its sort order, parent, and depth
            foreach ($items as $item) {
                if (isset($item['id']) && isset($item['sort'])) {
                    $updateData = [
                        'id' => $item['id'],
                        'sort' => $item['sort']
                    ];
                    
                    // Add parent if provided
                    if (isset($item['parent'])) {
                        $updateData['parent'] = $item['parent'];
                    }
                    
                    // Add depth if provided
                    if (isset($item['depth'])) {
                        $updateData['depth'] = $item['depth'];
                    }
                    
                    // Update the item
                    $this->menuRepository->updateMenuItem($updateData);
                }
            }
            
            return response()->json([
                'resp' => 1,
                'message' => 'Menu items reordered successfully'
            ], 200);
        }
        
        return response()->json([
            'resp' => 0,
            'message' => 'Invalid data provided'
        ], 400);
    }
}
