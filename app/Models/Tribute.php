<?php

namespace Ds\Models;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Services\EmailService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Swift_Message;
use Swift_Validate;

class Tribute extends Model implements Auditable, Liquidable
{
    use HasAuditing;
    use Permissions;
    use SoftDeleteUserstamp;
    use SoftDeletes;
    use Userstamps;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Attributes to exclude from the Audit.
     *
     * @var array
     */
    protected $auditExclude = [
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'float',
        'notify_at' => 'date',
        'notified_at' => 'date',
    ];

    /**
     * Attributes hidden from serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function tributeType(): BelongsTo
    {
        return $this->belongsTo(TributeType::class);
    }

    /**
     * Scope: Unsent letters
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeUnsentLetters($query)
    {
        return $query->where('notify', 'letter') // only letters
            ->whereNull('notified_at') // where it hasn't been sent
            ->where(function ($query) {
                return $query->where('notify_at', '<=', now()) // where the intended notification date is today or in the past
                    ->orWhereNull('notify_at'); // OR there is no intended notification date (meaning, send it asap)
            })->orderBy('notify_at'); // order by when it needs to be sent (oldest first);
    }

    /**
     * Get the full formatted address.
     *
     * @return string
     */
    public function getNotifyFullAddressAttribute()
    {
        // add html <br>'s
        return str_replace(chr(10), '<br>', address_format(
            e($this->notify_address),
            '',
            e($this->notify_city),
            e($this->notify_state),
            e($this->notify_zip),
            e($this->notify_country),
        ));
    }

    /**
     * Get the filename attribute.
     *
     * @return string
     */
    public function getFilenameAttribute()
    {
        return 'Letter.pdf';
    }

    /**
     * Uses 'toArray()' and a couple formatters to build out an array of values to be used as merge tags in emails and pdf template
     *
     * @return array
     */
    public function toMergeTagArray()
    {
        // base model data
        $data = $this->toArray();

        // additionally formatted merge tags
        $data['created_at'] = toLocalFormat($this->created_at, 'M j, Y');
        $data['notify_at'] = toLocalFormat($this->notify_at ?? $this->created_at, 'M j, Y');
        $data['notified_at'] = toLocalFormat($this->notified_at, 'M j, Y') ?? '';
        $data['amount'] = number_format($data['amount'], 2);
        $data['donor_title'] = $this->orderItem->order->billing_title;
        $data['donor_first_name'] = $this->orderItem->order->billing_first_name;
        $data['donor_last_name'] = $this->orderItem->order->billing_last_name;
        $data['donor_email'] = $this->orderItem->order->billingemail;
        $data['tribute_type'] = $this->tributeType->label;
        $data['product_name'] = $this->orderItem->variant->product->name;
        $data['notification_type'] = $this->notify;
        $data['recipient_name'] = $this->notify_name;
        $data['recipient_email'] = $this->notify_email;
        $data['recipient_mailing_address'] = (! empty($this->notify_address) || ! empty($this->notify_city) || ! empty($this->notify_state) || ! empty($this->notify_zip) || ! empty($this->notify_country)) ? $this->notify_address . ', ' . $this->notify_city . ', ' . $this->notify_state . ', ' . $this->notify_zip . ', ' . $this->notify_country : '';
        $data['message'] = $this->message;

        foreach ($this->orderItem->fields as $field) {
            $data['custom_field_' . str_pad($field->sequence, 2, '0', STR_PAD_LEFT)] = $field->value;
        }

        foreach ($this->orderItem->variant->product->categories as $index => $category) {
            $data['product_category_' . str_pad($index + 1, 2, '0', STR_PAD_LEFT)] = $category->name;
        }

        // return the data for merge tags, combined with the globally available merge tags (shop_url, etc)
        return array_merge($data, global_merge_tags());
    }

    /**
     * Transforms this model for use with label exports.
     *
     * @return array
     */
    public function toMergeTagsForLabel()
    {
        return [
            'address_formatted' => str_replace(chr(10), '<br>', address_format($this->notify_address, null, $this->notify_city, $this->notify_state, $this->notify_zip, $this->notify_country)),
            'name_formatted' => $this->notify_name,
            'address' => $this->notify_address,
            'address2' => null,
            'city' => $this->notify_city,
            'state' => $this->notify_state,
            'zip' => $this->notify_zip,
            'country' => $this->notify_country,
        ];
    }

    /**
     * Builds and returns the unique HTML body for this model to be EMAILED.
     *
     * @return string
     */
    public function getEmailBody()
    {
        // return html template populated with this models's data
        return string_substituteFromArray($this->tributeType->email_template, $this->toMergeTagArray());
    }

