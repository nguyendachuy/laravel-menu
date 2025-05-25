/**
 * Laravel Menu Manager - Enhanced JavaScript Implementation
 * Uses jQuery for compatibility with Nestable2
 */

// Translations object - will be populated from the backend
let translations = {};

/**
 * Get a translated string
 * @param {string} key - Translation key
 * @param {object} replacements - Optional replacements
 * @returns {string} - Translated string
 */
function __(key, replacements = {}) {
    // Remove 'menu.' prefix if present
    const actualKey = key.startsWith('menu.') ? key.substring(5) : key;
    
    // Get translation or fallback to key
    let string = translations[actualKey] || key;
    
    // Replace placeholders
    Object.keys(replacements).forEach(placeholder => {
        string = string.replace(`:${placeholder}`, replacements[placeholder]);
    });
    
    return string;
}

// Utilities
const MenuUtils = {
    /**
     * Show the loading indicator
     */
    showLoader() {
        const loader = $('#ajax_loader');
        loader.removeClass('loaded').addClass('loading');
    },

    /**
     * Hide the loading indicator
     */
    hideLoader() {
        const loader = $('#ajax_loader');
        loader.removeClass('loading').addClass('loaded');
        
        // Reset the loader state after animation completes
        setTimeout(() => {
            loader.removeClass('loaded').css('transform', 'scaleX(0)');
        }, 800);
    },

    /**
     * Show notification message
     * @param {string} message - Message to display
     * @param {string} type - Notification type (success, error, warning, info)
     * @param {number} duration - Duration in milliseconds
     */
    showNotification(message, type = 'info', duration = 3000) {
        // Remove existing notification if present
        $('.menu-notification').remove();
        
        // Create notification markup
        const iconMap = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };
        const icon = iconMap[type] || 'ℹ';
        
        const notification = $(`
            <div class="menu-notification ${type}">
                <span class="notification-icon">${icon}</span>
                ${message}
                <span class="notification-close">×</span>
            </div>
        `);
        
        // Add to body
        $('body').append(notification);
        
        // Handle close button
        notification.find('.notification-close').on('click', function() {
            notification.addClass('fade-out');
            setTimeout(() => notification.remove(), 300);
        });
        
        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => {
                notification.addClass('fade-out');
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }
        
        return notification;
    },
    
    /**
     * Show confirmation dialog
     * @param {string} message - Confirmation message
     * @returns {Promise} - Promise that resolves to boolean (true if confirmed)
     */
    showConfirmation(message) {
        return new Promise((resolve) => {
            // Create overlay and dialog
            const overlay = $(`
                <div class="menu-overlay">
                    <div class="menu-confirmation">
                        <div class="confirmation-message">${message}</div>
                        <div class="confirmation-buttons">
                            <button class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded text-sm confirm-yes">${__('menu.yes')}</button>
                            <button class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-800 border border-gray-300 rounded text-sm confirm-no">${__('menu.no')}</button>
                        </div>
                    </div>
                </div>
            `);
            
            // Add to body
            $('body').append(overlay);
            
            // Set up event handlers
            overlay.find('.confirm-yes').on('click', function() {
                overlay.remove();
                resolve(true);
            });
            
            overlay.find('.confirm-no').on('click', function() {
                overlay.remove();
                resolve(false);
            });
            
            // Focus confirm button
            overlay.find('.confirm-yes').focus();
        });
    }
};

/**
 * Add a new menu item
 * @param {HTMLElement} button - Button element that triggered the action
 * @param {string} type - Type of item to add ('default' or 'custom')
 */
