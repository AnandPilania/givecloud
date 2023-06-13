<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNodecontentColumnsToNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('node', function (Blueprint $table) {
            $table->string('metadescription', 500)->nullable()->after('category_id');
            $table->string('metakeywords', 500)->nullable()->after('metadescription');
            $table->custom('featured_image_id', 'int(11)')->unsigned()->nullable()->index('featured_image_id')->after('metakeywords');
            $table->custom('alt_image_id', 'int(11)')->unsigned()->nullable()->index('alt_image_id')->after('featured_image_id');
            $table->longText('body')->nullable()->after('alt_image_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('node', function (Blueprint $table) {
            $table->dropColumn([
                'metadescription',
                'metakeywords',
                'featured_image_id',
                'alt_image_id',
                'body',
            ]);
        });
    }
}