    /**
     * Builds and returns the unique HTML body for this model to be MAILED.
     *
     * @return string
     */
    public function getLetterBody()
    {
        // return html template populated with this models's data
        return string_substituteFromArray($this->tributeType->letter_template, $this->toMergeTagArray());
    }

    /**
     * Uses PDF library to produce a PDF object that can then be saved, streamed, downloaded, etc.
     *
     * @return \Ds\Common\Pdf
     */
    public function toPDF()
    {
        return app('pdf')
            ->loadHtml($this->getLetterBody())
            ->setFilename($this->filename)
            ->setProtected(true);
    }

    /**
     * Email notifies the owner of the tax receipt.
     *
     * @return self
     */
    public function doNotification()
    {
        // if notify by LETTER
        if ($this->notify == 'letter') {
            // update notified_at column
            $this->notified_at = now();
            $this->save();

        // if notify by email
        } elseif ($this->notify == 'email' && $this->tributeType->email_template) {
            // bail if email is not valid
            if (trim($this->notify_email) === '' && Swift_Validate::email($this->notify_email)) {
                throw new MessageException('Failed to send notification for this tribute (' . $this->id . '). "' . $this->notify_email . '" is not a valid email.');
            }

            $params = $this->toMergeTagArray();

            // email body & subject
            $body = $this->getEmailBody();
            $subject = string_substituteFromArray($this->tributeType->email_subject, $params);

            // prep message params
            $message = (new Swift_Message)
                ->setFrom(sys_get('email_from_address'), sys_get('email_from_name'))
                ->addTo($this->notify_email, $this->notify_name)
                ->setSubject($subject)
                ->setBody($body, 'text/html');

            $emailService = (new EmailService);

            // cc
            $cc = string_substituteFromArray($this->tributeType->email_cc, $params);
            $cc_emails = $emailService->parseEmailList($cc);
            foreach ($cc_emails as $email) {
                $message->addCc($email['email'], $email['name']);
            }

            // bcc
            $bcc = string_substituteFromArray($this->tributeType->email_bcc, $params);
            $bcc_emails = $emailService->parseEmailList($bcc);
            foreach ($bcc_emails as $email) {
                $message->addBcc($email['email'], $email['name']);
            }

            // attach letter
            if ($this->tributeType->letter_template) {
                $message->attach($this->toPDF()->toSwiftAttachment());
            }

            // now actually send the message
            send_using_swiftmailer($message);

            // update notified_at column
            $this->notified_at = now();
            $this->save();
        }

        // end
        return $this;
    }

    /**
     * Create tributes from an order.
     *
     * @return void
     */
    public static function createFromOrder(Order $order)
    {
        // loop over the order items
        foreach ($order->items as $item) {
            // if the item is a tribute
            if ($item->is_tribute) {
                // make sure we don't create a tribute if one was already created
                if (self::where('order_item_id', $item->id)->count() > 0) {
                    continue;
                }

                $tribute = new self;
                $tribute->order_item_id = $item->id;
                $tribute->tribute_type_id = $item->tribute_type_id;
                $tribute->name = $item->tribute_name;
                $tribute->amount = ($item->recurring_amount > 0) ? $item->recurring_amount * $item->qty : $item->price * $item->qty;

                if (in_array($item->tribute_notify, ['email', 'letter'])) {
                    $tribute->notify = $item->tribute_notify;
                    $tribute->message = $item->tribute_message;
                    $tribute->notify_name = $item->tribute_notify_name;

                    // either a specific date or NOW
                    if ($item->tribute_notify_at) {
                        $tribute->notify_at = $item->tribute_notify_at;
                    } else {
                        $tribute->notify_at = now();
                    }

                    if ($tribute->notify == 'email') {
                        $tribute->notify_email = $item->tribute_notify_email;
                    } elseif ($tribute->notify == 'letter') {
                        $tribute->notify_address = $item->tribute_notify_address;
                        $tribute->notify_city = $item->tribute_notify_city;
                        $tribute->notify_state = $item->tribute_notify_state;
                        $tribute->notify_zip = $item->tribute_notify_zip;
                        $tribute->notify_country = $item->tribute_notify_country;
                    }
                }
                // email notification
                $tribute->save();

                // if this is an email, send it now
                if ($tribute->notify == 'email') {
                    $tribute->doNotification();
                }
            }
        }
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid(): Drop
    {
        return Drop::factory($this, 'Tribute');
    }
}
