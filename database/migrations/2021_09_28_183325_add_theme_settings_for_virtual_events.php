<?php

use Ds\Enums\VirtualEventThemePrimaryColor;
use Ds\Enums\VirtualEventThemeStyle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThemeSettingsForVirtualEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('virtual_events', function (Blueprint $table) {
            $table->string('theme_style')->default(VirtualEventThemeStyle::DARK)->after('background_image');
            $table->string('theme_primary_color')->default(VirtualEventThemePrimaryColor::INDIGO)->after('theme_style');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('virtual_events', function (Blueprint $table) {
            $table->dropColumn('theme_style');
            $table->dropColumn('theme_primary_color');
        });
    }
}
