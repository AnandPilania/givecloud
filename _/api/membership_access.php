<?php

function membership_access_get_by_parent($parent_type, $parent_id)
{
    // find membership_ids where matches this parent type/id wher
    $qAccess = db_query(sprintf(
        "SELECT membership_id
            FROM membership_access
            INNER JOIN membership ON membership.id = membership_access.membership_id AND membership.deleted_at IS NULL
            WHERE parent_type = '%s'
                AND parent_id = %d",
        db_real_escape_string($parent_type),
        db_real_escape_string($parent_id)
    ));

    if ($qAccess === false || db_num_rows($qAccess) === 0) {
        return [];
    }

    // create an array of membership_ids
    $membership_ids = [];
    while ($access = db_fetch_object($qAccess)) {
        array_push($membership_ids, $access->membership_id);
    }

    return $membership_ids;
}

function membership_access_get_by_membership($membership_id)
{
    $return_var = (object) [
        'category_ids' => [],
        'node_ids' => [],
        'product_ids' => [],
    ];

    // categories
    $qCategories = db_query(sprintf(
        "SELECT parent_id
            FROM membership_access
            WHERE parent_type = 'product_category'
                AND membership_id = %d",
        db_real_escape_string($membership_id)
    ));
    if ($qCategories !== false) {
        while ($access = db_fetch_object($qCategories)) {
            $return_var->category_ids[] = $access->parent_id;
        }
    }

    // nodes
    $qNodes = db_query(sprintf(
        "SELECT parent_id
            FROM membership_access
            WHERE parent_type = 'node'
                AND membership_id = %d",
        db_real_escape_string($membership_id)
    ));
    if ($qNodes !== false) {
        while ($access = db_fetch_object($qNodes)) {
            $return_var->node_ids[] = $access->parent_id;
        }
    }

    // products
    $qProducts = db_query(sprintf(
        "SELECT parent_id
            FROM membership_access
            WHERE parent_type = 'product'
                AND membership_id = %d",
        db_real_escape_string($membership_id)
    ));
    if ($qProducts !== false) {
        while ($access = db_fetch_object($qProducts)) {
            $return_var->product_ids[] = $access->parent_id;
        }
    }

    return $return_var;
}
