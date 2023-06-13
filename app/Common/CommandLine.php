<?php

namespace Ds\Common;

use Illuminate\Support\Arr;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CommandLine
{
    /**
     * @param string|array $command
     * @return string
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function run($command): string
    {
        $process = $this->runCommand($command);

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * @param string|array $command
     * @return string
     */
    public function runQuietly($command): string
    {
        $process = $this->runCommand($command);

        return $process->isSuccessful() ? $process->getOutput() : '';
    }

    /**
     * @param string|array $command
     * @return \Symfony\Component\Process\Process
     */
    private function runCommand($command): Process
    {
        $config = [
            'command' => [],
            'cwd' => null,
            'env' => null,
            'input' => null,
            'timeout' => 600,
            'idle_timeout' => null,
            'tty_mode' => false,
            'run' => true,
        ];

        if (is_string($command) || Arr::isAssoc($command) === false) {
            $config['command'] = $command;
        } else {
            $config = Arr::defaults($command, $config);
        }

        if (is_string($config['command'])) {
            $process = Process::fromShellCommandline(
                $config['command'],
                $config['cwd'],
                $config['env'],
                $config['input'],
                $config['timeout']
            );
        } else {
            $process = new Process(
                $config['command'],
                $config['cwd'],
                $config['env'],
                $config['input'],
                $config['timeout']
            );
        }

        if ($config['idle_timeout']) {
            $process->setIdleTimeout($config['idle_timeout']);
        }

        if ($config['tty_mode'] && Process::isTtySupported()) {
            $process->setTty(true);
        }

        if ($config['run']) {
            $process->run(
                is_callable($config['run']) ? $config['run'] : null
            );
        }

        return $process;
    }
}
