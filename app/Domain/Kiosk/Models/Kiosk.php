<?php

namespace Ds\Domain\Kiosk\Models;

use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Product;
use Illuminate\Support\Arr;

class Kiosk extends Model implements Metadatable
{
    use HasMetadata;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'products',
    ];

    /**
     * Relationship: Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship: Sessions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sessions()
    {
        return $this->hasMany(KioskSession::class);
    }

    /**
     * Scope: Enabled
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeEnabled($query)
    {
        $query->where('enabled', true);
    }

    /**
     * Attribute Accessor: Products
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Suppport\Collection
     */
    public function getProductsAttribute()
    {
        $ids = explode_ids($this->product_ids);

        if (count($ids)) {
            return Product::with('customFields')->whereIn('id', $ids)->get();
        }

        return collect();
    }

    /**
     * Attribute Accessor: Product IDs
     *
     * @return string
     */
    public function getProductIdsAttribute()
    {
        return (string) ($this->metadata['product_ids'] ?: $this->product_id);
    }

    /**
     * Attribute Mutator: Product IDs
     *
     * @param string $value
     */
    public function setProductIdsAttribute($value)
    {
        $this->setMetadata('product_ids', $value);

        // set product_id for backwards compatibility
        $this->product_id = explode(',', $value)[0] ?: null;
    }

    /**
     * Attribute Accessor: Config
     *
     * @return \Illuminate\Config\Repository|array
     */
    public function getConfigAttribute()
    {
        if ($this->attributes['config']) {
            return json_decode($this->attributes['config'], true);
        }

        return $this->getDefaultConfig();
    }

    /**
     * Attribute Mutator: Config
     *
     * @param array|null $config
     */
    public function setConfigAttribute(?array $config = [])
    {
        Arr::set($config, 'core.timeout', (float) Arr::get($config, 'core.timeout'));
        Arr::set($config, 'core.is_onetime', (bool) Arr::get($config, 'core.is_onetime'));
        Arr::set($config, 'core.is_monthly', (bool) Arr::get($config, 'core.is_monthly'));
        Arr::set($config, 'core.is_recurring', (bool) Arr::get($config, 'core.is_recurring'));
        Arr::set($config, 'core.cover_fees', (bool) Arr::get($config, 'core.cover_fees'));
        Arr::set($config, 'core.cover_fees_default', (bool) Arr::get($config, 'core.cover_fees_default'));
        Arr::set($config, 'core.custom_fields', (bool) Arr::get($config, 'core.custom_fields'));
        Arr::set($config, 'checkout.enable_title', (bool) Arr::get($config, 'checkout.enable_title'));
        Arr::set($config, 'checkout.enable_account_type', (bool) Arr::get($config, 'checkout.enable_account_type'));
        Arr::set($config, 'checkout.enable_address', (bool) Arr::get($config, 'checkout.enable_address'));
        Arr::set($config, 'checkout.enable_phone', (bool) Arr::get($config, 'checkout.enable_phone'));
        Arr::set($config, 'checkout.enable_referral_source', (bool) Arr::get($config, 'checkout.enable_referral_source'));
        Arr::set($config, 'checkout.enable_comments', (bool) Arr::get($config, 'checkout.enable_comments'));
        Arr::set($config, 'checkout.request_billing', (bool) Arr::get($config, 'checkout.request_billing'));
        Arr::set($config, 'checkout.require_billing', (bool) Arr::get($config, 'checkout.require_billing'));
        Arr::set($config, 'splash_screen.enabled', (bool) Arr::get($config, 'splash_screen.enabled'));

        $this->attributes['config'] = json_encode($config);
    }

    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param array|string $key
     * @param mixed $default
     * @return mixed
     */
    public function config($key = null, $default = null)
    {
        $config = $this->config;

        if (is_null($key)) {
            return $config;
        }

        if (is_array($key)) {
            foreach ($key as $index => $value) {
                Arr::set($config, $index, $value);
            }

            $this->setConfigAttribute($config);
            $this->save();

            return $config;
        }

        return Arr::get($config, $key, Arr::get($this->getDefaultConfig(), $key, $default));
    }

    /**
     * Attribute Mutator: Default Config
     *
     * @return array
     */
    private function getDefaultConfig()
    {
        return [
            'core' => [
                'timeout' => 1,
                'is_onetime' => true,
                'is_monthly' => true,
                'is_recurring' => false,
                'amount_presets' => '10,25,50,100,250,Other',
                'default_amount' => null,
                'cover_fees' => false,
                'cover_fees_default' => false,
                'custom_fields' => false,
            ],
            'checkout' => [
                'enable_title' => false,
                'enable_account_type' => false,
                'enable_address' => true,
                'enable_phone' => true,
                'enable_referral_source' => true,
                'enable_comments' => true,
                'request_billing' => false,
                'require_billing' => false,
            ],
            'theme' => [
                'palette' => [
                    'primary' => '#005b8e',
                    'success' => '#5b8e01',
                    'warning' => '#ff8e02',
                    'failure' => '#fd5252',
                    'system' => '#555555',
                ],
                'background' => [
                    'color' => '#004778',
                    'image_url' => null,
                ],
                'primary_heading' => [
                    'font_family' => 'Montserrat',
                    'font_weight' => 'bold',
                    'font_size' => '100px',
                    'color' => '#ffffff',
                ],
                'secondary_heading' => [
                    'font_family' => 'Montserrat',
                    'font_weight' => 'bold',
                    'font_size' => '35px',
                    'color' => '#ffffff',
                ],
                'body_text' => [
                    'font_family' => "'Droid Sans'",
                    'font_weight' => 'normal',
                    'font_size' => '18px',
                    'color' => '#ffffff',
                ],
                'field_labels' => [
                    'font_family' => "'Droid Sans'",
                    'font_weight' => 'normal',
                    'font_size' => '18px',
                    'color' => '#ffffff',
                ],
                'primary_btn' => [
                    'font_family' => 'Montserrat',
                    'font_weight' => 'normal',
                    'font_size' => '30px',
                    'background_color' => '',
                    'color' => '#f8f9fa',
                    'padding' => '16px 8px 16px 8px',
                    'border_radius' => '60px',
                ],
                'secondary_btn' => [
                    'font_family' => "'Droid Sans'",
                    'font_weight' => 'normal',
                    'font_size' => '18px',
                    'background_color' => '',
                    'color' => '#f8f9fa',
                    'padding' => '12px 20px 10px 20px',
                    'border_radius' => '36px',
                ],
                'screen_transition' => false,
            ],
            'splash_screen' => [
                'enabled' => true,
                'background' => [
                    'color' => '#004778',
                    'image_url' => null,
                ],
                'content' => '',
            ],
            'checkout_screen' => [
                'heading_text' => 'Donate',
                'paynow_btn_text' => 'Pay Now',
                'cancel_btn_text' => 'Cancel donation',
                'thanks_text' => '<i class="fa fa-check"></i> Wahoo! <small>Thank you for your generosity!</small>',
                'donation_text' => 'Make another donation',
                'swipe_location' => 'top-right',
            ],
        ];
    }
}
