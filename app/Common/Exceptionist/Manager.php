<?php

namespace Ds\Common\Exceptionist;

use Bugsnag\Report;
use Ds\Common\DonorPerfect\QueryException;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Client\RequestException;
use Throwable;

class Manager
{
    /** @var \Illuminate\Foundation\Application */
    protected $app;

    /**
     * Create an instance
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Notify a non-fatal/handled throwable.
     *
     * @param \Throwable $throwable the throwable to notify Bugsnag about
     * @param callable|null $callback the customization callback
     * @return void
     */
    public function notifyException(Throwable $throwable, callable $callback = null)
    {
        if ($throwable instanceof Exception && ! $this->app[ExceptionHandler::class]->shouldReport($throwable)) {
            return;
        }

        if ($this->app->bound('bugsnag')) {
            $this->app['bugsnag']->notifyException($throwable, function ($report) use ($throwable, $callback) {
                try {
                    $this->includeMetaDataFromThrowable($report, $throwable);
                } catch (Throwable $e) {
                    // Ignore any errors.
                }
                if ($callback) {
                    $callback($report);
                }
            });
        }
    }

    /**
     * Notify a non-fatal/handled error.
     *
     * @param string $name the name of the error, a short (1 word) string
     * @param string $message the error message
     * @param callable|null $callback the customization callback
     * @return void
     */
    public function notifyError($name, $message, callable $callback = null)
    {
        if ($this->app->bound('bugsnag')) {
            $this->app['bugsnag']->notifyError($name, $message, $callback);
        }
    }

    /**
     * Include additional metadata from exception in report.
     *
     * @param \Bugsnag\Report $report
     * @param \Throwable $throwable
     */
    public function includeMetaDataFromThrowable(Report $report, Throwable $throwable)
    {
        try {
            if ($throwable instanceof RequestException || $throwable instanceof QueryException) {
                if ($throwable->response) {
                    $report->setMetaData([
                        'http_response' => $throwable->response->getDebugInfo(),
                    ]);
                }
            }

            if ($throwable instanceof \Ds\Common\Infusionsoft\HttpException) {
                $report->setMetaData([
                    'infusionsoft' => $throwable->getLogs(),
                ]);
            }
        } catch (Throwable $e) {
            // Ignore any errors.
        }
    }

    /**
     * Include addition metadata in report.
     *
     * @param \Bugsnag\Report $report
     * @return void
     */
    public function includeMetaData(Report $report)
    {
        try {
            if ($user = auth()->user()) {
                $report->setUser([
                    'id' => sys_get('ds_account_name'),
                    'first_name' => $user->firstname,
                    'last_name' => $user->lastname,
                    'email' => $user->firstname,
                    'site' => sys_get('ds_account_name'),
                ]);
            } else {
                $report->setUser([
                    'id' => sys_get('ds_account_name'),
                    'site' => sys_get('ds_account_name'),
                ]);
            }
        } catch (Throwable $e) {
            // Ignore any errors.
        }

        try {
            if (function_exists('member') && member()) {
                $report->setMetaData([
                    'member' => [
                        'id' => member('id'),
                        'first_name' => member('first_name'),
                        'last_name' => member('last_name'),
                        'email' => member('email'),
                    ],
                ]);
            }
        } catch (Throwable $e) {
            // Ignore any errors.
        }
    }
}
