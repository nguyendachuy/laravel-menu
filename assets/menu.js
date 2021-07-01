/**
 * load loading
 */
$(document).ajaxStart(function () {
    $("#ajax_loader").show();
}).ajaxStop(function () {
    $("#ajax_loader").hide('slow');
});
/**
 * change label
 */
$(document).on('keyup', '.edit-menu-item-title', function () {
    var title = $(this).val();
    var index = $('.edit-menu-item-title').index($(this));
    $('.menu-item-title').eq(index).html(title);
});
/**
 * change url
 */
$(document).on('keyup', '.edit-menu-item-url', function () {
    var url = $(this).val();
    var index = $('.edit-menu-item-url').index($(this));
    /**
     * limit string
     */
    var result = url.slice(0, 30) + (url.length > 30 ? "..." : "");
    $('.menu-item-link').eq(index).html(result);
});
/**
 * add item menu
 * type : default or custom
 */
function addCustomMenu(e, type) {
    const data = [];
    const form = $(e).parents('form');
    if (type == "default") {
        data.push({
            label: form.find('input[name="label"]').val(),
            url: form.find('input[name="url"]').val(),
            role: form.find('select[name="role"]').val(),
            icon: form.find('input[name="icon"]').val(),
            id: $('#idmenu').val()
        });
    } else {
        const selected = form.find('select.data-select option:selected');
        for (let index = 0; index < selected.length; index++) {
            const element = $(selected[index]);
            data.push({
                label: element.text(),
                url: element.attr('data-url'),
                role: form.find('select[name="role"]').val(),
                icon: element.attr('data-icon'),
                id: $('#idmenu').val()
            });
        }
    }
    $.ajax({
        data: {
            data: data
        },
        url: addCustomMenur,
        type: 'POST',
        success: function (response) {
            window.location.reload();
        },
        complete: function () { }
    });
}

function updateItem(id = 0) {
    if (id) {
        var label = $('#idlabelmenu_' + id).val();
        var clases = $('#clases_menu_' + id).val();
        var url = $('#url_menu_' + id).val();
        var icon = $('#icon_menu_' + id).val();
        var target = $('#target_menu_' + id).val();
        var role_id = 0;
        if ($('#role_menu_' + id).length) {
            role_id = $('#role_menu_' + id).val();
        }

        var data = {
            label: label,
            clases: clases,
            url: url,
            icon: icon,
            target: target,
            role_id: role_id,
            id: id
        };
    } else {
        var arr_data = [];
        $('.menu-item-settings').each(function (k, v) {
            var id = $(this)
                .find('.edit-menu-item-id')
                .val();
            var label = $(this)
                .find('.edit-menu-item-title')
                .val();
            var clases = $(this)
                .find('.edit-menu-item-classes')
                .val();
            var url = $(this)
                .find('.edit-menu-item-url')
                .val();
            var icon = $(this)
                .find('.edit-menu-item-icon')
                .val();
            var role = $(this)
                .find('.edit-menu-item-role')
                .val();
            var target = $(this)
                .find('select.edit-menu-item-target option:selected')
                .val();
            arr_data.push({
                id: id,
                label: label,
                class: clases,
                link: url,
                icon: icon,
                target: target,
                role_id: role
            });
        });

        var data = {
            arraydata: arr_data
        };
    }
    $.ajax({
        data: data,
        url: updateItemr,
        type: 'POST',
        beforeSend: function (xhr) {
            if (id) { }
        },
        success: function (response) { },
        complete: function () {
            if (id) { }
        }
    });
}

function actualizarMenu(serialize) {
    $.ajax({
        dataType: 'json',
        data: {
            data: serialize,
            menuName: $('#menu-name').val(),
            idMenu: $('#idmenu').val()
        },
        url: generateMenuControlr,
        type: 'POST',
        success: function (response) {
            /**
             * update text option
             */
            $(`select[name="menu"] option[value="${$('#idmenu').val()}"]`).html($('#menu-name').val());
        }
    });
}

function deleteItem(id) {
    $.ajax({
        dataType: 'json',
        data: {
            id: id
        },
        url: deleteItemMenur,
        type: 'POST',
        success: function (response) {
            window.location = currentItem;
        }
    });
}

function deleteMenu() {
    var r = confirm('Do you want to delete this menu ?');
    if (r == true) {
        $.ajax({
            dataType: 'json',
            data: {
                id: $('#idmenu').val()
            },
            url: deleteMenugr,
            type: 'POST',
            success: function (response) {
                if (!response.error) {
                    alert(response.resp);
                    window.location = menuwr;
                } else {
                    alert(response.resp);
                }
            }
        });
    } else {
        return false;
    }
}

function createNewMenu() {
    if (!!$('#menu-name').val()) {
        $.ajax({
            dataType: 'json',
            data: {
                menuname: $('#menu-name').val()
            },
            url: createNewMenur,
            type: 'POST',
            success: function (response) {
                window.location = menuwr + '?menu=' + response.resp;
            }
        });
    } else {
        alert('Enter menu name!');
        $('#menu-name').focus();
        return false;
    }
}


$(document).ready(function () {
    if ($('#nestable').length) {
        /**
         * https://github.com/RamonSmit/Nestable2#configuration
         */
        $('#nestable').nestable({
            expandBtnHTML: '',
            collapseBtnHTML: '',
            maxDepth: 5, //number of levels an item can be nested
            callback: function (l, e) {
                updateItem();
                actualizarMenu(l.nestable('toArray'));
            }
        });
    }
});