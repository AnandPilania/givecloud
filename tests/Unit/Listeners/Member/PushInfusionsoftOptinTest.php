<?php

namespace Tests\Unit\Listeners\Member;

use Ds\Common\Infusionsoft\Api;
use Ds\Events\MemberOptinChanged;
use Ds\Exceptions\Handler;
use Ds\Listeners\Member\PushInfusionsoftOptin;
use Ds\Models\Member;
use Ds\Models\MemberOptinLog;
use Ds\Services\InfusionsoftService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PushInfusionsoftOptinTest extends TestCase
{
    public function testEventListenerIsListeningOnMemberOptinChanged(): void
    {
        Event::fake();

        Event::assertListening(MemberOptinChanged::class, PushInfusionsoftOptin::class);
    }

    public function testJobAddsInfusionSoftTagWhenOptingin(): void
    {
        $infusionsoftOptinTag = 1234;
        sys_set('infusionsoft_optin_tag', $infusionsoftOptinTag);
        $member = Member::factory()->create([
            'infusionsoft_contact_id' => 56789,
        ]);

        MemberOptinLog::factory()->for($member)->optin()->create();
        MemberOptinLog::factory()->for($member)->optout()->create();
        MemberOptinLog::factory()->for($member)->optin()->create();

        /** @var \PHPUnit\Framework\MockObject\MockObject */
        $infusionsoftServiceMock = $this->createMock(InfusionsoftService::class);
        $infusionsoftServiceMock
            ->expects($this->once())
            ->method('addUniqueContactTags')
            ->with($member->infusionsoft_contact_id, [$infusionsoftOptinTag])
            ->willReturn(true);

        $this->app->make(PushInfusionsoftOptin::class, ['infusionsoftService' => $infusionsoftServiceMock])
            ->handle((new MemberOptinChanged($member)));
    }

    public function testJobRemovesInfusionSoftTagWhenOptingout(): void
    {
        $infusionsoftOptinTag = 1234;
        sys_set('infusionsoft_optin_tag', $infusionsoftOptinTag);
        $member = Member::factory()->create([
            'infusionsoft_contact_id' => 56789,
        ]);

        MemberOptinLog::factory()->for($member)->optin()->create();
        MemberOptinLog::factory()->for($member)->optout()->create();

        /** @var \PHPUnit\Framework\MockObject\MockObject */
        $infusionsoftApiMock = $this->createMock(Api::class);
        $infusionsoftApiMock
            ->expects($this->once())
            ->method('removeTags')
            ->with($member->infusionsoft_contact_id, [$infusionsoftOptinTag])
            ->willReturn(true);
        $infusionsoftService = $this->app->make(InfusionsoftService::class, ['api' => $infusionsoftApiMock]);

        $this->app->make(PushInfusionsoftOptin::class, ['infusionsoftService' => $infusionsoftService])
            ->handle((new MemberOptinChanged($member)));
    }

    public function testJobRemovesInfusionSoftTagWhenApiThrowsModelNotFoundException(): void
    {
        $infusionsoftOptinTag = 1234;
        sys_set('infusionsoft_optin_tag', $infusionsoftOptinTag);
        $member = Member::factory()->create([
            'infusionsoft_contact_id' => 56789,
        ]);
        MemberOptinLog::factory()->for($member)->optout()->create();

        /** @var \PHPUnit\Framework\MockObject\MockObject */
        $infusionsoftApiMock = $this->createMock(Api::class);
        $infusionsoftApiMock
            ->expects($this->once())
            ->method('removeTags')
            ->with($member->infusionsoft_contact_id, [$infusionsoftOptinTag])
            ->willThrowException(new ModelNotFoundException());
        $infusionsoftService = $this->app->make(InfusionsoftService::class, ['api' => $infusionsoftApiMock]);

        $this->expectExceptionReported();

        $this->app->make(PushInfusionsoftOptin::class, ['infusionsoftService' => $infusionsoftService])
            ->handle((new MemberOptinChanged($member)));
    }

    public function testJobDoesNotQueueWhithoutOptinLog(): void
    {
        $member = Member::factory()->create();

        /** @var \PHPUnit\Framework\MockObject\MockObject */
        $infusionsoftServiceMock = $this->createMock(InfusionsoftService::class);
        $infusionsoftServiceMock
            ->expects($this->never())
            ->method('addUniqueContactTags');
        $infusionsoftServiceMock
            ->expects($this->never())
            ->method('getClient');

        $this->expectExceptionReported();

        $this->app->make(PushInfusionsoftOptin::class, ['infusionsoftService' => $infusionsoftServiceMock])
            ->shouldQueue((new MemberOptinChanged($member)));
    }

    private function expectExceptionReported(): void
    {
        // Mock ExceptionhHandler used for reporting
        $exceptionHandlerMock = $this->createMock(Handler::class, ['report']);
        $exceptionHandlerMock
            ->expects($this->once())
            ->method('report');

        $this->app->instance(ExceptionHandler::class, $exceptionHandlerMock);
    }
}
