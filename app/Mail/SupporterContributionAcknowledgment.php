<?php

namespace Ds\Mail;

use Ds\Enums\ProductType;
use Ds\Http\Resources\DonationForms\DonationFormResource;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupporterContributionAcknowledgment extends Mailable
{
    use Queueable;
    use SerializesModels;

    /** @var \Ds\Models\Order */
    public $order;

    /** @var \object */
    public $donationForm;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $item = $this->order->items->first(function (OrderItem $item) {
            return $item->variant->product->type === ProductType::DONATION_FORM;
        });

        $this->donationForm = DonationFormResource::make($item->variant->product)->toObject();

        $amount = money($this->order->balance_amt, $this->order->currency)->format('$0[.]00');

        $this->view('mailables.supporter-contribution-acknowledgment')
            ->from(sys_get('email_from_address'), sys_get('email_from_name', sys_get('clientShortName')))
            ->subject(__('frontend/mailables.subject', ['amount' => $amount]));

        if (sys_get('email_replyto_address')) {
            $this->replyTo(sys_get('email_replyto_address'));
        }

        if (sys_get('email_sender_required')) {
            $this->withSwiftMessage(fn ($message) => $message->setSender('notifications@givecloud.co'));
        }

        return $this->with([
            'amount' => $amount,
            'brandingLogo' => $this->getBrandingLogo(),
            'thankYouMessage' => $this->getThankYouMessage(),
            'org' => [
                'name' => sys_get('clientName'),
                'url' => secure_site_url(),
                'phone' => sys_get('org_support_number'),
                'email' => sys_get('org_support_email'),
            ],
        ]);
    }

    public function getBrandingLogo(): ?string
    {
        $logo = $this->order->recurring_items
            ? $this->donationForm->branding_monthly_logo
            : $this->donationForm->branding_logo;

        return $logo->thumb ?? sys_get('default_logo');
    }

    public function getThankYouMessage(): ?string
    {
        return $this->order->recurring_items && $this->donationForm->thank_you_email_monthly_message
            ? $this->donationForm->thank_you_email_monthly_message
            : $this->donationForm->thank_you_email_message;
    }
}
