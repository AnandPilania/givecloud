<?php

namespace Ds\Domain\Sponsorship\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Member;
use Ds\Models\OrderItem;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sponsor extends Model implements Liquidable
{
    use HasFactory;
    use Permissions;
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use Userstamps;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'started_at',
        'ended_at',
        'last_payment_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_payment_amt' => 'float',
        'lifetime_amt' => 'float',
    ];

    /**
     * Relationship: Member
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Relationship: Sponsorship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sponsorship()
    {
        return $this->belongsTo(Sponsorship::class)->withTrashed();
    }

    /**
     * Relationship: Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Relationship: EndedBy User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function endedBy()
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    /**
     * Relationship: RecurringPaymentProfile
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function recurringPaymentProfile()
    {
        return $this->hasOne('Ds\Models\RecurringPaymentProfile', 'productorderitem_id', 'order_item_id');
    }

    /**
     * Scope: Sponsors that are active (start/end dates)
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeActive($query)
    {
        return $query->where(DB::raw('DATE(started_at)'), '<=', now())
            ->where(function ($query) {
                return $query->whereNull('ended_at')
                    ->orWhere(DB::raw('DATE(ended_at)'), '>=', now());
            });
    }

    /**
     * Find the recurring payment profile.
     *
     * ????  HOW DO WE SET THIS UP AS A RELATIONSHIP ????
     * ????  HOW DO WE SET THIS UP AS A RELATIONSHIP ????
     * ????  HOW DO WE SET THIS UP AS A RELATIONSHIP ????
     */
    public function getRecurringPaymentProfile()
    {
        static $profile;

        if (isset($profile[$this->id])) {
            return $profile[$this->id];
        }

        $profile[$this->id] = RecurringPaymentProfile::where('member_id', $this->member_id)
            ->where('sponsorship_id', $this->sponsorship_id)
            ->where('productorderitem_id', $this->order_item_id)
            ->first();

        return $profile[$this->id];
    }

    /**
     * Attribute mask: is_ended
     *
     * @return bool
     */
    public function getIsEndedAttribute()
    {
        return $this->ended_at && $this->ended_at->isPast();
    }

    /**
     * Attribute mask: can_restore_sponsorship
     *
     * If a sponsor has ended their sponsorship, we want to limit the
     * ability for them to restore their sponsorship if there is a
     * recurring payment profile linked to it.
     *
     * @return bool
     */
    public function getCanRestoreSponsorshipAttribute()
    {
        return ($this->getRecurringPaymentProfile() && $this->getRecurringPaymentProfile()->status == RecurringPaymentProfileStatus::CANCELLED) ? false : true;
    }

    /**
     * Send a notification to the sponsor
     * (start, stop, anniversary, b-day)
     *
     * @param \Ds\Models\Email|string $email
     * @return bool
     */
    public function notify($email, $params = [])
    {
        if (empty($this->member)) {
            return false;
        }

        $orderParams = [];

        if ($order = data_get($this, 'orderItem.order')) {
            $orderParams = $order->notifyParams();
        }

        $params = array_merge($orderParams, $params, [
            'sponsorship_start_date' => toLocalFormat($this->started_at, 'F d, Y'),
            'sponsorship_end_date' => ($this->ended_at) ? toLocalFormat($this->ended_at, 'F d, Y') : null,
            'sponsorship_end_reason' => $this->ended_reason,
            'sponsorship_source' => $this->source,
            'sponsorship_first_name' => $this->sponsorship->first_name,
            'sponsorship_last_name' => $this->sponsorship->last_name,
            'sponsorship_bio' => $this->sponsorship->biography,
            'sponsorship_birth_date' => $this->sponsorship->birth_date,
            'sponsorship_age' => $this->sponsorship->age,
            'sponsorship_reference' => $this->sponsorship->reference_number,
            'sponsorship_image_raw' => media_thumbnail($this->sponsorship, '300x'),
            'sponsorship_image' => '<img src="' . media_thumbnail($this->sponsorship, '300x') . '" style="width:300px; height:auto;" />',
            'sponsorship_image_circle' => '<img src="' . media_thumbnail($this->sponsorship, '300x') . '" style="width:300px; height:auto; border-radius:150px;" />',
            'sponsorship_recurring_description' => optional($this->orderItem)->payment_string,
        ]);

        return $this->member->notify($email, $params);
    }

    /**
     * ############################################
     * ###### USED DURING INITIAL MIGRATION #######
     * ############################################
     * Populates the sponsors table based on purchases.
     * !! WILL NOT CREATE DUPLICATES - safe to run twice !!
     */
    public static function createFromOrders()
    {
        $order_item_sponsors = OrderItem::whereNotNull('sponsorship_id')
            ->join('productorder', 'productorder.id', '=', 'productorderitem.productorderid')
            ->whereNotNull('productorder.confirmationdatetime')
            ->with('order.member')
            ->select('productorderitem.*')
            ->get();

        foreach ($order_item_sponsors as $order_item) {
            // if there is no member, create one
            if (! $order_item->order->member) {
                // find a matching member
                $member = Member::where('email', $order_item->order->billingemail)
                    ->whereRaw('lcase(member.first_name) = ?', [strtolower(trim($order_item->order->billing_first_name))])
                    ->whereRaw('lcase(member.last_name) = ?', [strtolower(trim($order_item->order->billing_last_name))])
                    ->first();

                // if no member found, create one
                if (! $member) {
                    $member = new Member;
                    $member->first_name = $order_item->order->billing_first_name;
                    $member->last_name = $order_item->order->billing_last_name;
                    $member->email = $order_item->order->billingemail;
                    $member->ship_first_name = $order_item->order->shipping_first_name;
                    $member->ship_last_name = $order_item->order->shipping_last_name;
                    $member->ship_email = $order_item->order->shipemail;
                    $member->ship_address_01 = $order_item->order->shipaddress1;
                    $member->ship_address_02 = $order_item->order->shipaddress2;
                    $member->ship_city = $order_item->order->shipcity;
                    $member->ship_state = $order_item->order->shipstate;
                    $member->ship_zip = $order_item->order->shipzip;
                    $member->ship_country = $order_item->order->shipcountry;
                    $member->ship_phone = $order_item->order->shipphone;
                    $member->bill_first_name = $order_item->order->billing_first_name;
                    $member->bill_last_name = $order_item->order->billing_last_name;
                    $member->bill_email = $order_item->order->billingemail;
                    $member->bill_address_01 = $order_item->order->billingaddress1;
                    $member->bill_address_02 = $order_item->order->billingaddress2;
                    $member->bill_city = $order_item->order->billingcity;
                    $member->bill_state = $order_item->order->billingstate;
                    $member->bill_zip = $order_item->order->billingzip;
                    $member->bill_country = $order_item->order->billingcountry;
                    $member->bill_phone = $order_item->order->billingphone;
                    $member->is_active = 1;
                    $member->donor_id = $order_item->order->alt_contact_id;
                    $member->force_password_reset = 1;
                    $member->created_at = now();
                    $member->updated_at = now();
                    $member->sign_up_method = 'sponsorship-migration';
                    $member->save();
                }

                // if still no member was created - just move on to next record
                if (! $member) {
                    continue;
                }

                // update order
                $order_item->order->member_id = $member->id;
                $order_item->order->save();

            // otherwise, use the member that already exists
            } else {
                $member = $order_item->order->member;
            }

            // create sponsor
            $order_item->createSponsor();
        }
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Sponsorship');
    }
}
