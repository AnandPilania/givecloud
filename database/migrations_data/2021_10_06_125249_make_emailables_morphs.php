<?php

use Ds\Models\Email;
use Ds\Models\Membership;
use Ds\Models\Product;
use Ds\Models\Variant;
use Illuminate\Database\Migrations\Migration;

class MakeEmailablesMorphs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Email::query()
            ->whereNotNull('parent_id')
            ->whereNotNull('parent_type')
            ->each(function (Email $email) {
                if ($email->emailable instanceof Product) {
                    $email->products()->attach(explode(',', $email->parent_id));
                }

                if ($email->emailable instanceof Variant) {
                    $email->variants()->attach(explode(',', $email->parent_id));
                }

                if ($email->emailable instanceof Membership) {
                    $email->memberships()->attach(explode(',', $email->parent_id));
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('emailables')->truncate();
    }
}
