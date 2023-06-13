<?php

namespace Ds\Eloquent;

/** @phan-file-suppress PhanUndeclaredMethod */
trait ChangeTracking
{
    /**
     * Append any changes made to the changes column.
     */
    public function trackChanges()
    {
        $changed = $this->buildChangedArray();

        if (count($changed) > 0) {
            $changes = (array) $this->getAttribute('changes');

            $changes[] = (object) [
                'changed_at' => toUtcFormat('now', 'datetime'),
                'changed_by' => user('id'),
                'changes_log' => $changed,
            ];

            $this->setAttribute('changes', $changes);
        }
    }

    /**
     * Attribute Mask: changes_formatted (formatted array of all changes)
     *
     * @return array
     */
    public function getChangesFormattedAttribute()
    {
        $changes = (array) $this->getAttribute('changes');

        if (count($changes) === 0) {
            return [];
        }

        $formatted = [];

        foreach (array_reverse($changes) as $change) {
            $user = \Ds\Models\User::find($change['changed_by']);
            $date = toLocalFormat($change['changed_at'], 'M j, Y');

            $output = "Changes authorized by {$user->full_name} on $date:";

            foreach ($change['changes_log'] as $modification) {
                $output .= "\n  - {$modification['label']} changed from '{$modification['from']}' to '{$modification['to']}'.";
            }

            $formatted[] = $output;
        }

        return $formatted;
    }

    /**
     * Build a array of tracked changes.
     *
     * @return array
     */
    protected function buildChangedArray()
    {
        if (isset($this->tracked) === false) {
            return [];
        }

        $changes = [];

        foreach ($this->tracked as $attribute => $label) {
            if (isset($this->original[$attribute]) === false) {
                continue;
            }

            $change = [
                'column' => $attribute,
                'label' => $label,
                'from' => $this->getRawOriginal($attribute),
                'to' => $this->getAttribute($attribute),
            ];

            if (is_bool($change['to']) && $change['to'] === (bool) $change['from']) {
                continue;
            }

            if (is_int($change['to']) && $change['to'] === (int) $change['from']) {
                continue;
            }

            if (is_float($change['to']) && $change['to'] === (float) $change['from']) {
                continue;
            }

            if (is_string($change['to']) && $change['to'] === (string) $change['from']) {
                continue;
            }

            if (is_instanceof($change['to'], 'DateTimeInterface')) {
                $change['to'] = toUtcFormat($change['to'], 'Y-m-d');
                $change['from'] = toUtcFormat($change['from'], 'Y-m-d');

                if ($change['from'] === $change['to']) {
                    continue;
                }
            }

            $changes[] = $change;
        }

        return $changes;
    }
}
