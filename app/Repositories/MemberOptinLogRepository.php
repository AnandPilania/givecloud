<?php

namespace Ds\Repositories;

use Ds\Models\MemberOptinLog;

class MemberOptinLogRepository
{
    /** @var \Ds\Models\MemberOptinLog */
    protected $model;

    public function __construct(MemberOptinLog $model)
    {
        $this->model = $model;
    }

    public function getLastLogFromMember(int $memberId): ?MemberOptinLog
    {
        $memberForeignKey = $this->model->member()->getForeignKeyName();

        return $this->model->newQuery()
            ->where($memberForeignKey, $memberId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }
}
