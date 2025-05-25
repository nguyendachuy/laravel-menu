<?php

Route::group([
    'namespace' => 'NguyenHuy\Menu\Http\Controllers',
    'middleware' => config('menu.middleware'),
    'prefix' => config('menu.route_prefix'),
    'as' => 'h-menu.',
], function () {
    /**
     * menu items
     */
    Route::post('add-item', array(
        'as' => 'add-item',
        'uses' => 'MenuController@createItem'
    ));
    Route::post('delete-item', array(
        'as' => 'delete-item',
        'uses' => 'MenuController@destroyItem'
    ));
    Route::post('update-item', array(
        'as' => 'update-item',
        'uses' => 'MenuController@updateItem'
    ));
    Route::post('reorder-items', array(
        'as' => 'reorder-items',
        'uses' => 'MenuController@reorderItems'
    ));

    /**
     * menu
     */
    Route::post('create-menu', array(
        'as' => 'create-menu',
        'uses' => 'MenuController@createNewMenu'
    ));
    Route::post('delete-menu', array(
        'as' => 'delete-menu',
        'uses' => 'MenuController@destroyMenu'
    ));
    Route::post('update-menu-and-items', array(
        'as' => 'update-menu-and-items',
        'uses' => 'MenuController@generateMenuControl'
    ));
});