function addItemMenu(button, type) {
    const form = $(button).closest('form');
    const data = [];
    
    if (type == "default") {
        const labelInput = form.find('input[name="label"]');
        const urlInput = form.find('input[name="url"]');
        const iconInput = form.find('input[name="icon"]');
        const roleSelect = form.find('select[name="role"]');
        
        if (!labelInput.val() || !urlInput.val()) {
            MenuUtils.showNotification('Please enter label and URL', 'error');
            return;
        }
        
        data.push({
            label: labelInput.val(),
            url: urlInput.val(),
            role: roleSelect.length ? roleSelect.val() : null,
            icon: iconInput.length ? iconInput.val() : null,
            id: $('#idmenu').val()
        });
    } else {
        const checkboxes = form.find('input[name="menu_id"]:checked');
        let hasError = false;
        
        if (checkboxes.length === 0) {
            MenuUtils.showNotification('Please select at least one item', 'warning');
            return;
        }
        
        checkboxes.each(function() {
            const checkbox = $(this);
            const item = {
                label: checkbox.data('label'),
                url: checkbox.data('url'),
                role: form.find('select[name="role"]').length ? form.find('select[name="role"]').val() : null,
                icon: checkbox.data('icon') || null,
                id: $('#idmenu').val()
            };
            
            if (!item.label || !item.url) {
                hasError = true;
            }
            
            data.push(item);
        });
        
        if (hasError) {
            MenuUtils.showNotification('Please ensure all selected items have a label and URL', 'error');
            return;
        }
    }
    
    $.ajax({
        data: { data: data },
        url: URL_CREATE_ITEM_MENU,
        type: 'POST',
        beforeSend: function() {
            MenuUtils.showLoader();
        },
        success: function() {
            MenuUtils.showNotification('Menu item(s) added successfully. Reloading...', 'success');
            setTimeout(() => window.location.reload(), 1000);
        },
        error: function(error) {
            console.error('Error adding menu item:', error);
            MenuUtils.showNotification('Error adding menu item. Please try again.', 'error');
        },
        complete: function() {
            MenuUtils.hideLoader();
        }
    });
}

/**
 * Update a specific menu item or all items
 * @param {number|null} id - ID of the item to update, or null to update all
 */
