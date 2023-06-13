<?php

namespace Ds\Repositories;

use Ds\Models\Node;
use Ds\Models\ProductCategory;

class MenuRepository
{
    public static function categoryToNode($categories = null, $parent_id = 0, $start_sequence = 0)
    {
        // if categories don't exist, start at the bottom categories
        if (! isset($categories)) {
            return self::categoryToNode(ProductCategory::topLevel()->get(), 0, (int) Node::where('parentid', 0)->max('sequence'));
        }

        // loop over each category
        foreach ($categories as $category) {
            // node
            $node = new Node;
            $node->parentid = $parent_id;
            $node->sequence = $start_sequence++;
            $node->title = $category->name;
            $node->type = 'category';
            $node->isactive = true;
            $node->ishidden = false;
            $node->category_id = $category->id;
            $node->save();

            // if there are sub categories
            if (count($category->childCategories) > 0) {
                // loop recursively
                self::categoryToNode($category->childCategories, $node->id);
            }
        }
    }
}
