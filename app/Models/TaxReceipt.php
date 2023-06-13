<?php

namespace Ds\Models;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\ChangeTracking;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Member as Account;
use Ds\Models\Observers\TaxReceiptObserver;
use Ds\Services\EmailService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Swift_Message;
use Swift_Validate;

class TaxReceipt extends Model implements Auditable, Liquidable
{
    use ChangeTracking;
    use HasAuditing;
    use Permissions;
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use Userstamps;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'issued_at' => 'date',
        'ordered_at' => 'date',
        'amount' => 'float',
        'versions' => 'array',
        'changes' => 'array',
        'voided_at' => 'datetime',
    ];

    /**
     * Attributes hidden from serialization.
     *
     * @var array
     */
    protected $hidden = [
        'versions',
        'changes',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'ordered_at',
        'full_address',
    ];

    /**
     * Each column's English label (for change tracking)
     *
     * @var array
     */
    protected $tracked = [
        'name' => 'Name',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'address_01' => 'Address Line 1',
        'address_02' => 'Address Line 2',
        'city' => 'City',
        'state' => 'State/Province',
        'zip' => 'ZIP/Postal Code',
        'country' => 'Country',
        'issued_at' => 'Issued Date',
        'amount' => 'Amount',
        'number' => 'Number',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(new TaxReceiptObserver);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'account_id');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(TaxReceiptLineItem::class)
            ->orderBy('donated_at', 'asc');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'tax_receipt_line_items', 'tax_receipt_id', 'order_id')
            ->withSpam()
            ->using(TaxReceiptLineItem::class);
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'tax_receipt_line_items', 'tax_receipt_id', 'transaction_id')
            ->using(TaxReceiptLineItem::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaxReceiptTemplate::class, 'tax_receipt_template_id');
    }

    /**
     * Get the full display name of the member.
     *
     * @return string
     */
    public function getOrderedAtAttribute()
    {
        if (count($this->orders)) {
            return $this->orders->first()->ordered_at;
        }

        if (count($this->transactions)) {
            return $this->transactions->first()->order_time;
        }

        return $this->issued_at;
    }

    /**
     * Get the full formatted address.
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        // add html <br>'s
        return str_replace(chr(10), '<br>', address_format(
            e($this->address_01),
            e($this->address_02),
            e($this->city),
            e($this->state),
            e($this->zip),
            e($this->country),
        ));
    }

    /**
     * Get the full name of the person issued this receipt.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the filename attribute.
     *
     * @return string
     */
    public function getFilenameAttribute()
    {
        return 'TaxReceipt_' . $this->number . '.pdf';
    }

    /**
     * Issue a tax receipt.
     */
    public function issue()
    {
        if ($this->status === 'draft') {
            $this->status = 'issued';
            $this->issued_at = $this->issued_at ?? fromLocal('now');
            $this->save();
        }
    }

    /**
     * Revise a tax receipt.
     *
     * @param array $changes
     */
    public function revise(array $changes)
    {
        // Perform a refresh to ensure that there are no external changes being made on the
        // model as it is being revised. Very important to keep close track of all updates.
        $this->refresh();

        // save version
        $previousVersion = $this->toArray();

        // make each change
        foreach ($changes as $attribute => $value) {
            if ($attribute === 'amount') {
                $value = numeral($value)->toFloat() ?? 0;
            }

            $this->setAttribute($attribute, $value);
        }

        // update receipt number
        $this->number = $this->formatNumber(count($this->changes) + 1);

        // track version
        $all_versions = (is_array($this->versions)) ? array_merge($this->versions, []) : [];
        $all_versions[] = $previousVersion;
        $this->versions = $all_versions;

        // save changes
        $this->trackChanges();

        // save the receipt
        $this->save();
    }

    /**
     * Void or delete a tax receipt.
     */
    public function void()
    {
        if ($this->status === 'draft') {
            $this->delete();
        } elseif ($this->status === 'issued') {
            $this->status = 'void';
            $this->voided_at = now();
            $this->voided_by = user('id');
            $this->save();
        }
    }

    /**
     * Set the account.
     *
     * @param \Ds\Models\Member $account
     */
    public function setAccount(Account $account)
    {
        $this->account_id = $account->id;
        $this->name = ucwords("{$account->bill_first_name} {$account->bill_last_name}");
        $this->first_name = $account->bill_first_name;
        $this->last_name = $account->bill_last_name;
        $this->email = $account->bill_email;
        $this->address_01 = $account->bill_address_01;
        $this->address_02 = $account->bill_address_02;
        $this->city = $account->bill_city;
        $this->state = $account->bill_state;
        $this->zip = $account->bill_zip;
        $this->country = $account->bill_country;
        $this->phone = $account->bill_phone;

        if ($account->accountType && $account->accountType->is_organization) {
            $this->name = trim($account->bill_title . ' ' . ucwords($account->bill_organization_name));
        }
    }

    /**
     * Set the template.
     *
     * @param \Ds\Models\TaxReceiptTemplate $template
     */
    public function setTemplate(TaxReceiptTemplate $template)
    {
        if ($template->template_type === 'revision') {
            $this->tax_receipt_template_id = $template->id;
        } else {
            if (! $template->latestRevision || $template->latestRevision->created_at->lessThan($template->updated_at)) {
                $template->createRevision();
            }

            $this->tax_receipt_template_id = $template->latest_revision_id;
        }
    }

    /**
     * Recalculate the tax receipt.
     */
    private function recalculate()
    {
        $this->amount = $this->lineItems()->sum('amount');
    }

    /**
     * Creates a tax receipt from an order_id.
     *
     * @param string $order_id ID of the order we are creating a tax receipt for
     * @param bool $auto_notify whether or not this function should automatically
     *                          notify the receipt owner
     * @return \Ds\Models\TaxReceipt|void
     */
    public static function createFromOrder($order_id, $auto_notify = false)
    {
        // is the tax receipt feature enabled
        if (! feature('tax_receipt') || ! sys_get('tax_receipt_pdfs')) {
            return;
        }

        // grab the order details
        $order = Order::withSpam()->find($order_id);

        // if no order found, bail
        if (! $order) {
            throw new MessageException('Failed to create tax receipt. No contribution found for ID (' . $order_id . ').');
        }

        // make sure there isn't already a receipt associated with this order.
        if ($existing_receipt = $order->taxReceipt) {
            throw new MessageException('Failed to create tax receipt. Tax receipt ' . $existing_receipt->number . ' already exists for contribution ID (' . $order_id . ').');
        }

        // check order country
        if (! in_array(sys_get('tax_receipt_country'), [$order->member->bill_country ?? $order->billingcountry, 'ANY'], true)) {
            throw new MessageException('Failed to create tax receipt. Contribution #' . $order->invoicenumber . ' was billed to ' . $order->billingcountry . '. Receipts can only be issued on ' . sys_get('tax_receipt_country') . ' contributions.');
        }

        // bail if the order was refunded
        if ($order->is_refunded) {
            throw new MessageException('Failed to create tax receipt. Contribution #' . $order->invoicenumber . ' has been refunded.');
        }

        // bail if there is no receiptable amount
        if ($order->receiptable_amount == 0) {
            throw new MessageException('Failed to create tax receipt. Contribution #' . $order->invoicenumber . ' has no receiptable amount.');
        }

        $template = TaxReceiptTemplate::query()
            ->where('is_default', true)
            ->firstOrFail();

        // create a new tax receipt
        $receipt = new static;
        $receipt->status = 'issued';
        $receipt->receipt_type = 'single';
        $receipt->issued_at = fromLocal('now');
        $receipt->currency_code = $order->currency_code;
        $receipt->name = ($order->accountType && $order->accountType->is_organization) ? ucwords($order->billing_organization_name) : (($order->billing_title) ? ($order->billing_title . ' ') : '') . ucwords($order->billing_first_name . ' ' . $order->billing_last_name);
        $receipt->email = $order->billingemail;
        $receipt->address_01 = $order->billingaddress1;
        $receipt->address_02 = $order->billingaddress2;
        $receipt->city = $order->billingcity;
        $receipt->state = $order->billingstate;
        $receipt->zip = $order->billingzip;
        $receipt->country = $order->billingcountry;
        $receipt->phone = $order->billingphone;

        if ($order->member) {
            $receipt->setAccount($order->member);
        }

        $receipt->setTemplate($template);
        $receipt->save();

        $receipt->attachOrder($order);

        // if we need to notify, do it
        if ($auto_notify) {
            $receipt->notify();
        }

        // return receipt
        return $receipt;
    }

    /**
     * Attach a order to the receipt.
     *
     * @param \Ds\Models\Order $order
     */
    public function attachOrder(Order $order)
    {
        $glCodes = $order->items->where('receiptable_amount', '>', 0)->pluck('gl_code')->implode(', ');

        $this->orders()->attach($order->id, [
            'description' => "Contribution #{$order->client_uuid}",
            'amount' => $order->receiptable_amount,
            'currency_code' => $order->currency_code,
            'donated_at' => $order->ordered_at ?? $order->confirmationdatetime,
            'gl_code' => $glCodes ?: null,
        ]);

        $this->recalculate();
        $this->save();
    }

    /**
     * Determine the receiptable amount of an entire order.
     *
     * @param \Ds\Models\Order $order
     * @return float
     */
    public static function getReceiptableAmountFromOrder($order)
    {
        // if this order has been refunded, its ZERO
        if ($order->is_refunded) {
            return 0;
        }

        return $order->items->sum('receiptable_amount');
    }

    /**
     * Creates a tax receipt from an transaction_id (recurring payments).
     *
     * @return \Ds\Models\TaxReceipt|void
     */
    public static function createFromTransaction($transaction_id)
    {
        // is the tax receipt feature enabled
        if (! feature('tax_receipt') || ! sys_get('tax_receipt_pdfs')) {
            return;
        }

        // grab the order details
        $transaction = Transaction::find($transaction_id);

        // if no order found, bail
        if (! $transaction) {
            throw new MessageException('Failed to create tax receipt. No transaction found with ID ' . $transaction_id . '.');
        }

        // make sure there isn't already a receipt associated with this order.
        if ($existing_receipt = $transaction->taxReceipt) {
            throw new MessageException('Failed to create tax receipt. Tax receipt ' . $existing_receipt->number . ' already exists for transaction ID ' . $transaction_id . '.');
        }

        // if payment failed, bail
        if (! $transaction->is_payment_accepted) {
            throw new MessageException('Failed to create tax receipt. Transaction ID ' . $transaction_id . ' has no payment.');
        }

        // check order country
        if (! in_array(sys_get('tax_receipt_country'), [$transaction->recurringPaymentProfile->member->bill_country ?? $transaction->recurringPaymentProfile->order->billingcountry, 'ANY'], true)) {
            throw new MessageException('Failed to create tax receipt. ' . $transaction->recurringPaymentProfile->member->display_name . ' (ID ' . $transaction->recurringPaymentProfile->member->id . ') has an invalid billing address. Receipts can only be issued in ' . sys_get('tax_receipt_country') . '.');
        }

        // bail if the product / sponsorship is not receiptable
        if ($transaction->recurringPaymentProfile->product && ! $transaction->recurringPaymentProfile->product->is_tax_receiptable) {
            throw new MessageException('Failed to create tax receipt. The product associated with this recurring payment is not receiptable.');
        }

        if ($transaction->recurringPaymentProfile->sponsorship && sys_get('sponsorship_tax_receipts') != 1) {
            throw new MessageException('Failed to create tax receipt. The sponsorship payments cannot be receipted.');
        }

        $template = TaxReceiptTemplate::query()
            ->where('is_default', true)
            ->firstOrFail();

        // create a new tax receipt
        $receipt = new static;
        $receipt->status = 'issued';
        $receipt->receipt_type = 'single';
        $receipt->issued_at = fromLocal('now');
        $receipt->currency_code = $transaction->currency_code;
        $receipt->setAccount($transaction->recurringPaymentProfile->member);
        $receipt->setTemplate($template);
        $receipt->save();

        $receipt->attachTransaction($transaction);

        // return receipt
        return $receipt;
    }

    /**
     * Attach a transaction to the receipt.
     *
     * @param \Ds\Models\Transaction $transaction
     */
    public function attachTransaction(Transaction $transaction)
    {
        $this->transactions()->attach($transaction->id, [
            'description' => "Recurring Payment #{$transaction->recurringPaymentProfile->profile_id}-{$transaction->id}",
            'amount' => $transaction->amt,
            'currency_code' => $transaction->currency_code,
            'donated_at' => $transaction->order_time,
            'gl_code' => $transaction->recurringPaymentProfile->gl_code ?: null,
        ]);

        $this->recalculate();
        $this->save();
    }

    /**
     * Creates a formatted tax receipt number.
     *
     * @return \Ds\Models\TaxReceipt
     */
    public function formatNumber($revision = null)
    {
        // base formatting
        $str = sys_get('tax_receipt_number_format');

        // final string
        $str = str_replace('[YY]', toLocalFormat($this->issued_at, 'y'), $str);
        $str = str_replace('[YYYY]', toLocalFormat($this->issued_at, 'Y'), $str);

        // number format
        $str = preg_replace_callback('/\[(0+)\]/', fn ($m) => str_pad((string) $this->id, strlen($m[1]), '0', STR_PAD_LEFT), $str);

        // track revisions
        if ($revision) {
            $str .= '-R' . $revision;
        }

        // return final number
        return $str;
    }

    /**
     * Uses 'toArray()' and a couple formatters to build out an array of values to be used as merge tags in emails and pdf template
     *
     * @return array
     */
    public function toMergeTagArray()
    {
        // base tax receipt data
        $data = $this->attributesToArray();

        // additionally formatted merge tags
        $data['issued_at'] = toLocalFormat($this->issued_at, 'M j, Y');
        $data['ordered_at'] = toLocalFormat($this->ordered_at, 'M j, Y');
        $data['amount'] = number_format($data['amount'], 2); // number_format() fixes an issue where amount had a bunch of decimal places
        $data['changes'] = str_replace(chr(10), '<br>', implode('<br><br>', $this->changes_formatted));

        if ($this->receipt_type === 'consolidated' && count($this->lineItems)) {
            $summaryTable = '<style type="text/css">table.summary-table th, table.summary-table td {border: 1px solid black;font-family:Helvetica,Arial,sans-serif;padding:6px 8px 4px;}</style>';
            $summaryTable .= '<table cellpadding="0" cellspacing="0" border="0" class="summary-table" style="width:500px;border-collapse:collapse;border: 1px solid black;">';
            $summaryTable .= '<tr>';
            $summaryTable .= '<th style="text-align:left;font-size:12px;">Date</th>';
            if (sys_get('tax_receipt_summary_include_gl')) {
                $summaryTable .= '<th style="text-align:left;font-size:12px;">Fund</th>';
            }
            if (sys_get('tax_receipt_summary_include_description')) {
                $summaryTable .= '<th style="text-align:left;font-size:12px;">Name</th>';
            }
            $summaryTable .= '<th style="text-align:left;font-size:12px;">Amount</th>';
            $summaryTable .= '</tr>';
            foreach ($this->lineItems as $item) {
                $summaryTable .= '<tr>';
                $summaryTable .= '<td style="font-size:12px;">' . toLocalFormat($item->donated_at) . '</td>';
                if (sys_get('tax_receipt_summary_include_gl')) {
                    $summaryTable .= '<td style="font-size:12px;">' . $item->gl_code . '</td>';
                }
                if (sys_get('tax_receipt_summary_include_description')) {
                    $summaryTable .= '<td style="font-size:12px;">' . $item->description . '</td>';
                }
                $summaryTable .= '<td style="font-size:12px;">' . money($item->amount, $item->currency_code) . '</td>';
                $summaryTable .= '</tr>';
            }
            $summaryTable .= '<tr>';
            if (sys_get('tax_receipt_summary_include_gl') && sys_get('tax_receipt_summary_include_description')) {
                $summaryTable .= '<td colspan="3" style="font-size:12px; font-weight:bold;"></td>';
            } elseif (sys_get('tax_receipt_summary_include_gl') || sys_get('tax_receipt_summary_include_description')) {
                $summaryTable .= '<td colspan="2" style="font-size:12px; font-weight:bold;"></td>';
            } else {
                $summaryTable .= '<td colspan="1" style="font-size:12px; font-weight:bold;"></td>';
            }
            $summaryTable .= '<td style="font-size:12px; font-weight:bold;">' . money($this->amount, $this->currency_code) . '</td>';
            $summaryTable .= '</tr>';
            $summaryTable .= '</table>';
            $data['summary_table'] = $summaryTable;
        } else {
            $data['summary_table'] = '';
        }

        // return the data for merge tags, combined with the globally available merge tags (shop_url, etc)
        return array_merge($data, global_merge_tags());
    }

    /**
     * Builds and returns the unique HTML template for this tax receipt to be displayed or converted to PDF.
     *
     * @return string
     */
    public function toHtmlTemplate()
    {
        $bannerNotice = '';

        if ($this->status === 'draft') {
            $bannerNotice = '<div style="background-color:#ccc; text-align:center; width:100%; margin-bottom:15px; padding:10px 0; font-size:16px; font-weight:bold; font-family:monospace; color:#000;">DRAFT</div>';
        } elseif ($this->status === 'void') {
            $bannerNotice = '<div style="background-color:#f30; text-align:center; width:100%; margin-bottom:15px; padding:10px 0; font-size:16px; font-weight:bold; font-family:monospace; color:#fff;">RECEIPT VOID ON ' . strtoupper($this->voided_at->format('M j, Y')) . '</div>';
        }

        $html = string_substituteFromArray($this->template->body, $this->toMergeTagArray());

        return $bannerNotice . $html . $bannerNotice;
    }

    /**
     * Uses PDF library to produce a PDF object that can then be saved, streamed, downloaded, etc.
     *
     * @return \Ds\Common\Pdf
     */
    public function toPDF()
    {
        return app('pdf')
            ->loadHtml($this->toHtmlTemplate())
            ->setFilename($this->filename)
            ->setProtected(true);
    }

    /**
     * Email notifies the owner of the tax receipt.
     *
     * @return void
     */
    public function notify()
    {
        // bail if email is not valid
        if (trim($this->email) === '' || ! Swift_Validate::email($this->email)) {
            throw new MessageException('Failed to send notification for Tax Receipt "' . $this->number . '" (' . $this->id . '). "' . $this->email . '" is not a valid email.');
        }
        // get email template
        $template = \Ds\Models\Email::where('type', 'customer_tax_receipt')->where('is_active', 1)->first();

        if ($template) {
            // email body & subject
            $body = string_substituteFromArray($template->body_template, $this->toMergeTagArray());
            $subject = string_substituteFromArray($template->subject, $this->toMergeTagArray());

            // prep message params
            $message = (new Swift_Message)
                ->setFrom(sys_get('email_from_address'), sys_get('email_from_name'))
                ->addTo($this->email, $this->first_name . ' ' . $this->last_name)
                ->setSubject($subject)
                ->setBody($body, 'text/html');

            // cc
            if ($template->cc !== '') {
                $message->setCc(app(EmailService::class)->getValidEmailsFromString($template->cc));
            }

            // bcc
            if ($template->bcc !== '') {
                $message->setBcc(app(EmailService::class)->getValidEmailsFromString($template->bcc));
            }

            // attach receipt
            $message->attach($this->toPDF()->toSwiftAttachment());

            // now actually send the message
            send_using_swiftmailer($message);
        }
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'TaxReceipt');
    }
}
