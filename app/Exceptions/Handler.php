<?php

namespace Ds\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types whos messages can be disclosed
     * to production users in error messages.
     *
     * @var string[]
     */
    protected $discloseable = [
        \DomainException::class,
        \Ds\Domain\Shared\Exceptions\DisclosableException::class,
        \Illuminate\Session\TokenMismatchException::class,
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        \Ds\Common\DonorPerfect\RequestException::class,
        \Ds\Domain\Shared\Exceptions\DisclosableException::class,
        \Ds\Domain\Shared\Exceptions\HtmlableException::class,
        \Ds\Domain\Shared\Exceptions\PermissionException::class,
        \Ds\Domain\Shared\Exceptions\RedirectException::class,
        \League\OAuth2\Server\Exception\OAuthServerException::class,
        \Symfony\Component\Console\Exception\CommandNotFoundException::class,
        \Symfony\Component\Console\Exception\RuntimeException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Report or log an exception.
     *
     * @param \Throwable $e
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $e)
    {
        // Temporarily enabling reporting of token mismatches
        if ($e instanceof TokenMismatchException && app()->bound('exceptionist')) {
            app('exceptionist')->notifyException($e);
        }

        if ($this->shouldReport($e) && app()->bound('exceptionist')) {
            app('exceptionist')->notifyException($e);
        }

        parent::report($e);
    }

    /**
     * Get the default context variables for logging.
     *
     * @return array
     */
    protected function context()
    {
        return array_merge(parent::context(), [
            'site' => sys_get('ds_account_name'),
        ]);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        $showFrontendException = isGivecloudPro() && ! $request->is('jpanel/*');

        if ($showFrontendException && ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Page not found'], 404);
            }

            return (new \Ds\Http\Controllers\Frontend\DefaultController)->callAction('handleError', [404]);
        }

        return parent::render($request, $e);
    }

    /**
     * Get the view used to render HTTP exceptions.
     *
     * @param \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e
     * @return string
     */
    protected function getHttpExceptionView(HttpExceptionInterface $e)
    {
        $viewName = "errors.{$e->getStatusCode()}";

        return view()->exists($viewName) ? $viewName : 'errors.500';
    }

    /**
     * Convert the given exception to an array.
     *
     * @param \Throwable $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        if (config('app.debug')) {
            return [
                'error' => $e->getMessage(),
                'exception' => [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ];
        }

        return [
            'error' => ($this->isHttpException($e) || is_instanceof($e, $this->discloseable))
                ? $e->getMessage()
                : 'Oops! Looks like there was an error. Please try again.',
        ];
    }
}
