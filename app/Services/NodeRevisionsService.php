<?php

namespace Ds\Services;

use DomainException;
use Ds\Enums\NodeType;
use Ds\Models\Metadata;
use Ds\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NodeRevisionsService
{
    /** @var string[] */
    protected $revisionable = [
        NodeType::ADVANCED,
        NodeType::HTML,
        NodeType::LIQUID,
    ];

    /** @var string[] */
    protected $attributes = [
        'title',
        'pagetitle',
        'body',
        'metadescription',
        'metakeywords',
    ];

    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    public function supportsRevisions(): bool
    {
        return in_array($this->node->type, $this->revisionable, true);
    }

    protected function requireSupportForRevisions(): void
    {
        if (! $this->supportsRevisions()) {
            throw new DomainException("'{$this->node->type}' nodes aren't revisable.");
        }
    }

    protected function getRevisableAttributes(): Collection
    {
        return collect($this->attributes)->sort()->values();
    }

    protected function getRevisableMetadata(): Collection
    {
        $templateMetadata = app('theme')->getTemplateMetadata('page');

        return collect($templateMetadata[$this->node->template_suffix]->schema ?? null)
            ->pluck('settings')
            ->flatten()
            ->filter(fn ($setting) => ($setting->revisable ?? false))
            ->pluck('name')
            ->unique()
            ->sort()
            ->values();
    }

    /** @return mixed */
    protected function getNodeAttribute(string $key)
    {
        return $this->node->getAttributes()[$key] ?? null;
    }

    public function requestHasChangesForRevisableContent(?Request $request = null): bool
    {
        $this->requireSupportForRevisions();

        $request ??= request();

        foreach ($this->getRevisableAttributes() as $key) {
            $value = (string) $request->input($key);
            $originalValue = (string) $this->getNodeAttribute($key);

            if ($value !== $originalValue) {
                return true;
            }
        }

        // strip the cast types from the metadata key names
        $inputMetadata = collect($request->input('metadata'))
            ->mapWithKeys(fn ($value, $key) => [Str::after($key, ':') => $value]);

        foreach ($this->getRevisableMetadata() as $key) {
            $value = (string) ($inputMetadata[$key] ?? '');
            $originalValue = (string) ($this->node->getMetadata($key)->value ?? '');

            if ($value !== $originalValue) {
                return true;
            }
        }

        return false;
    }

    public function createRevision(bool $autosave = false): void
    {
        $this->requireSupportForRevisions();

        if ($autosave) {
            $this->cleanupAutosave();
        }

        $revision = new Node;
        $revision->type = NodeType::REVISION;
        $revision->parentid = $this->node->getKey();
        $revision->hide_menu_link_when_logged_out = 0;
        $revision->autosave = $autosave;

        $this->storeAttributesForRevision($revision);
        $this->storeMetadataForRevision($revision);

        $this->cleanupRevisions();
    }

    protected function storeAttributesForRevision(Node $revision): void
    {
        foreach ($this->getRevisableAttributes() as $key) {
            $revision->setAttribute($key, $this->getNodeAttribute($key));
        }

        $revision->save();
    }

    protected function storeMetadataForRevision(Node $revision): void
    {
        foreach ($this->getRevisableMetadata() as $key) {
            $metadata = $this->node->getMetadata($key);

            if (! $metadata) {
                continue;
            }

            $revisionMetadata = new Metadata(Arr::except(
                $metadata->getAttributes(),
                [$metadata->getKeyName()],
            ));

            $revisionMetadata->metadatable_id = $revision->getKey();
            $revisionMetadata->save();
        }
    }

    protected function cleanupAutosave(): void
    {
        $this->node->autosaveRevision()->delete();
    }

    public function cleanupRevisions(): void
    {
        $this->requireSupportForRevisions();

        while (1) {
            // delete old revisions in chunks of 100
            $revisionIds = $this->node->revisions()
                ->withoutAutosave()
                ->orderByDesc('created_at')
                ->offset(sys_get('node_revisions_to_keep'))
                ->limit(100)
                ->pluck('id');

            if ($revisionIds->isEmpty()) {
                break;
            }

            DB::table('node')
                ->whereIn('id', $revisionIds)
                ->delete();

            DB::table('metadata')
                ->where('metadatable_type', $this->node->getMorphClass())
                ->whereIn('metadatable_id', $revisionIds)
                ->delete();
        }
    }

    public function useRevision(int $revisionId): void
    {
        $this->requireSupportForRevisions();

        $revision = $this->node->revisions()->findOrFail($revisionId);

        foreach ($this->getRevisableAttributes() as $key) {
            $this->node->setAttribute($key, $revision->getAttribute($key));
        }

        $metadata = [];

        foreach ($revision->metadataRelation as $revisionMetadata) {
            $metadata["{$revisionMetadata->type}:{$revisionMetadata->key}"] = $revisionMetadata->value;
        }

        $this->node->metadata = $metadata;
    }
}