function updateItem(id = 0) {
    if (id) {
        // Update single item
        const labelInput = $('#label-menu-' + id);
        const clasesInput = $('#clases-menu-' + id);
        const urlInput = $('#url-menu-' + id);
        const iconInput = $('#icon-menu-' + id);
        const targetSelect = $('#target-menu-' + id);
        const roleSelect = $('#role_menu_' + id);
        
        if (!labelInput.val() || !urlInput.val()) {
            MenuUtils.showNotification('Please enter label and URL', 'error');
            return;
        }
        
        // Get the item's current depth from its position in the DOM
        const $item = $('#settings-' + id).closest('.dd-item');
        const depth = $item.parents('.dd-list').length;
        
        // Get the parent item ID, if any
        const $parentItem = $item.closest('.dd-list').closest('.dd-item');
        const parentId = $parentItem.length ? $parentItem.data('id') || 0 : 0;
        
        // Calculate the sort order based on the item's position among its siblings
        const siblings = $item.parent().children('.dd-item');
        const sortOrder = siblings.index($item);
        
        // Check if mega menu is enabled
        const isMegaMenu = $('#is-mega-menu-' + id).is(':checked');
        const megaMenuContent = $('#mega-menu-content-' + id).val();
        
        const data = {
            id: id,
            label: labelInput.val(),
            clases: clasesInput.val(),
            url: urlInput.val(),
            icon: iconInput.val(),
            target: targetSelect.val(),
            depth: depth, // Add the depth information
            parent: parentId, // Add parent ID
            sort: sortOrder, // Add sort order
            role_id: roleSelect.length ? roleSelect.val() : 0,
            is_mega_menu: isMegaMenu ? 1 : 0,
            mega_menu_content: megaMenuContent
        };
        
        $.ajax({
            data: data,
            url: URL_UPDATE_ITEM_MENU,
            type: 'POST',
            beforeSend: function() {
                MenuUtils.showLoader();
            },
            success: function() {
                MenuUtils.showNotification('Item updated successfully', 'success');
            },
            error: function(error) {
                console.error('Error updating menu item:', error);
                MenuUtils.showNotification('Error updating menu item. Please try again.', 'error');
            },
            complete: function() {
                MenuUtils.hideLoader();
            }
        });
    } else {
        // Update all items
        const menuItems = $('.menu-item-settings');
        const data = { 
            dataItem: [],
            menuName: $('#menu-name').val(),
            menuClass: $('#menu-class').val(),
            idMenu: $('#idmenu').val()
        };
        let hasError = false;
        let errorItems = [];
        
        menuItems.each(function() {
            const item = $(this);
            const id = item.find('.edit-menu-item-id').val();
            const label = item.find('.edit-menu-item-title').val();
            const clases = item.find('.edit-menu-item-classes').val();
            const url = item.find('.edit-menu-item-url').val();
            const icon = item.find('.edit-menu-item-icon').val();
            const roleSelect = item.find('.edit-menu-item-role');
            const target = item.find('select.edit-menu-item-target option:selected').val();
            
            // Get the item's current depth from its position in the DOM
            const $ddItem = item.closest('.dd-item');
            const depth = $ddItem.parents('.dd-list').length;
            const $parentItem = $ddItem.closest('.dd-list').closest('.dd-item');
            const parentId = $parentItem.length ? $parentItem.data('id') || 0 : 0;
            
            if (!label || !url) {
                hasError = true;
                errorItems.push(id);
                return;
            }
            
            data.dataItem.push({
                id: id,
                label: label,
                class: clases,
                link: url,
                icon: icon,
                target: target,
                depth: depth, // Add depth information
                parent: parentId, // Add parent ID
                role_id: roleSelect.length ? roleSelect.val() : 0
            });
        });
        
        if (hasError) {
            const errorMessage = `Please ensure all items have a label and URL. Check items: ${errorItems.join(', ')}`;
            MenuUtils.showNotification(errorMessage, 'error', 5000);
            return;
        }
        
        $.ajax({
            data: data,
            url: URL_UPDATE_ITEM_MENU,
            type: 'POST',
            beforeSend: function() {
                MenuUtils.showLoader();
            },
            success: function() {
                MenuUtils.showNotification('All items updated successfully', 'success');
            },
            error: function(error) {
                console.error('Error updating menu items:', error);
                MenuUtils.showNotification('Error updating menu items. Please try again.', 'error');
            },
            complete: function() {
                MenuUtils.hideLoader();
            }
        });
    }
}

/**
 * Delete a menu item
 * @param {number} id - ID of the item to delete
 */
async function deleteItem(id) {
    const confirmed = await MenuUtils.showConfirmation(__('menu.confirm_delete_menu_item'));
    
    if (confirmed) {
        $.ajax({
            data: { id: id },
            url: URL_DELETE_ITEM_MENU,
            type: 'POST',
            beforeSend: function() {
                MenuUtils.showLoader();
            },
            success: function() {
                MenuUtils.showNotification('Menu item deleted successfully. Reloading...', 'success');
                setTimeout(() => window.location.href = URL_FULL, 1000);
            },
            error: function(error) {
                console.error('Error deleting menu item:', error);
                MenuUtils.showNotification('Error deleting menu item. Please try again.', 'error');
            },
            complete: function() {
                MenuUtils.hideLoader();
            }
        });
    }
}

/**
 * Update menu structure
 * @param {Array|boolean} serialize - Serialized menu data or false
 */
