<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

function dpo_is_enabled()
{
    return (sys_get('dpo_user') && sys_get('dpo_pass')) || sys_get('dpo_api_key');
}

function dpo_is_connected()
{
    if (! dpo_is_enabled()) {
        return false;
    }

    static $is_connected;

    if ($is_connected === null) {
        if (dpo_is_enabled()) {
            $is_connected = app('\Ds\Services\DonorPerfectService')->ping();
        } else {
            $is_connected = false;
        }
    }

    return $is_connected;
}

/**
 * @param string $action
 * @param string|array $params
 * @param bool $throw Flags whether or not dpo_request should throw an error on failure or not
 * @return \Illuminate\Support\Collection
 */
function dpo_request($action, $params = [], $throw = false)
{
    try {
        return app('dpo')->request($action, $params);
    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        if ($throw) {
            throw $e;
        }
    } catch (\Throwable $throwable) {
        notifyException($throwable, function ($report) use ($action, $params) {
            $report->setMetaData([
                'dpo_action' => $action,
                'dpo_params' => $params,
            ]);
        });

        if ($throw) {
            throw $throwable;
        }
    }

    return collect();
}

function dpo_castValue($value, $cast = 'string')
{
    $value = (string) $value;

    if ($value === '') {
        return null;
    }

    switch (strtolower($cast)) {
        case 'date':
            return fromLocalFormat($value, 'Y-m-d');
        case 'datetime':
            return fromLocalFormat($value, 'Y-m-d H:i:s');
        case 'bool':
        case 'boolean':
            if (is_string($value) && strtolower($value) === 'false') {
                return false;
            }

            return (bool) $value;
        case 'float':
        case 'double':
            return (float) $value;
        case 'int':
        case 'integer':
            return (int) $value;
        case 'string':
        default:
            return (string) $value;
    }

    return null;
}

function dpo_gift_goal_progress($gift_data)
{
    // goal data defaults
    $gift_data_defaults = ['gl_code' => '', 'campaign' => '', 'solicit_code' => '', 'sub_solicit_code' => ''];
    foreach ($gift_data_defaults as $opt => $value) {
        if (! isset($gift_data[$opt])) {
            $gift_data[$opt] = $value;
        }
    }

    // cache this for 1hr
    // cache key (rated_weight_city_prov_postal_country)
    $cache_key = 'dpo_goal_' . $gift_data['gl_code'] . '_' . $gift_data['campaign'] . '_' . $gift_data['solicit_code'] . '_' . $gift_data['sub_solicit_code'];
    $minutes = now()->addMinutes(1);

    // return a cached set of results
    return Cache::untilUpdated($cache_key, $minutes, function () use ($gift_data) {
        try {
            $query = app('dpo')->table('dpgift')
                ->select([
                    DB::raw('COUNT(gift_id) AS gift_count'),
                    DB::raw('SUM(amount) AS total_amount'),
                ])->where('record_type', 'G');

            foreach (['gl_code', 'campaign', 'solicit_code', 'sub_solicit_code'] as $column) {
                if ($gift_data[$column]) {
                    $query->where($column, $gift_data[$column]);
                }
            }

            if ($gift = $query->first()) {
                return (object) [
                    'gift_count' => dpo_castValue($gift->gift_count, 'float'),
                    'total_amount' => dpo_castValue($gift->total_amount, 'float'),
                ];
            }
        } catch (Throwable $e) {
            // ignore errors
        }

        return null;
    });
}

function dpo_get_membership_for_donor($donor_id)
{
    $results = dpo_request(sprintf(
        "SELECT d.first_name, d.last_name, m.mcat, c.description, m.mcat_enroll_date, m.mcat_expire_date
            FROM dp d
            INNER JOIN dpudf m ON m.donor_id = d.donor_id
            INNER JOIN dpcodes c ON c.code = m.mcat AND c.field_name = 'MCAT'
            WHERE d.donor_id = %d",
        (int) $donor_id
    ));

    if (count($results)) {
        return $results[0];
    }
}

