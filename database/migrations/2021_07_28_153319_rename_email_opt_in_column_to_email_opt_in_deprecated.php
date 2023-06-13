<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameEmailOptInColumnToEmailOptInDeprecated extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member', function (Blueprint $table) {
            $table->renameColumn('email_opt_in', 'email_opt_in_deprecated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member', function (Blueprint $table) {
            $table->renameColumn('email_opt_in_deprecated', 'email_opt_in');
        });
    }
}
