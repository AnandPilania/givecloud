<?php

namespace Ds\Http\Controllers\Frontend\API\Services;

use Ds\Http\Controllers\Frontend\API\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LocaleController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCountries()
    {
        $data = Cache::rememberForever('iso3166:countries' . $this->addLocaleSuffix(), function () {
            $data = [
                'countries' => [],
                'html' => '',
            ];

            foreach (app('iso3166')->countries() as $country) {
                $data['countries'][$country['alpha_2']] = $country['name'];
                $data['html'] .= "<option value=\"{$country['alpha_2']}\">{$country['name']}</option>";
            }

            return $data;
        });

        return response()->json($data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubdivisions($country)
    {
        $key = 'iso3166:subdivisions:' . Str::slug($country) . $this->addLocaleSuffix();

        $data = Cache::store('app')->rememberForever($key, function () use ($country) {
            $data = [
                'subdivisions' => [],
                'subdivision_type' => '',
                'html' => '',
            ];

            $subdivisions = app('iso3166')->subdivisions($country);

            // @phan-suppress-next-line PhanNonClassMethodCall
            $data['subdivision_type'] = collect($subdivisions)
                ->groupBy('type')
                ->map->count()
                ->sort()
                ->keys()
                ->last() ?? 'Province';

            foreach ($subdivisions as $subdivision) {
                $code = substr($subdivision['code'], 3);
                $data['subdivisions'][$code] = $subdivision['name'];
                $data['html'] .= "<option value=\"$code\">{$subdivision['name']}</option>";
            }

            return $data;
        });

        return response()->json($data);
    }

    public function getTimezones(): JsonResponse
    {
        return response()->json(array_values(config('timezone.zones')));
    }

    protected function addLocaleSuffix(): string
    {
        return ':' . strtolower(app()->getLocale());
    }
}
