<?php

namespace Ds\Illuminate\Console;

use BadMethodCallException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Helper as ConsoleHelper;
use Symfony\Component\Console\Helper\ProgressBar as Bar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

class ProgressBar
{
    use ForwardsCalls;

    /** @var \Symfony\Component\Console\Output\ConsoleSectionOutput */
    private $output;

    /** @var \Symfony\Component\Console\Output\ConsoleSectionOutput */
    private $barOutput;

    /** @var \Symfony\Component\Console\Helper\ProgressBar */
    private $bar;

    /** @var \Symfony\Component\Console\Cursor */
    private $cursor;

    /** @var array */
    private static $formats = [
        'simple' => '%spinner% %current%/%max% <fg=yellow>%remaining%</><fg=yellow>%duration%</> <fg=white>%message%</>',
        'complex' => '⸨%bar%⸩ %spinner% %current%/%max% <fg=yellow>%remaining%</><fg=yellow>%duration%</> <fg=white>%message%</>',
    ];

    /** @var bool */
    private $shouldTerminate = false;

    /**
     * @param int $max
     * @param string|null $format
     */
    public function __construct(int $max = 0, ?string $format = null)
    {
        $output = new ConsoleOutput;

        $this->output = $output->section();
        $this->barOutput = $output->section();

        static::initFormats();
        static::initPlaceholderFormatters();

        $this->bar = new Bar($this->barOutput, $max);
        $this->bar->setFormat($format ?: 'complex');
        $this->bar->setRedrawFrequency(1);
        $this->bar->setBarWidth(28);
        $this->bar->setEmptyBarCharacter('░');
        $this->bar->setProgressCharacter('');
        $this->bar->setBarCharacter('█');
        $this->bar->setMessage('');

        $this->bar->setRedrawFrequency(100);
        $this->bar->minSecondsBetweenRedraws(0.1);
        $this->bar->maxSecondsBetweenRedraws(0.5);

        pcntl_async_signals(true);

        pcntl_signal(SIGINT, function ($signo, $signinfo) {
            $this->shouldTerminate = true;
        });

        $this->cursor = new Cursor($output);
    }

    /**
     * Starts the progress output.
     *
     * @param int|null $max
     */
    public function start(int $max = null)
    {
        $this->bar->start($max);
        $this->cursor->hide();
    }

    /**
     * Advances the progress output X steps.
     *
     * @param int $step
     */
    public function advance(int $step = 1)
    {
        if ($this->shouldTerminate) {
            $this->bar->display();
            $this->cursor->show();
            exit(1);
        }

        $this->bar->advance($step);
        $this->cursor->hide();
    }

    /**
     * Finishes the progress output.
     */
    public function finish()
    {
        $this->bar->finish();
        $this->cursor->show();
    }

    /**
     * Associates a text with a named placeholder.
     *
     * The text is displayed when the progress bar is rendered but only
     * when the corresponding placeholder is part of the custom format line
     * (by wrapping the name with %).
     *
     * @param \Throwable|string $message
     * @param string $name
     */
    public function setMessage($message, string $name = 'message')
    {
        if (is_object($message) && $message instanceof Throwable) {
            $message = $message->getMessage();
        }

        $this->bar->setMessage($message, $name);
    }

    /**
     * Format input to textual table.
     *
     * @param array $headers
     * @param \Illuminate\Contracts\Support\Arrayable|array $rows
     * @param string $tableStyle
     * @param array $columnStyles
     * @return void
     */
    public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders((array) $headers)->setRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    /**
     * Write a string as information output.
     *
     * @param string $string
     * @return void
     */
    public function info($string)
    {
        $this->line($string, 'info');
    }

    /**
     * Write a string as standard output.
     *
     * @param string $string
     * @param string|null $style
     * @return void
     */
    public function line($string, $style = null)
    {
        $styled = $style ? "<$style>$string</$style>" : $string;

        $this->output->writeln($styled);
    }

    /**
     * Write a string as comment output.
     *
     * @param string $string
     * @return void
     */
    public function comment($string)
    {
        $this->line($string, 'comment');
    }

    /**
     * Write a string as question output.
     *
     * @param string $string
     * @return void
     */
    public function question($string)
    {
        $this->line($string, 'question');
    }

