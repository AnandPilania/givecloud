<?php

namespace Ds\Http\Controllers;

use Ds\Enums\Supporters\SupporterVerifiedStatus;
use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Illuminate\Http\RedirectResponse;

class MemberVerifiedStatusController extends Controller
{
    public function store(Member $member): RedirectResponse
    {
        $member->verified_status = SupporterVerifiedStatus::VERIFIED;

        if ($member->save()) {
            $this->flash->success('Supporter was verified successfully');
        } else {
            $this->flash->error('An error occured. Please try again.');
        }

        // Send notifications
        $member->fundraisingPages()->pending()->each(function (FundraisingPage $fundraisingPage) {
            $fundraisingPage->activate();
        });

        return redirect()->back();
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->verified_status = SupporterVerifiedStatus::DENIED;

        if ($member->save()) {
            $this->flash->success('Supporter was denied.');
        } else {
            $this->flash->error('An error occured. Please try again.');
        }

        return redirect()->back();
    }
}
