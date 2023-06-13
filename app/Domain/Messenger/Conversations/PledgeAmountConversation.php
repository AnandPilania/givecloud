<?php

namespace Ds\Domain\Messenger\Conversations;

use Ds\Common\DataAccess;
use Ds\Domain\Messenger\Conversation;
use Ds\Models\Pledge;
use Ds\Models\PledgeCampaign;
use Throwable;

class PledgeAmountConversation extends Conversation
{
    /**
     * Start the conversation
     */
    public function handle()
    {
        $this->parameters['amount'] = numeral($this->parameters['amount'])->toFloat();

        $this->requireVerifiedAccount();
        $this->createPledge();
    }

    /**
     * Create a pledge.
     */
    public function createPledge()
    {
        $account = $this->getAccount();

        try {
            $pledgeCampaign = PledgeCampaign::findOrFail($this->getConversation()->metadata['pledge_campaign']);
        } catch (Throwable $e) {
            return $this->say('Oh no! There was a problem recording your pledge.');
        }

        $pledge = new Pledge;
        $pledge->account_id = $account->id;
        $pledge->pledge_campaign_id = $pledgeCampaign->id;
        $pledge->currency_code = sys_get('dpo_currency');
        $pledge->total_amount = (float) $this->parameters['amount'];
        $pledge->save();

        $account->notify('customer_pledge_received', $pledge->getMergeTags());

        $this->say(sprintf(
            "Thanks, %s 🙂. We've recorded your pledge for %s.",
            $account->first_name,
            money($pledge->total_amount, $pledge->currency_code)
        ));
    }

    /**
     * Get the configuration for the conversation.
     *
     * @return array
     */
    public static function configuration(): array
    {
        return [
            'label' => 'Pledge with an amount',
            'example' => 'PLEDGE {amount}',
            'parameters' => [
                'amount' => static::AMOUNT_PARAMETER_REGEX,
            ],
            'settings' => DataAccess::collection([
                [
                    'type' => 'header',
                    'content' => 'Options',
                ], [
                    'type' => 'pledge_campaign',
                    'name' => 'pledge_campaign',
                    'label' => 'Pledge Campaign',
                    'hint' => '<i class="fa fa-question-circle"></i> All pledges received will be associated with this campaign.',
                ],
            ]),
        ];
    }
}
