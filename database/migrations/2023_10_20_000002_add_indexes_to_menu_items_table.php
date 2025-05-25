<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('menu.table_prefix') . config('menu.table_name_items'), function (Blueprint $table) {
            // Add indexes to frequently queried columns
            $table->index('parent');
            $table->index('menu');
            $table->index('sort');
            $table->index('role_id');
            
            // Add composite index for common combined queries
            $table->index(['menu', 'parent']);
            $table->index(['menu', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('menu.table_prefix') . config('menu.table_name_items'), function (Blueprint $table) {
            $table->dropIndex(['parent']);
            $table->dropIndex(['menu']);
            $table->dropIndex(['sort']);
            $table->dropIndex(['role_id']);
            $table->dropIndex(['menu', 'parent']);
            $table->dropIndex(['menu', 'sort']);
        });
    }
}