function dpo_get_gift_history_for_donor($donor_id, $start_date = null, $end_date = null, $gl_codes = null, $gift_types = null)
{
    // validate start date
    if (trim($start_date) !== '') {
        try {
            $start_date = fromLocal($start_date);
        } catch (Throwable $e) {
            $start_date = false;
        }
    } else {
        $start_date = false;
    }

    // validate end date
    if (trim($end_date) !== '') {
        try {
            $end_date = fromLocal($end_date);
        } catch (Throwable $e) {
            $end_date = false;
        }
    } else {
        $end_date = false;
    }

    // build date filter
    $date_filters = '';
    if ($start_date && $end_date) {
        $date_filters .= " AND g.gift_date >= '" . $start_date->format('Y-m-d') . "' AND g.gift_date <= '" . $end_date->format('Y-m-d') . "'";
    } elseif ($start_date) {
        $date_filters .= " AND g.gift_date >= '" . $start_date->format('Y-m-d') . "'";
    } elseif ($end_date) {
        $date_filters .= " AND g.gift_date <= '" . $end_date->format('Y-m-d') . "'";
    }

    // build gl filter
    $gl_filters = '';
    if ($gl_codes) {
        $gl_codes_safe = [];
        foreach (explode(',', $gl_codes) as $gl) {
            $gl_codes_safe[] = "'" . app('dpo.client')->escape($gl) . "'";
        }
        $gl_filters = ' AND g.gl_code IN (' . implode(',', $gl_codes_safe) . ')';
    }

    // build gift type filter
    $gift_type_filters = '';
    if ($gift_types) {
        $gift_types_safe = [];
        foreach (explode(',', $gift_types) as $gt) {
            $gift_types_safe[] = "'" . app('dpo.client')->escape($gt) . "'";
        }
        $gift_type_filters = ' AND g.gift_type IN (' . implode(',', $gift_types_safe) . ')';
    }

    $results = dpo_request(sprintf(
        "SELECT g.donor_id,
                COUNT(*) AS gift_count,
                SUM(g.amount) AS total_amount
            FROM dpgift g
            WHERE g.donor_id = %d
                AND g.record_type = 'G'
                " . $date_filters . '
                ' . $gl_filters . '
                ' . $gift_type_filters . '
            GROUP BY g.donor_id',
        (int) $donor_id
    ));

    if (count($results) === 0) {
        return [
            'gift_count' => 0,
            'total_amount' => 0.0,
            'gifts' => [],
        ];
    }

    $return_object = (object) [
        'gift_count' => dpo_castValue($results[0]->gift_count, 'int'),
        'total_amount' => dpo_castValue($results[0]->total_amount, 'float'),
        'gifts' => [],
    ];

    $results = dpo_request(sprintf(
        "SELECT gift_id, gift_date, currency, fmv, rcpt_num, amount, reference
            FROM dpgift g
            WHERE g.donor_id = %d
                AND g.record_type = 'G'
                " . $date_filters . '
                ' . $gl_filters . '
                ' . $gift_type_filters . '
            ORDER BY gift_date DESC',
        (int) $donor_id
    ));

    foreach ($results as $gift) {
        array_push($return_object->gifts, (object) [
            'gift_id' => dpo_castValue($gift->gift_id, 'int'),
            'gift_date' => dpo_castValue($gift->gift_date, 'date'),
            'currency' => dpo_castValue($gift->currency),
            'fmv' => dpo_castValue($gift->fmv, 'float'),
            'rcpt_num' => dpo_castValue($gift->rcpt_num),
            'amount' => dpo_castValue($gift->amount, 'float'),
        ]);
    }

    return $return_object;
}

function dpo_donorCount()
{
    static $count;
    if (isset($count)) {
        return $count;
    }

    // count the number of donors
    $aggregate = dpo_request('SELECT COUNT(*) as counter FROM dp');

    // if we got a result, show it
    if (isset($aggregate[0]->counter) && is_numeric($aggregate[0]->counter)) {
        $count = (int) $aggregate[0]->counter;
    } else {
        $count = 0;
    }

    return $count;
}
