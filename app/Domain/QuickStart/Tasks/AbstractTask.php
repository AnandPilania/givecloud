<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\Concerns\IsSkippable;
use Ds\Domain\QuickStart\QuickStartService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class AbstractTask implements Arrayable, Jsonable, JsonSerializable
{
    abstract public function title(): string;

    abstract public function description(): string;

    abstract public function action(): string;

    abstract public function actionText(): string;

    abstract public function knowledgeBase(): string;

    abstract public function isCompleted(): bool;

    public static function initialize(): AbstractTask
    {
        return app(static::class);
    }

    public function isActive(): bool
    {
        if (! method_exists($this, 'dependsOn')) {
            return true;
        }

        // If one of our parent task is inactive, we are also inactive by design.
        foreach ($this->dependsOn() as $class) {
            if (! app($class)->isActive()) {
                return false;
            }
        }

        return true;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->slug(),
            'title' => $this->title(),
            'description' => $this->description(),
            'action' => $this->action(),
            'actionText' => $this->actionText(),
            'knowledgeBase' => $this->knowledgeBase(),
            'isActive' => $this->isActive(),
            'isCompleted' => $this->isCompleted(),
            'isSkippable' => in_array(IsSkippable::class, class_uses_recursive($this), true),
            'isSkipped' => method_exists($this, 'isSkipped') ? $this->isSkipped() : false,
            'isDependant' => method_exists($this, 'dependsOn'),
            'dependantOf' => method_exists($this, 'dependsOn') ? $this->dependsOn() : [],
            'potentialResolutionPaths' => $this->potentialResolutionPaths(),
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR | $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function potentialResolutionPaths(): array
    {
        return [];
    }

    public function slug(): string
    {
        return Str::snake(class_basename($this));
    }

    public function update(): void
    {
        app(QuickStartService::class)->updateTaskStatus($this);
    }
}
