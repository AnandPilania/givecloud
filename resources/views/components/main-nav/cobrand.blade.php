<?php if(site()->partner->in_app_logo): ?>
    <span class="mx-2">+</span> <img class="h-7 w-auto" src="<?= e(site()->partner->in_app_logo) ?>">
<?php else: ?>
    <span class="mx-2 text-base whitespace-nowrap"> + <?= e(site()->partner->in_app_brand) ?></span>
<?php endif; ?>
