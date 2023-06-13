<?php

namespace Ds\Http\Controllers\Frontend\API;

use DomainException;
use Ds\Domain\Shared\Exceptions\DisclosableException;
use Ds\Illuminate\Database\MySqlConnection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Throwable;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * Create an instance.
     */
    public function __construct()
    {
        $this->registerMiddleware();
    }

    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        // do nothing
    }

    /**
     * Get successful response.
     *
     * @param mixed $data
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, $status = 200)
    {
        if (is_null($data)) {
            $data = ['success' => true];
        }

        try {
            return MySqlConnection::runWithSelectCache(function () use ($data, $status) {
                return response()->json($this->resolveData($data), $status);
            });
        } catch (DisclosableException $e) {
            return $this->failure($e->getMessage(), 422);
        } catch (DomainException $e) {
            return $this->failure($e->getMessage(), 422);
        }
    }

    /**
     * Get failure response.
     *
     * @param mixed $data
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function failure($data = null, $status = 422)
    {
        if (is_null($data)) {
            $data = 'Unknown error';
        }

        if (is_string($data)) {
            $data = ['error' => $data];
        }

        try {
            return response()->json($this->resolveData($data), $status);
        } catch (DisclosableException $e) {
            return response()->json($e->getMessage(), 422);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }
    }

    /**
     * Resolve data to a JSON-safe response.
     *
     * @param mixed $data
     * @return mixed
     */
    protected function resolveData($data = null)
    {
        if (is_string($data)) {
            return $data;
        }

        if (is_object($data)) {
            if ($data instanceof Throwable) {
                if (isDev()) {
                    return [
                        'type' => get_class($data),
                        'message' => $this->resolveData((string) $data->getMessage()),
                        'file' => $data->getFile(),
                        'line' => $data->getLine(),
                    ];
                }

                if ($data instanceof DisclosableException || $data instanceof DomainException) {
                    return ['error' => $this->resolveData((string) $data->getMessage())];
                }

                notifyException($data);

                return ['error' => 'An error occurred.'];
            }

            if ($data instanceof \DateTime) {
                return toUtcFormat($data, 'api');
            }

            if ($data instanceof \Ds\Domain\Theming\Liquid\Liquidable) {
                return $this->resolveData($data->toLiquid());
            }

            if ($data instanceof \Illuminate\Support\Collection) {
                return $this->resolveData($data->all());
            }

            if ($data instanceof \Illuminate\Contracts\Support\Arrayable) {
                return $this->resolveData($data->toArray());
            }

            if ($data instanceof \Traversable) {
                return $this->resolveData(iterator_to_array($data));
            }

            if ($data instanceof \Illuminate\Http\Resources\Json\JsonResource) {
                return $this->resolveData($data->toArray(request()));
            }

            return ['error' => 'Unsupported object [' . get_class($data) . '].'];
        }

        if (is_array($data)) {
            return collect($data)
                ->reject(function ($item) {
                    return $item instanceof \Illuminate\Http\Resources\MissingValue;
                })->map(function ($item) {
                    return $this->resolveData($item);
                })->all();
        }

        return $data;
    }

    /**
     * Get the URL for an API request.
     *
     * @param string $path
     * @return string
     */
    protected function url($path)
    {
        return url('gc-json/v1/' . ltrim($path, '/'));
    }
}
