<?php

namespace Ds\Illuminate\Database\Console;

use Illuminate\Database\Console\Migrations\InstallCommand;

class MigrateDataInstallCommand extends InstallCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'migrate:install_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the data migration repository';
}
