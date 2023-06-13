<?php

namespace Ds\Models;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxReceiptTemplate extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
        'parent_id' => 'integer',
        'latest_revision_id' => 'integer',
    ];

    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(TaxReceiptTemplate::class, 'parent_id');
    }

    public function latestRevision(): BelongsTo
    {
        return $this->belongsTo(TaxReceiptTemplate::class, 'latest_revision_id');
    }

    /**
     * Create a revision of the template.
     *
     * @return void
     */
    public function createRevision()
    {
        if ($this->template_type === 'revision') {
            throw new MessageException('Unable create revision based on another revision');
        }

        $revision = $this->replicate([
            'is_default',
            'latest_revision_id',
        ]);

        $revision->template_type = 'revision';
        $revision->parent_id = $this->id;
        $revision->save();

        $this->latest_revision_id = $revision->id;
        $this->save();

        $this->load('latestRevision');
    }
}