function actualizarMenu(serialize) {
    const menuName = $('#menu-name').val();
    
    if (!menuName) {
        MenuUtils.showNotification('Please enter a menu name', 'warning');
        return;
    }
    
    // If no serialized data is provided, get it from the Nestable plugin
    if (!serialize) {
        serialize = $('#nestable').nestable('serialize');
    }
    
    // Process the serialized data to ensure depth information is included
    if (Array.isArray(serialize)) {
        serialize = processItemsDepth(serialize);
    }
    
    const data = {
        data: serialize,
        menuName: menuName,
        idMenu: $('#idmenu').val(),
        class: $('#menu-class').length ? $('#menu-class').val() : ''
    };
    
    $.ajax({
        dataType: 'json',
        data: data,
        url: URL_UPDATE_ITEMS_AND_MENU,
        type: 'POST',
        beforeSend: function() {
            MenuUtils.showLoader();
        },
        success: function(response) {
            // Update dropdown text
            $(`select[name="menu"] option[value="${$('#idmenu').val()}"]`).text(menuName);
            
            // Ensure the input fields reflect the current values
            $('#menu-name').val(menuName);
            $('#menu-class').val(data.class);
            
            MenuUtils.showNotification(__("menu.menu_updated"), 'success');
        },
        error: function(error) {
            console.error('Error updating menu structure:', error);
            MenuUtils.showNotification('Error updating menu structure. Please try again.', 'error');
        },
        complete: function() {
            MenuUtils.hideLoader();
        }
    });
}

/**
 * Process items to add depth information
 * @param {Array} items - Array of menu items
 * @param {number} depth - Current depth level
 * @returns {Array} - Processed items with depth information
 */
function processItemsDepth(items, depth = 0) {
    return items.map(item => {
        // Add depth information to the item
        item.depth = depth + 1;
        
        // Process children recursively if they exist
        if (item.children && Array.isArray(item.children) && item.children.length > 0) {
            item.children = processItemsDepth(item.children, item.depth);
        }
        
        return item;
    });
}

/**
 * Create a new menu
 */
function createNewMenu() {
    const menuName = $('#menu-name').val();
    
    if (!menuName) {
        MenuUtils.showNotification('Please enter a menu name', 'warning');
        $('#menu-name').focus();
        return;
    }
    
    const menuClass = $('#menu-class').length ? $('#menu-class').val() : '';
    
    $.ajax({
        dataType: 'json',
        data: { 
            name: menuName,
            class: menuClass
        },
        url: URL_CREATE_MENU,
        type: 'POST',
        beforeSend: function() {
            MenuUtils.showLoader();
        },
        success: function(response) {
            MenuUtils.showNotification(__('menu.menu_created') + '. ' + __('menu.redirecting') + '...', 'success');
            setTimeout(() => {
                window.location.href = `${URL_CURRENT}?menu=${response.resp}`;
            }, 1000);
        },
        error: function(error) {
            console.error('Error creating menu:', error);
            MenuUtils.showNotification(__('menu.error_occurred') + '. ' + __('menu.please_try_again'), 'error');
        },
        complete: function() {
            MenuUtils.hideLoader();
        }
    });
}

/**
 * Delete the current menu
 */
async function deleteMenu() {
    const confirmed = await MenuUtils.showConfirmation(__('menu.confirm_delete_menu'));
    
    if (confirmed) {
        $.ajax({
            dataType: 'json',
            data: { id: $('#idmenu').val() },
            url: URL_DELETE_MENU,
            type: 'POST',
            beforeSend: function() {
                MenuUtils.showLoader();
            },
            success: function(response) {
                if (!response.error) {
                    MenuUtils.showNotification(response.resp, 'success');
                    setTimeout(() => window.location.href = URL_CURRENT, 1000);
                } else {
                    MenuUtils.showNotification(response.resp, 'error');
                }
            },
            error: function(error) {
                console.error('Error deleting menu:', error);
                MenuUtils.showNotification(__('menu.error_occurred') + '. ' + __('menu.please_try_again'), 'error');
            },
            complete: function() {
                MenuUtils.hideLoader();
            }
        });
    }
}

// Toggle mega menu content visibility when checkbox is clicked
function toggleMegaMenuContent() {
    $(document).on('change', '[id^="is-mega-menu-"]', function() {
        const id = $(this).attr('id').replace('is-mega-menu-', '');
        const contentContainer = $('#mega-menu-content-' + id).closest('.mega-menu-content-container');
        
        if ($(this).is(':checked')) {
            contentContainer.removeClass('hidden');
        } else {
            contentContainer.addClass('hidden');
        }
    });
}

