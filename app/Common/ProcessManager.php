<?php

namespace Ds\Common;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class ProcessManager
{
    /** @var int */
    protected $pollingInterval = 1000;

    /** @var int */
    protected $maxSimultaneous = 3;

    /** @var array */
    protected $slots = [];

    /**
     * Run artisan command once there's an available slot.
     */
    public function runArtisanCommand(string $cmd, array $args = [], ?Closure $callback = null): void
    {
        // when max simultaneous is set to 1 we can optimize
        // and call the artisan command directly instead of forking
        if ($this->maxSimultaneous === 1) {
            $exitCode = Artisan::call($cmd, $args);

            if (is_callable($callback)) {
                $callback(Artisan::output(), $exitCode);
            }

            return;
        }

        $this->runCommand($this->prepareArtisanCommand($cmd, $args), $callback);
    }

    private function prepareArtisanCommand(string $cmd, array $args): array
    {
        $command = ['php', base_path('artisan'), $cmd];

        foreach ($args as $key => $value) {
            if (0 === strpos($key, '--')) {
                $command = array_merge($command, $this->prepareArtisanLongOption($key, $value));
            } elseif (0 === strpos($key, '-')) {
                $command = array_merge($command, $this->prepareArtisanShortOption($key, $value));
            } else {
                $command = array_merge($command, $this->prepareArtisanArgument($value));
            }
        }

        return $command;
    }

    private function prepareArtisanArgument($value): array
    {
        return [$value];
    }

    private function prepareArtisanLongOption(string $key, $value): array
    {
        if (is_bool($value)) {
            return $value ? [$key] : [];
        }

        return ["{$key}={$value}"];
    }

    private function prepareArtisanShortOption(string $key, $value): array
    {
        if (is_bool($value)) {
            return $value ? [$key] : [];
        }

        return [$key, $value];
    }

    /**
     * Run command once there's an available slot.
     */
    public function runCommand($command, ?Closure $callback = null): void
    {
        $this->waitForAvailableSlot();

        $process = new Process(Arr::wrap($command));
        $process->setTimeout(0);
        $process->start();

        $this->slots[] = [$process, $callback];
    }

    /**
     * Wait for slots to be fully drained.
     */
    public function wait(): void
    {
        while (count($this->slots) > 0) {
            $this->checkSlots();
        }
    }

    private function waitForAvailableSlot(): void
    {
        while (count($this->slots) >= $this->maxSimultaneous) {
            $this->checkSlots();
        }
    }

    private function checkSlots(): void
    {
        usleep($this->pollingInterval);

        foreach ($this->slots as $index => [$process, $callback]) {
            if ($process->isRunning()) {
                continue;
            }

            unset($this->slots[$index]);

            if (is_callable($callback)) {
                $callback($process->getOutput(), $process->getExitCode());
            }
        }
    }

    /**
     * Set the max number of simultaneous processes.
     */
    public function setMaxSimultaneous(int $maxSimultaneous): void
    {
        $this->maxSimultaneous = $maxSimultaneous;
    }
}
