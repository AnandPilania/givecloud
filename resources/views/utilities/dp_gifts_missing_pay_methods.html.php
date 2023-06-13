
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            DP Gifts Missing Payment Methods <span class="label label-danger">GC support only</span>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead>
        <tr>
            <th>GIFT_ID</th>
            <th>FIRST_NAME</th>
            <th>LAST_NAME</th>
            <th>AMOUNT</th>
            <th>VAULT_ID</th>
            <th>DPPAYMENTMETHODID</th>
            <th>CUSTOMERVAULTID</th>
            <th>RECORD_TYPE</th>
            <th>CREATED_BY</th>
            <th>GIFT_NARRATIVE</th>
            <th>View Contribution (New tab)</th>
        </tr>
                </thead>
                <tbody>
        <?php foreach($gifts as $gift): ?>
            <tr>
                <td><?= e($gift->GIFT_ID) ?></td>
                <td><?= e($gift->FIRST_NAME) ?></td>
                <td><?= e($gift->LAST_NAME) ?></td>
                <td><?= e($gift->AMOUNT) ?></td>
                <td><?= e($gift->VAULT_ID) ?></td>
                <td><?= e($gift->DPPAYMENTMETHODID) ?></td>
                <td><?= e($gift->CUSTOMERVAULTID) ?></td>
                <td><?= e($gift->RECORD_TYPE) ?></td>
                <td><?= e($gift->CREATED_BY) ?></td>
                <td><?= e($gift->GIFT_NARRATIVE) ?></td>
                <td>
                    <?php if($gift->CART_ID): ?>
                        <a href="<?= e(route('backend.orders.edit', $gift->CART_ID)) ?>" target="_blank">View</a>
                    <?php else: ?>
                        <i>Not found</i>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
