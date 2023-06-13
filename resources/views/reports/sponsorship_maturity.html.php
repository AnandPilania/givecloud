
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead>
        <tr>
            <th width="16"></th>
            <th style="width:80px;">Ref#</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th width="140">Birth Date</th>
            <th style="text-align:center; width:60px;">Age</th>
            <th style="text-align:center; width:120px;">Sponsored</th>
        </tr>
                </thead>
                <tbody>
    <?php foreach($sponsorships as $sponsorship): ?>
        <tr>
            <td width="16"><a href="/jpanel/sponsorship/edit?i=<?= e($sponsorship->id) ?>"><i class="fa fa-search"></i></a></td>
            <td><?= e($sponsorship->reference_number) ?></td>
            <td><?= e($sponsorship->last_name) ?></td>
            <td><?= e($sponsorship->first_name) ?></td>
            <td data-order="<?= e(toLocalFormat($sponsorship->birth_date,'U')) ?>"><?= e(toLocalFormat($sponsorship->birth_date, 'date:fdate')) ?></td>
            <td style="text-align:center; width:60px;"><?= e($sponsorship->age) ?></td>
            <td style="text-align:center; width:90px;"><?= e(($sponsorship->is_sponsored == 1) ? 'Yes' : 'No') ?><?= dangerouslyUseHTML(($sponsorship->is_sponsored_auto == 0) ? ' <span style="color:#999;">(Manual)</span>' : '') ?></td>
        </tr>
    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
