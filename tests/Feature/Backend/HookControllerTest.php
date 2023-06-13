<?php

namespace Tests\Feature\Backend;

use Ds\Models\Hook;
use Ds\Models\HookEvent;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * @group backend
 * @group hooks
 */
class HookControllerTest extends TestCase
{
    public function testIndexSuccess(): void
    {
        $hooks = Hook::factory(3)->create();

        $this->actingAsUser($this->createUserWithPermissions('hooks.index'));
        $response = $this->get(route('backend.settings.hooks.index'));

        $response->assertOk();
        $hooks->each(function ($hook) use ($response) {
            $response->assertSeeText($hook->payload_url);
        });
    }

    public function testCreateSuccess(): void
    {
        $this->actingAsUser($this->createUserWithPermissions('hooks.create'));
        $this->get(route('backend.settings.hooks.create'))->assertOk();
    }

    public function testEditSuccess(): void
    {
        $hook = Hook::factory()->create();

        $this->actingAsUser($this->createUserWithPermissions('hooks.edit'));
        $response = $this->get(route('backend.settings.hooks.edit', $hook));

        $response->assertOk();
        $response->assertSeeText($hook->payload_url);
    }

    public function testEditNotFound(): void
    {
        $this->actingAsUser($this->createUserWithPermissions('hooks.edit'));
        $this->get(route('backend.settings.hooks.edit', 0))->assertNotFound();
    }

    /**
     * @dataProvider storeSuccessData
     */
    public function testStoreSuccess(array $newHookOverrides): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection */
        $hookEvents = HookEvent::factory(3)->make();
        $eventsToLink = $hookEvents->random(2)->map->name->toArray();

        $newHook = Hook::factory()->inactive()->make($newHookOverrides);
        $newHookData = [
            'payload_url' => $newHook->payload_url,
            'content_type' => $newHook->content_type,
        ];
        if ($newHook->secret) {
            $newHookData['secret'] = $newHook->secret;
        }

        $this->actingAsUser($this->createUserWithPermissions('hooks.edit'));
        $response = $this->postJson(
            route('backend.settings.hooks.store'),
            $newHookData + ['events' => $eventsToLink]
        );

        $response->assertOk();
        $jsonResponse = $response->json();
        $this->assertTrue($jsonResponse['success']);
        $this->assertIsInt($jsonResponse['hook_id']);

