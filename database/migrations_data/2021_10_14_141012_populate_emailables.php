<?php

use Ds\Models\Email;
use Illuminate\Database\Migrations\Migration;

class PopulateEmailables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Email::query()
            ->whereNotNull('parent_id_deprecated')
            ->whereNotNull('parent_type_deprecated')
            ->each(function (Email $email) {
                if ($email->parent_type_deprecated === 'Product') {
                    $email->products()->attach([$email->parent_id_deprecated]);
                }

                if ($email->parent_type_deprecated === 'variant') {
                    $email->variants()->attach([$email->parent_id_deprecated]);
                }

                if ($email->parent_type_deprecated === 'Membership') {
                    $email->memberships()->attach([$email->parent_id_deprecated]);
                }
            });
    }
}
