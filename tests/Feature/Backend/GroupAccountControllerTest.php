<?php

namespace Tests\Feature\Backend;

use Ds\Models\GroupAccount;
use Tests\TestCase;

class GroupAccountControllerTest extends TestCase
{
    public function testDestroySuccess(): void
    {
        // url to redirect back to.
        $backUrl = route('backend.member.index');

        $group = GroupAccount::factory()->create();

        $this->actingAsUser();
        $this->get($backUrl);

        $response = $this->post(
            route('backend.group_account.destroy'),
            ['group_account_id' => $group->getKey()]
        );

        $response->assertRedirect($backUrl);
        $response->assertSessionMissing('_flashMessages.error');
    }

    public function testDestroyFailsWhenGroupAccountIdNotFound(): void
    {
        // url to redirect back to.
        $backUrl = route('backend.member.index');

        $this->actingAsUser();
        $this->get($backUrl);

        $response = $this->post(route('backend.group_account.destroy'), ['group_account_id' => 0]);

        $response->assertRedirect($backUrl);
        $response->assertSessionHas('_flashMessages.error', 'Oops! The group #0 does not exist.');
    }
}
