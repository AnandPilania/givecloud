<?php

    $emails = [];
    while ($e = db_fetch_assoc($qList)) $emails[] = $e;

    echo '<h1>Emails w/ Names</h1>';
    echo '<p>';
    foreach ($emails as $e) echo $e['email_formatted'].', ';
    echo '</p>';

    echo '<h1>Emails only</h1>';
    echo '<p>';
    foreach ($emails as $e) echo $e['email'].', ';
    echo '</p>';