    /**
     * Write a string as error output.
     *
     * @param \Throwable|string $string
     * @return void
     */
    public function error($string)
    {
        if (is_object($string) && $string instanceof Throwable) {
            $string = $string->getMessage();
        }

        $this->line($string, 'error');
    }

    /**
     * Write a string as warning output.
     *
     * @param string $string
     * @return void
     */
    public function warn($string)
    {
        if (! $this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');

            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning');
    }

    /**
     * Write a string in an alert box.
     *
     * @param string $string
     * @return void
     */
    public function alert($string)
    {
        $length = Str::length(strip_tags($string)) + 12;

        $this->comment(str_repeat('*', $length));
        $this->comment('*     ' . $string . '     *');
        $this->comment(str_repeat('*', $length));

        $this->newLine();
    }

    /**
     * Write a new line.
     *
     * @param int $count
     * @return void
     */
    public function newLine(int $count = 1)
    {
        $this->output->write(str_repeat(PHP_EOL, $count));
    }

    /**
     * Add custom format definitions to the symfony
     * progress bar class.
     */
    private static function initFormats()
    {
        foreach (static::$formats as $name => $format) {
            Bar::setFormatDefinition($name, $format);
        }
    }

    /**
     * Add custom placeholder formatter definitions to the symfony
     * progress bar class.
     */
    private static function initPlaceholderFormatters()
    {
        $formatters = [
            'current' => 'placeholderFormatterCurrent',
            'duration' => 'placeholderFormatterDuration',
            'remaining' => 'placeholderFormatterRemaining',
            'spinner' => 'placeholderFormatterSpinner',
        ];

        foreach ($formatters as $name => $method) {
            Bar::setPlaceholderFormatterDefinition($name, [static::class, $method]);
        }
    }

    /**
     * Display the current progress (without padding).
     *
     * @param \Symfony\Component\Console\Helper\ProgressBar $bar
     * @return int
     */
    public static function placeholderFormatterCurrent(Bar $bar)
    {
        return $bar->getProgress();
    }

    /**
     * Display the duration of the task.
     *
     * @param \Symfony\Component\Console\Helper\ProgressBar $bar
     * @return string
     */
    public static function placeholderFormatterDuration(Bar $bar)
    {
        if ($bar->getProgress() < $bar->getMaxSteps()) {
            return '';
        }

        return static::formatTime($bar, time() - $bar->getStartTime());
    }

    /**
     * Display the time remaining (in shortform with whitespace removed).
     *
     * @param \Symfony\Component\Console\Helper\ProgressBar $bar
     * @return string
     */
    public static function placeholderFormatterRemaining(Bar $bar)
    {
        if ($bar->getProgress()) {
            $remaining = round((time() - $bar->getStartTime()) / $bar->getProgress() * ($bar->getMaxSteps() - $bar->getProgress()));
        } else {
            $remaining = 0;
        }

        if ($bar->getProgress() === $bar->getMaxSteps()) {
            return '';
        }

        return static::formatTime($bar, $remaining);
    }

    /**
     * Display spinner using the brail dots.
     *
     * @param \Symfony\Component\Console\Helper\ProgressBar $bar
     * @return string
     */
    public static function placeholderFormatterSpinner(Bar $bar)
    {
        $chars = '⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏';

        if ($bar->getProgress() < $bar->getMaxSteps()) {
            return mb_substr($chars, $bar->getProgress() % mb_strlen($chars), 1);
        }

        return '';
    }

    /**
     * Format the time.
     *
     * @param \Symfony\Component\Console\Helper\ProgressBar $bar
     * @param int $time
     * @return string
     */
    private static function formatTime(Bar $bar, int $time)
    {
        if ($bar->getProgress() === $bar->getMaxSteps()) {
            if ($time < 1) {
                return '';
            }
        }

        return str_replace(
            [' secs', ' sec', ' mins', ' min', ' hrs', ' hr', 'days', ' day'],
            ['s', 's', 'm', 'm', 'h', 'h', 'd', 'd'],
            ConsoleHelper::formatTime($time)
        );
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        try {
            return $this->forwardCallTo($this->bar, $method, $parameters);
        } catch (BadMethodCallException $e) {
            return $this->forwardCallTo($this->output, $method, $parameters);
        }
    }
}
