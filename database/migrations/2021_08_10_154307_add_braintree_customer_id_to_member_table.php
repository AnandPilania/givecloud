<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBraintreeCustomerIdToMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->string('braintree_customer_id', 64)->nullable()->after('authorizenet_customer_id');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('braintree_customer_id', 64)->nullable()->after('authorizenet_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->dropColumn('braintree_customer_id');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('braintree_customer_id');
        });
    }
}
