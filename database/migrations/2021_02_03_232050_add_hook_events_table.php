<?php

use Ds\Models\Hook;
use Ds\Models\HookEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHookEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hook_events', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->foreignIdFor(Hook::class)->index();
        });

        Schema::table('hook_deliveries', function (Blueprint $table) {
            // Adding event to hook_deliveries as well in case hooks events change,
            // order_completed being the legacy value being default'd here.
            $table->string('event')->after('id')->default('order_completed');
        });

        $this->seedLegacy();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hook_events');
        Schema::table('hook_deliveries', function (Blueprint $table) {
            $table->dropColumn('event');
        });
    }

    protected function seedLegacy()
    {
        // Default events relationship to order_completed to keep backward compatibility,
        // when there was no events relationship and all hooks were for order_completed.
        Hook::chunk(200, function ($hooks) {
            $hooks->each(function ($hook) {
                $event = new HookEvent();
                $event->name = 'order_completed';

                $hook->events()->saveMany([$event]);
            });
        });
    }
}
