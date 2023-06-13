<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TaxReceiptLineItem extends Pivot
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tax_receipt_line_items';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'double',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withSpam();
    }

    public function taxReceipt(): BelongsTo
    {
        return $this->belongsTo(TaxReceipt::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Attribute Accessor: HTML Description
     *
     * @return string
     */
    public function getHtmlDescriptionAttribute()
    {
        if (Str::startsWith($this->description, ['Contribution #', 'Order #'])) {
            $description = preg_replace('/^(?:Contribution|Order) #/', '', $this->description);

            return sprintf('Contribution <a href="%s">#%s</a>', route('backend.orders.edit_without_id', ['c' => $description]), $description);
        }

        if (Str::startsWith($this->description, 'Recurring Payment #')) {
            return preg_replace(
                '/^(Recurring Payment )#(.*?)(-.*)$/',
                '$1<a href="/jpanel/recurring_payments/$2">#$2$3</a>',
                $this->description
            );
        }

        return $this->description;
    }
}
