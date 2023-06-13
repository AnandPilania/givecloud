<?php

namespace Ds\Models;

use Ds\Eloquent\SoftDeleteBooleans;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Services\EmailService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Swift_Message;
use Throwable;

class Email extends Model
{
    use HasFactory;
    use SoftDeleteBooleans;
    use Userstamps;

    /**
     * The name of the "deleted at" column.
     *
     * @var string
     */
    // const DELETED_AT = 'is_deleted';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'active_start_date',
        'active_end_date',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'is_protected' => 'boolean',
        'disables_generic' => 'boolean',
    ];

    /**
     * A list of system email codes that have attachments.
     * Primarily for on-screen help text.
     *
     * @var array
     */
    protected $have_attachments = [
        'customer_tax_receipt',
    ];

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'emailable')->withTrashed();
    }

    public function variants(): MorphToMany
    {
        return $this->morphedByMany(Variant::class, 'emailable')->withTrashed();
    }

    public function memberships(): MorphToMany
    {
        return $this->morphedByMany(Membership::class, 'emailable')->withTrashed();
    }

    /**
     * Attribute mask: has_attachments
     *
     * @return bool
     */
    public function getHasAttachmentsAttribute()
    {
        return in_array($this->type, $this->have_attachments);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->isDeleted()
            || $this->isNoLongerActive()
            || $this->isNotYetActive()
            || $this->isOffline();
    }

    public function isNoLongerActive(): bool
    {
        return ! empty($this->active_end_date)
            && toLocal($this->active_end_date)->lt(fromLocal('today'));
    }

    public function isNotYetActive(): bool
    {
        return ! empty($this->active_start_date)
            && toLocal($this->active_start_date)->gt(fromLocal('today'));
    }

    public function isOffline(): bool
    {
        return $this->is_active === false;
    }

    public function isDeleted(): bool
    {
        return $this->is_deleted === true;
    }

    /**
     * Scope: Active emails by name
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $type
     */
    public static function scopeActiveType($query, $type)
    {
        return $query->where('is_active', 1)
            ->where('type', $type)
            ->get();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
    }

    /**
     * Custom email notifications.
     */
    public static function customNotifications()
    {
        return self::where('is_protected', 0)
            ->get();
    }

    /**
     * System email notifications.
     *
     * @return self
     */
    public static function systemNotifications()
    {
        // base query
        $email_query = self::where('is_protected', 1)
            ->orderBy('category', 'asc');

        // ====================================
        // auto scope based on features
        // ====================================
        if (! feature('tax_receipt') || sys_get('tax_receipt_pdfs') == 0) {
            $email_query->where('type', '!=', 'customer_tax_receipt');
        }

        if (sys_get('rpp_donorperfect') == 1) {
            $email_query->whereNotIn('type', ['merchant_recurring_payment_processing_summary', 'customer_recurring_payment_success', 'customer_recurring_payment_failure']);
        }

        if (! feature('accounts')) {
            $email_query->whereNotIn('type', ['member_welcome', 'member_profile_update', 'member_password_reset']);
        }

        if (! feature('edownloads')) {
            $email_query->whereNotIn('type', ['customer_downloads']);
        }

        if (! feature('sponsorship')) {
            $email_query->whereNotIn('type', ['sponsorship_started']);
            $email_query->whereNotIn('type', ['sponsorship_ended']);
        }

        return $email_query->get();
    }

    /**
     * Send an email template.
     *
     * @param array $to
     * @param array $merge
     * @return bool
     */
    public function send($to, $merge)
    {
        // bail if not active or deleted
        if (! $this->is_active || $this->is_deleted) {
            return false;
        }

        // bail if start date is in the future
        if ($this->active_start_date && $this->active_start_date->isFuture()) {
            return false;
        }

        // bail if end date is in the past
        if ($this->active_end_date && $this->active_end_date->isPast()) {
            return false;
        }

        // add all the global merge codes in
        $merge = array_merge($merge, global_merge_tags());

        // merge codes
        $body = string_substituteFromArray($this->body_template, $merge);
        $subject = string_substituteFromArray($this->subject, $merge);

        // prep message params
        $message = (new Swift_Message)
            ->setFrom(sys_get('email_from_address'), sys_get('email_from_name'))
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        $emailService = (new EmailService);

        $to = string_substituteFromArray($to, $merge);
        $to_emails = $emailService->parseEmailList($to);
        foreach ($to_emails as $email) {
            $message->addTo($email['email'], $email['name']);
        }

        // cc
        $cc = string_substituteFromArray($this->cc, $merge);
        $cc_emails = $emailService->parseEmailList($cc);
        foreach ($cc_emails as $email) {
            $message->addCc($email['email'], $email['name']);
        }

        // bcc
        $bcc = string_substituteFromArray($this->bcc, $merge);
        $bcc_emails = $emailService->parseEmailList($bcc);
        foreach ($bcc_emails as $email) {
            $message->addBcc($email['email'], $email['name']);
        }

        // now actually send the message
        try {
            $send_status = send_using_swiftmailer($message);
        } catch (Throwable $e) {
            $send_status = false;
            notifyException($e);
        }

        return ($send_status) ? true : false;
    }
}
