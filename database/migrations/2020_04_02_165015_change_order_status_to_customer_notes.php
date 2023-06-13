<?php

use Illuminate\Database\Migrations\Migration;

class ChangeOrderStatusToCustomerNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DBAL can't rename columns in a table if it has any enum columns
        DB::statement("ALTER TABLE productorder CHANGE `status` customer_notes TEXT COLLATE 'utf8mb4_unicode_ci' NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // DBAL can't rename columns in a table if it has any enum columns
        DB::statement("ALTER TABLE productorder CHANGE customer_notes `status` TEXT COLLATE 'utf8mb4_unicode_ci' NULL");
    }
}
