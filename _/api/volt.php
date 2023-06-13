<?php

use Illuminate\Support\Str;

function volt_setting($name, $default = '')
{
    static $cache;

    // slugify to allow for legacy calls with are using
    // the label for the setting instead of the name
    $name = Str::slug($name, '_');

    $setting = setting($name);

    // cache the formatted value of the setting
    if ($setting && ! isset($cache[$name])) {
        switch ($setting->type) {
            case 'category':
                $category = \Ds\Models\ProductCategory::where('name', $setting->value)->first();
                $cache[$name] = $category ? $category : null;
                break;
            case 'html':
                $cache[$name] = do_shortcode($setting->value ?? '');
                break;
            case 'raw-html':
                $cache[$name] = liquid($setting->value ?? '', reqcache('render_template_assigns') ?? [], $name);
                break;
            case 'product':
                $product = \Ds\Models\Product::find($setting->value);
                $cache[$name] = $product ? $product : null;
                break;
            case 'media':
                $media = \Ds\Models\Media::find($setting->value);
                $cache[$name] = $media;
                break;
            default:
                $cache[$name] = $setting->value;
        }

        switch ($setting->name) {
            case 'conversion_scripts':
                if (\Illuminate\Support\Facades\Route::is(['order_review', 'frontend.orders.thank_you'])) {
                    $cart = \Ds\Models\Order::where('client_uuid', request()->route('name'))->first();
                } else {
                    $cart = \Ds\Models\Order::getActiveSession();
                }
                $data = [
                    '[order_id]' => $cart->client_uuid ?? '',
                    '[order_amount]' => $cart->totalamount ?? '',
                ];
                $cache[$name] = liquid($setting->value ?? '', ['cart' => $cart], $name);
                $cache[$name] = str_replace(array_keys($data), array_values($data), $cache[$name]);
                break;
        }
    }

    return $cache[$name] ?? $default;
}

function volt_explode($str, $delimiter = ',')
{
    if (empty($str)) {
        return [];
    }

    $data = explode($delimiter, $str);

    return array_map('trim', $data);
}

function volt_selected($var, $value, $default = false)
{
    if (is_array($value)) {
        return (in_array($var, $value) || (empty($var) && $default)) ? 'selected' : '';
    }

    if ($var == $value || (empty($var) && $default)) {
        return 'selected';
    }
}

function volt_checked($var, $value)
{
    if (is_array($value)) {
        return (in_array($var, $value)) ? 'checked' : '';
    }

    if ($var == $value) {
        return 'checked';
    }
}

function volt_has_account_feature($str)
{
    return in_array($str, explode(',', sys_get('account_login_features')));
}
