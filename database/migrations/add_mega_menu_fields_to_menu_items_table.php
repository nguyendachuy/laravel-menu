<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMegaMenuFieldsToMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = config('menu.table_prefix') . config('menu.table_name_items');
        
        Schema::table($tableName, function (Blueprint $table) {
            $table->boolean('is_mega_menu')->default(false)->after('target');
            $table->text('mega_menu_content')->nullable()->after('is_mega_menu');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = config('menu.table_prefix') . config('menu.table_name_items');
        
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn(['is_mega_menu', 'mega_menu_content']);
        });
    }
}
