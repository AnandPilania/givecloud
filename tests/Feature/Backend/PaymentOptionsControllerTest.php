<?php

namespace Tests\Feature\Backend;

use Ds\Models\User;
use Tests\TestCase;

class PaymentOptionsControllerTest extends TestCase
{
    public function testHandlePaymentOptionDeleteWhenUnknownId(): void
    {
        // Allow Member to edit payment options
        sys_set('feature_sponsorship', true);

        $this->actingAsUser(User::factory()->create([
            'permissions_json' => ['paymentoption.edit'],
        ]));

        $response = $this->post(route('backend.sponsorship.payment_options.destroy'), ['id' => 0]);
        $response->assertRedirect(route('backend.sponsorship.payment_options.index'));

        $this->followRedirects($response)
            ->assertSee('Cannot find Payment Option #0 for deletion.');
    }
}
