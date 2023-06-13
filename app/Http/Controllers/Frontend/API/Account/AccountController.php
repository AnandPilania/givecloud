<?php

namespace Ds\Http\Controllers\Frontend\API\Account;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Http\Controllers\Frontend\API\Controller;
use Ds\Http\Requests\Frontend\API\Account\AccountUpdateFormRequest;
use Ds\Models\Member;
use Ds\Models\Member as Account;
use Ds\Services\DonorPerfectService;
use Ds\Services\MemberService;
use Illuminate\Http\JsonResponse;
use Throwable;

class AccountController extends Controller
{
    /** @var \Ds\Services\MemberService */
    private $memberService;

    public function __construct(MemberService $memberService)
    {
        parent::__construct();

        $this->memberService = $memberService;
    }

    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccount()
    {
        return $this->success([
            'account' => Drop::factory(member(), 'Account'),
        ]);
    }

    public function updateAccount(AccountUpdateFormRequest $request, Account $account): JsonResponse
    {
        if ($request->getValidator()->fails()) {
            return $this->failure($request->getValidator()->errors()->first());
        }

        $original = $account->toObject();

        member_update($request->except('password_confirmation'));

        $member = member(null, true);

        $this->memberService
            ->setMember($member)
            ->updateOptin(
                $request->email_opt_in ?? null,
                $request->email_opt_out_reason_other ?? $request->email_opt_out_reason ?? null
            );

        cart()->populateMember($member);

        $this->updateDonorPerfect($member);

        member_notify_updated_profile($member->id, $original);

        return $this->success([
            'account' => Drop::factory($member, 'Account'),
        ]);
    }

    private function updateDonorPerfect(Member $member): void
    {
        if (! sys_get('allow_account_users_to_update_donor') || $member->donor_id < 1) {
            return;
        }

        try {
            app(DonorPerfectService::class)->updateDonorFromAccount($member);
        } catch (MessageException $e) {
            // ignore DP errors when customers update their accounts
        } catch (Throwable $e) {
            notifyException($e);
        }
    }
}
