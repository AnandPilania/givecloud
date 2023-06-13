<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Messenger\Jobs\ResumeConversation;
use Ds\Domain\Messenger\Models\ResumableConversation;
use Ds\Models\Member as Account;
use Throwable;

class SMSController extends Controller
{
    /**
     * @param string $hashid
     * @return string
     */
    public function handlePayload($hashid)
    {
        try {
            $resumableConversation = ResumableConversation::query()
                ->where('expires', '>', now())
                ->hashid($hashid)
                ->firstOrFail();
        } catch (Throwable $e) {
            return $this->renderTemplate('~sms/404');
        }

        $account = Account::query()
            ->billPhoneE164($resumableConversation->sender)
            ->where('sms_verified', true)
            ->first();

        if ($account) {
            member_login_with_id($account->id, true);

            if ($account->defaultPaymentMethod) {
                return $this->showThankYou($resumableConversation);
            }

            return $this->showPaymentMethods($resumableConversation);
        }

        return $this->showSignup($resumableConversation);
    }

    /**
     * @param \Ds\Domain\Messenger\Models\ResumableConversation $resumableConversation
     * @return string
     */
    private function showSignup(ResumableConversation $resumableConversation)
    {
        pageSetup(__('frontend/theme.sms.signup.signup'));

        $template = sys_get('messenger_use_minimal_templates') ? 'minimal' : null;

        return $this->renderTemplate("~sms/signup.$template", [
            'context' => "sms:{$resumableConversation->hashid}",
            'verify_sms' => encrypt($resumableConversation->sender),
            'recaptcha_token' => app('recaptcha')->generateVerificationToken(),
        ]);
    }

    /**
     * @param \Ds\Domain\Messenger\Models\ResumableConversation $resumableConversation
     * @return string
     */
    private function showPaymentMethods(ResumableConversation $resumableConversation)
    {
        pageSetup(__('frontend/theme.sms.payment_methods.payment_methods'));

        $template = sys_get('messenger_use_minimal_templates') ? 'minimal' : null;

        return $this->renderTemplate("~sms/payment-methods.$template", [
            'context' => "sms:{$resumableConversation->hashid}",
        ]);
    }

    /**
     * @param \Ds\Domain\Messenger\Models\ResumableConversation $resumableConversation
     * @return string
     */
    private function showThankYou(ResumableConversation $resumableConversation)
    {
        pageSetup(__('frontend/theme.sms.thank_you.thank_you'));

        dispatch(new ResumeConversation($resumableConversation));

        return $this->renderTemplate('~sms/thank-you', [
            'context' => "sms:{$resumableConversation->hashid}",
        ]);
    }
}