        $storedHook = Hook::where($newHookData)->firstOrFail();
        $this->assertNotEmpty($storedHook->secret);
        $this->assertSame($storedHook->getKey(), $jsonResponse['hook_id']);
        $this->assertSame($storedHook->events->map->name->toArray(), $eventsToLink);
    }

    public function storeSuccessData(): array
    {
        return [
            [[]],
            [['secret' => null]],
        ];
    }

    /**
     * @dataProvider storeValidationInvalidData
     */
    public function testStoreValidationFails(array $invalidData, array $expectedErrors): void
    {
        $this->actingAsUser($this->createUserWithPermissions('hooks.edit'))
            ->postJson(route('backend.settings.hooks.store'), $invalidData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors($expectedErrors);
    }

    public function storeValidationInvalidData(): array
    {
        return [
            [
                $this->makeValidHookData(['content_type' => 'unsupported/type']),
                ['content_type' => 'The selected content type is invalid.'],
            ],
            [
                $this->makeValidHookData(['events' => [1000000]]),
                ['events.0' => 'The selected events.0 is invalid.'],
            ],
            [
                $this->makeValidHookData(['payload_url' => 'not an url']),
                ['payload_url' => 'The payload url format is invalid.'],
            ],
            [
                ['content_type' => Arr::random(Hook::CONTENT_TYPES)],
                [
                    'events' => 'The events field is required.',
                    'payload_url' => 'The payload url field is required.',
                ],
            ],
            [
                ['events' => [HookEvent::getEnabledEvents()->random()]],
                [
                    'content_type' => 'The content type field is required.',
                    'payload_url' => 'The payload url field is required.',
                ],
            ],
            [
                ['payload_url' => 'http://some-url.com'],
                [
                    'content_type' => 'The content type field is required.',
                    'events' => 'The events field is required.',
                ],
            ],
            [
                ['secret' => 'secret'],
                [
                    'content_type' => 'The content type field is required.',
                    'events' => 'The events field is required.',
                    'payload_url' => 'The payload url field is required.',
                ],
            ],
            [
                ['active' => false],
                [
                    'content_type' => 'The content type field is required.',
                    'events' => 'The events field is required.',
                    'payload_url' => 'The payload url field is required.',
                ],
            ],
            [
                ['payload_url' => 'http://some-url.com', 'active' => false],
                [
                    'content_type' => 'The content type field is required.',
                    'events' => 'The events field is required.',
                ],
            ],
            [
                ['secret' => 'secret', 'active' => false],
                [
                    'content_type' => 'The content type field is required.',
                    'events' => 'The events field is required.',
                    'payload_url' => 'The payload url field is required.',
                ],
            ],
            [
                ['insecure_ssl' => true, 'payload_url' => 'http://some-url.com'],
                [
                    'content_type' => 'The content type field is required.',
                    'events' => 'The events field is required.',
                ],
            ],
        ];
    }

    /**
     * @dataProvider updateSuccessData
     */
    public function testUpdateSuccess(array $newHookOverrides): void
    {
        [$hookOldEvent, $hookNewEvent] = HookEvent::factory(2)->make();
        $hook = Hook::factory()->create();
        $hook->events()->saveMany([$hookOldEvent]);

        $newHook = Hook::factory()->make($newHookOverrides);
        $newHookData = [
            'payload_url' => $newHook->payload_url,
            'content_type' => $newHook->content_type,
            'secret' => $newHook->secret,
        ];
        if ($newHook->active) {
            $newHookData['active'] = true;
        }

        $this->actingAsUser($this->createUserWithPermissions('hooks.edit'));
        $response = $this->putJson(
            route('backend.settings.hooks.update', $hook),
            $newHookData + ['events' => [$hookNewEvent->name]]
        );

        $response->assertOk();
        $jsonResponse = $response->json();
        $this->assertTrue($jsonResponse['success']);
        $this->assertIsInt($jsonResponse['hook_id']);

        $this->assertSame($hook->getKey(), $jsonResponse['hook_id']);
        $this->assertSame($newHook->active, $hook->refresh()->active);
        $this->assertSame($hookNewEvent->name, $hook->events->first()->name);
        $this->assertSame($newHook->insecure_ssl, $hook->insecure_ssl);
        $this->assertSame($newHook->payload_url, $hook->payload_url);
        $this->assertSame($newHook->secret, $hook->secret);
    }

    public function updateSuccessData(): array
    {
        return [
            [[]],
            [['secret' => null]],
        ];
    }

    public function testUpdateHookWithInsecureSSLSuccess(): void
    {
        $hook = Hook::factory()->create();

        $this->actingAsUser($this->createUserWithPermissions('hooks.edit'));
        $response = $this->putJson(route('backend.settings.hooks.update', $hook), ['insecure_ssl' => true]);

        $response->assertOk();
        $jsonResponse = $response->json();
        $this->assertTrue($jsonResponse['success']);
        $this->assertIsInt($jsonResponse['hook_id']);

        $this->assertSame($hook->refresh()->getKey(), $jsonResponse['hook_id']);
        $this->assertTrue($hook->insecure_ssl);
    }

    /**
     * @dataProvider updateValidationInvalidData
     */
    public function testUpdateValidationFails(array $invalidData, array $expectedErrors): void
    {
        $this->actingAsUser($this->createUserWithPermissions('hooks.edit'));

        $this->putJson(route('backend.settings.hooks.update', Hook::factory()->create()), $invalidData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors($expectedErrors);
    }

    public function updateValidationInvalidData(): array
    {
        return [
            [
                $this->makeValidHookData(['content_type' => 'unsupported/type']),
                ['content_type' => 'The selected content type is invalid.'],
            ],
            [
                $this->makeValidHookData(['events' => [100000000]]),
                ['events.0' => 'The selected events.0 is invalid.'],
            ],
            [
                $this->makeValidHookData(['payload_url' => 'not an url']),
                ['payload_url' => 'The payload url format is invalid.'],
            ],
            [
                ['events' => [HookEvent::getEnabledEvents()->random()]],
                [
                    'content_type' => 'The content type field is required when active / events / payload url / secret is present.',
                    'payload_url' => 'The payload url field is required when active / content type / events / secret is present.',
                ],
            ],
            [
                ['payload_url' => 'http://some-url.com'],
                [
                    'content_type' => 'The content type field is required when active / events / payload url / secret is present.',
                    'events' => 'The events field is required when active / content type / payload url / secret is present.',
                ],
            ],
            [
                ['secret' => 'secret'],
                [
                    'content_type' => 'The content type field is required when active / events / payload url / secret is present.',
                    'payload_url' => 'The payload url field is required when active / content type / events / secret is present.',
                    'events' => 'The events field is required when active / content type / payload url / secret is present.',
                ],
            ],
            [
                ['active' => false],
                [
                    'content_type' => 'The content type field is required when active / events / payload url / secret is present.',
                    'events' => 'The events field is required when active / content type / payload url / secret is present.',
                    'payload_url' => 'The payload url field is required when active / content type / events / secret is present.',
                ],
            ],
            [
                ['payload_url' => 'http://some-url.com', 'active' => false],
                [
                    'content_type' => 'The content type field is required when active / events / payload url / secret is present.',
                    'events' => 'The events field is required when active / content type / payload url / secret is present.',
                ],
            ],
            [
                ['secret' => 'secret', 'active' => false],
                [
                    'content_type' => 'The content type field is required when active / events / payload url / secret is present.',
                    'events' => 'The events field is required when active / content type / payload url / secret is present.',
                    'payload_url' => 'The payload url field is required when active / content type / events / secret is present.',
                ],
            ],
            [
                ['insecure_ssl' => true, 'payload_url' => 'http://some-url.com'],
                [
                    'content_type' => 'The content type field is required when active / events / payload url / secret is present.',
                    'events' => 'The events field is required when active / content type / payload url / secret is present.',
                ],
            ],
        ];
    }

    private function makeValidHookData(array $overrides): array
    {
        return array_merge([
            'active' => true,
            'content_type' => Arr::random(Hook::CONTENT_TYPES),
            'events' => [HookEvent::getEnabledEvents()->random()],
            'insecure_ssl' => false,
            'payload_url' => 'https://some-url.com',
            'secret' => 'random-secret-' . microtime(),
        ], $overrides);
    }

    public function testDestroySuccess(): void
    {
        $hook = Hook::factory()->create(['deleted_at' => null]);

        $this->actingAsUser($this->createUserWithPermissions('hooks.destroy'));
        $response = $this->deleteJson(route('backend.settings.hooks.destroy', $hook->getKey()));

        $response->assertOk();
        $this->assertTrue($response->json()['success']);
        $this->assertNotEmpty($hook->refresh()->deleted_at);
    }

    public function testDestroyNotFound(): void
    {
        $this->actingAsUser($this->createUserWithPermissions('hooks.destroy'));
        $this->deleteJson(route('backend.settings.hooks.destroy', 0))->assertNotFound();
    }
}
