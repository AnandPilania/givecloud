<?php

function membership_get($membership_id)
{
    $qMembership = db_query(sprintf(
        "SELECT membership.*,
                CONCAT(updated_user.firstname,' ',updated_user.lastname) updated_by_name,
                CONCAT(created_user.firstname,' ',created_user.lastname) created_by_name
            FROM membership
            LEFT JOIN `user` updated_user ON updated_user.id = membership.updated_by
            LEFT JOIN `user` created_user ON created_user.id = membership.created_by
            WHERE membership.id = %d",
        db_real_escape_string($membership_id)
    ));

    if (! ($qMembership !== false && db_num_rows($qMembership) > 0)) {
        return false;
    }

    return db_fetch_object($qMembership);
}
