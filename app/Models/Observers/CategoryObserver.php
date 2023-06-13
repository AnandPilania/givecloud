<?php

namespace Ds\Models\Observers;

use Ds\Models\Category;

class CategoryObserver
{
    /**
     * Response to the creating event.
     *
     * @param \Ds\Models\Category $model
     * @return void
     */
    public function creating(Category $model)
    {
        $model->handle = $model->createUniqueHandle();
    }

    /**
     * Response to the deleting event.
     *
     * @param \Ds\Models\Category $model
     * @return void
     */
    public function deleting(Category $model)
    {
        // Keep the Posts but unlink them from Category
        $model->posts()->detach();

        if ($model->childCategories) {
            // Cascade delete child categories as well
            // Using the the childCategories() function instead
            // will not propagate the event to cascade to sub categories
            $model->childCategories->each->delete();
        }
    }
}
