<?php

namespace Ds\Illuminate\Console;

use AustinHeap\Database\Encryption\Console\Commands\MigrateEncryptionCommand;
use Illuminate\Console\Application as BaseApplication;
use Illuminate\Console\Scheduling\ScheduleFinishCommand;
use Illuminate\Console\Scheduling\ScheduleRunCommand;

class Application extends BaseApplication
{
    /**
     * Blacklisted commands when APP_LEVEL is true.
     *
     * @var array
     */
    protected $blacklistedCommands = [
        // 'command.cache.clear',
        // 'command.cache.forget',
        // 'command.clear-compiled',
        'command.auth.resets.clear',
        // 'command.config.cache',
        // 'command.config.clear',
        'command.down',
        // 'command.environment',
        'command.key.generate',
        'command.migrate',
        'command.migrate.fresh',
        'command.migrate.install',
        'command.migrate.refresh',
        'command.migrate.reset',
        'command.migrate.rollback',
        'command.migrate.status',
        // 'command.package.discover',
        // 'command.preset',
        'command.queue.failed',
        'command.queue.flush',
        'command.queue.forget',
        'command.queue.listen',
        'command.queue.restart',
        'command.queue.retry',
        // 'command.queue.work',
        // 'command.route.cache',
        // 'command.route.clear',
        // 'command.route.list',
        'command.seed',
        ScheduleFinishCommand::class,
        ScheduleRunCommand::class,
        'command.storage.link',
        'command.up',
        // 'command.view.clear',

        'command.app.name',
        // 'command.auth.make',
        'command.cache.table',
        // 'command.channel.make',
        // 'command.console.make',
        // 'command.controller.make',
        // 'command.event.generate',
        // 'command.event.make',
        // 'command.exception.make',
        // 'command.factory.make',
        // 'command.job.make',
        // 'command.listener.make',
        // 'command.mail.make',
        // 'command.middleware.make',
        // 'command.migrate.make',
        // 'command.model.make',
        // 'command.notification.make',
        'command.notification.table',
        // 'command.policy.make',
        // 'command.provider.make',
        'command.queue.failed-table',
        'command.queue.table',
        // 'command.request.make',
        // 'command.resource.make',
        // 'command.rule.make',
        // 'command.seeder.make',
        'command.session.table',
        'command.serve',
        // 'command.test.make',
        // 'command.vendor.publish',

        MigrateEncryptionCommand::class,
    ];

    /**
     * Resolve an array of commands through the application.
     *
     * @param array|mixed $commands
     * @return $this
     */
    public function resolveCommands($commands)
    {
        if (defined('APP_LEVEL_ENABLED')) {
            foreach ($commands as $i => $command) {
                if (in_array($command, $this->blacklistedCommands)) {
                    unset($commands[$i]);
                }
            }
        }

        return parent::resolveCommands($commands);
    }
}
