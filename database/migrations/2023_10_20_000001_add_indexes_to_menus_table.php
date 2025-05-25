<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('menu.table_prefix') . config('menu.table_name_menus'), function (Blueprint $table) {
            // Add index to the name column as it's frequently used for lookups
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('menu.table_prefix') . config('menu.table_name_menus'), function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
}
