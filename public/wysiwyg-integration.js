/**
 * Laravel Menu - WYSIWYG Editor Integration for Mega Menu
 * 
 * This file provides examples of how to integrate popular WYSIWYG editors
 * with the mega menu content textarea.
 * 
 * To use this file:
 * 1. Include it after the menu.js file
 * 2. Include the WYSIWYG editor library of your choice
 * 3. Uncomment the section for your preferred editor
 */

document.addEventListener('DOMContentLoaded', function() {
    // Function to initialize editors when the page loads
    initializeEditors();
    
    // Also initialize editors when a menu item is being edited
    document.addEventListener('menu-item-edit-start', function() {
        // Small delay to ensure the DOM is updated
        setTimeout(function() {
            initializeEditors();
        }, 100);
    });
});

/**
 * Initialize WYSIWYG editors for mega menu content
 * Uncomment the section for your preferred editor
 */
function initializeEditors() {
    const megaMenuTextareas = document.querySelectorAll('.wysiwyg-editor');
    
    if (megaMenuTextareas.length === 0) return;
    
    // Toggle visibility of editor based on mega menu checkbox
    document.querySelectorAll('.edit-menu-item-mega').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const itemId = this.id.replace('is-mega-menu-', '');
            const contentContainer = document.getElementById('mega-menu-content-' + itemId);
            
            if (contentContainer) {
                if (this.checked) {
                    contentContainer.classList.remove('hidden');
                } else {
                    contentContainer.classList.add('hidden');
                }
            }
        });
    });

    // =============================================
    // CKEditor Integration
    // Uncomment this section to use CKEditor
    // =============================================
    /*
    if (typeof CKEDITOR !== 'undefined') {
        megaMenuTextareas.forEach(function(textarea) {
            if (!CKEDITOR.instances[textarea.id]) {
                CKEDITOR.replace(textarea.id, {
                    height: 200,
                    toolbar: [
                        { name: 'document', items: ['Source'] },
                        { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                        { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll'] },
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                        { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
                        { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                        { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                        { name: 'colors', items: ['TextColor', 'BGColor'] },
                        { name: 'tools', items: ['Maximize', 'ShowBlocks'] }
                    ]
                });
            }
        });

        // Update form data before submission
        document.addEventListener('menu-item-before-update', function(e) {
            for (const instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
            }
        });
    }
    */

    // =============================================
    // TinyMCE Integration
    // Uncomment this section to use TinyMCE
    // =============================================
    /*
    if (typeof tinymce !== 'undefined') {
        megaMenuTextareas.forEach(function(textarea) {
            const editorId = textarea.id;
            
            // Remove any existing editor instance
            if (tinymce.get(editorId)) {
                tinymce.remove('#' + editorId);
            }
            
            // Initialize TinyMCE
            tinymce.init({
                selector: '#' + editorId,
                height: 200,
                menubar: false,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }'
            });
        });
        
        // Update form data before submission
        document.addEventListener('menu-item-before-update', function(e) {
            tinymce.triggerSave();
        });
    }
    */

    // =============================================
    // Summernote Integration
    // Uncomment this section to use Summernote
    // =============================================
    /*
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.summernote !== 'undefined') {
        jQuery('.wysiwyg-editor').each(function() {
            // Destroy any existing instance
            if (jQuery(this).data('summernote')) {
                jQuery(this).summernote('destroy');
            }
            
            // Initialize Summernote
            jQuery(this).summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
        
        // No need for special handling before submission as Summernote
        // automatically updates the textarea value
    }
    */
}

/**
 * Custom event dispatched before updating a menu item
 * This allows the WYSIWYG editors to update their associated textareas
 */
function dispatchBeforeUpdateEvent() {
    document.dispatchEvent(new CustomEvent('menu-item-before-update'));
}

/**
 * Custom event dispatched when a menu item is being edited
 * This allows initialization of WYSIWYG editors when an item is being edited
 */
function dispatchEditStartEvent() {
    document.dispatchEvent(new CustomEvent('menu-item-edit-start'));
}

// Modify the updateItem function to dispatch the before-update event
const originalUpdateItem = window.updateItem;
if (typeof originalUpdateItem === 'function') {
    window.updateItem = function(id) {
        dispatchBeforeUpdateEvent();
        return originalUpdateItem(id);
    };
}

// Hook into the edit button click event to initialize editors
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('edit-item')) {
        dispatchEditStartEvent();
    }
});