// Initialize when document is ready
$(document).ready(function() {
    // Load translations from global variable or data attribute
    if (window.NDHuyMenu && window.NDHuyMenu.translations) {
        // First priority: use translations from global variable
        translations = window.NDHuyMenu.translations;
        console.log('Translations loaded from global variable:', translations);
    } else if ($('#menu-translations').length) {
        // Second priority: use translations from data attribute
        try {
            translations = JSON.parse($('#menu-translations').attr('data-translations'));
            console.log('Translations loaded from data attribute:', translations);
        } catch (e) {
            console.error('Error parsing translations:', e);
        }
    } else {
        console.warn('No translations found. Using default keys.');
    }
    
    // Initialize mega menu toggle functionality
    toggleMegaMenuContent();
    
    // Initialize nestable with proper configuration for sub-items
    if ($('#nestable').length) {
        // Initialize Nestable
        $('#nestable').nestable({
            expandBtnHTML: '',
            collapseBtnHTML: '',
            maxDepth: 5,                  // Allow up to 5 levels of nesting
            handleClass: 'dd-handle',     // Drag handle class
            noDragClass: 'dd-nodrag',     // Class for elements that shouldn't be dragged
            placeClass: 'dd-placeholder', // Class for the placeholder while dragging
            itemClass: 'dd-item',         // Class for menu items
            listClass: 'dd-list'          // Class for nested lists
        });
        
        // Track if we're currently dragging
        let isDragging = false;
        let menuChanged = false;
        
        // Add a change event listener to detect structure changes
        $('#nestable').on('change', function() {
            console.log('Nestable change event triggered');
            // Just mark that the menu has changed, but don't send AJAX yet
            menuChanged = true;
            // Update visual depth indicators
            updateItemsVisualDepth();
        });
        
        // Apply drag start effects
        $(document).on('mousedown', '.dd-handle', function() {
            isDragging = true;
            $(this).closest('.dd-item').css('opacity', '0.8');
        });
        
        // When drag ends, only update visual indicators but don't send AJAX
        $(document).on('mouseup', function() {
            if (isDragging && menuChanged) {
                // Reset flags
                isDragging = false;
                menuChanged = false;
                
                // Restore opacity
                $('.dd-item').css('opacity', '1');
                
                // Only update visual indicators, don't send AJAX
                console.log('Drag complete - visual update only');
                // Show notification to remind user to click Update Menu
                MenuUtils.showNotification('Menu structure changed. Click "Update Menu" to save changes.', 'info', 3000);
            } else if (isDragging) {
                // Just a click, not a drag that changed anything
                isDragging = false;
                $('.dd-item').css('opacity', '1');
            }
        });
    }
    
    // Set up event listeners for label changes
    $(document).on('keyup', '.edit-menu-item-title', function() {
        const title = $(this).val();
        const index = $('.edit-menu-item-title').index($(this));
        $('.menu-item-title').eq(index).html(title);
    });
    
    // Set up event listeners for URL changes
    $(document).on('keyup', '.edit-menu-item-url', function() {
        const url = $(this).val();
        const index = $('.edit-menu-item-url').index($(this));
        const result = url.slice(0, 30) + (url.length > 30 ? "..." : "");
        $('.menu-item-link').eq(index).html('(' + result + ')');
    });
    
    // Handle clicking on the Edit button (using delegation for dynamically added elements)
    $(document).on('click', '.edit-button', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Stop event bubbling to prevent drag start
        
        // Get the item ID from data attribute
        const itemId = $(this).data('item-id');
        
        // Toggle the settings panel
        $('#settings-' + itemId).toggleClass('hidden');
    });
    
    // Handle clicking on the Cancel button
    $(document).on('click', '.cancel-button', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Stop event bubbling
        
        // Get the item ID from data attribute
        const itemId = $(this).data('item-id');
        
        // Hide the settings panel
        $('#settings-' + itemId).addClass('hidden');
    });
    
    // Handle Tailwind collapse functionality for side panels
    $(document).on('click', '[data-toggle="collapse"]', function(e) {
        e.preventDefault();
        const targetId = $(this).data('target');
        const target = $(targetId);
        
        if (target.hasClass('hidden')) {
            target.removeClass('hidden').addClass('block');
            $(this).addClass('expanded').attr('aria-expanded', 'true');
        } else {
            target.removeClass('block').addClass('hidden');
            $(this).removeClass('expanded').attr('aria-expanded', 'false');
        }
    });
    
    // Activate loading indicator
    $(document).ajaxStart(function() {
        MenuUtils.showLoader();
    }).ajaxStop(function() {
        MenuUtils.hideLoader();
    });
});

/**
 * Update depth indicators for all items
 */
function updateItemsVisualDepth() {
    // First, update all depth values based on the DOM structure
    // Root level items have depth 0
    $('.dd-list').first().children('.dd-item').each(function() {
        updateItemDepth($(this), 0);
    });
    
    // Recursive function to update depth for an item and its children
    function updateItemDepth($item, currentDepth) {
        // Update this item's depth
        const $parentItem = currentDepth > 0 ? $item.closest('.dd-list').closest('.dd-item') : null;
        const parentId = $parentItem ? $parentItem.data('id') || 0 : 0;
        
        // Update visual indicator
        $item.find('.menu-item-depth').first().text(`(Depth: ${currentDepth})`);
        
        // Add data attributes for depth and parent that can be used by the server
        $item.attr('data-depth', currentDepth);
        $item.attr('data-parent', parentId);
        
        // Also update any hidden input fields for depth/parent if they exist
        $item.find('.edit-menu-item-depth').val(currentDepth);
        $item.find('.edit-menu-item-parent').val(parentId);
        
        // Process children with incremented depth
        const $childList = $item.children('.dd-list');
        if ($childList.length) {
            $childList.children('.dd-item').each(function() {
                updateItemDepth($(this), currentDepth + 1);
            });
        }
    }
}

/**
 * Reorder menu items based on current DOM structure
 */
function reorderMenuItems() {
    // Get all items with their current positions and parent relationships
    const items = [];
    let index = 0;
    
    // Process root level items first
    $('.dd-list').first().children('.dd-item').each(function(rootIndex) {
        const $item = $(this);
        const itemId = $item.data('id');
        
        // Add this item with its current index as sort value
        items.push({
            id: itemId,
            sort: rootIndex,
            parent: 0,
            depth: 0
        });
        
        // Process any children recursively
        processChildren($item, itemId, 1);
    });
    
    // Function to process children recursively
    function processChildren($parent, parentId, depth) {
        const $childList = $parent.children('.dd-list');
        if ($childList.length) {
            $childList.children('.dd-item').each(function(childIndex) {
                const $child = $(this);
                const childId = $child.data('id');
                
                // Add this child with its index as sort value
                items.push({
                    id: childId,
                    sort: childIndex,
                    parent: parentId,
                    depth: depth
                });
                
                // Process any grandchildren
                processChildren($child, childId, depth + 1);
            });
        }
    }
    
    // Send the order to the server
    $.ajax({
        url: URL_REORDER_ITEMS || (URL_CURRENT + '/reorder-items'),
        type: 'POST',
        data: { items: items },
        beforeSend: function() {
            MenuUtils.showLoader();
        },
        success: function(response) {
            MenuUtils.showNotification('Menu order updated successfully', 'success');
            // Reload the page to reflect the new order
            setTimeout(() => window.location.reload(), 1000);
        },
        error: function(error) {
            console.error('Error reordering menu items:', error);
            MenuUtils.showNotification('Error updating menu order. Changes may not be saved.', 'error');
        },
        complete: function() {
            MenuUtils.hideLoader();
        }
    });
}