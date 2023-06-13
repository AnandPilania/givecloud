
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Super-User Utilities</h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<style>
.caption-fixed-height { height:170px; max-height: 150px; overflow: hidden; }
</style>

<div class="row">

    <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
            <div class="caption">
                <div class="caption-fixed-height">
                    <h3>Gifts Missing Payment Methods</h3>
                    <p>This tool will list all gifts (and correlating contribution numbers) that are missing payment methods in DonorPerfect.</p>
                </div>
                <p><a href="/jpanel/utilities/dp_gifts_missing_pay_methods" class="btn btn-primary" role="button">Run</a></p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
            <div class="caption">
                <div class="caption-fixed-height">
                    <h3>Sync Contributions</h3>
                    <p>Loops over all contributions that are not sync'd with DonorPerfect and tries to sync them. A log will be produced to review.</p>
                </div>
                <p><a href="/jpanel/utilities/sync_unsynced_orders" onclick="return confirm('Are you sure you want to run this function?');" class="btn btn-primary" role="button">Run</a></p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
            <div class="caption">
                <div class="caption-fixed-height">
                    <h3>Sync Transactions <span class="badge"><?= e(\Ds\Models\Transaction::unsynced()->count()) ?></span></h3>
                    <p>Loops over all transactions that haven't been synced with DP and sync's each one. Triggers a job that runs in the background so the browser doesn't timeout. <?= e(config('mail.support.address')) ?> will receive an email when complete.</p>
                </div>
                <p><a href="/jpanel/utilities/sync_unsynced_txns" onclick="return confirm('Are you sure you want to run this function?');" class="btn btn-primary" role="button">Run</a></p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
            <div class="caption">
                <div class="caption-fixed-height">
                    <h3>Importable Pledges</h3>
                    <p>Loops over all members with DP ID's, finds any pledges in DP, and outputs the pledges into a file that is emailed to <?= e(config('mail.support.address')) ?>. This is used as a primer to the RPP import.</p>
                </div>
                <p><a href="/jpanel/utilities/all_pledges" onclick="return confirm('Are you sure you want to run this function?');" class="btn btn-primary" role="button">All Pledges</a></p>
                <p><a href="/jpanel/utilities/importable_pledges" onclick="return confirm('Are you sure you want to run this function?');" class="btn btn-primary" role="button">Only Pledges Where Accounts are Linked</a></p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
            <div class="caption">
                <div class="caption-fixed-height">
                    <h3>Issue Tax Receipts</h3>
                    <p>Find missing tax receipts for a given year and a given product and issue them.</p>
                </div>

                <form action="/jpanel/utilities/show_unreceipted" method="get">
                    <div class="input-group">
                        <select class="form-control" name="year">
                            <option>2016</option>
                            <option>2017</option>
                            <option>2018</option>
                            <option>2019</option>
                            <option>2020</option>
                            <option>2021</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-primary">Find</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
            <div class="caption">
                <div class="caption-fixed-height">
                    <h3>Refunded Contributions &amp; DP</h3>
                    <p>This script collects all refunded contributions in Givecloud and analyzes every gift associated with those contributions in DP. If the Gift in DP is not also refunded or adjusted, it will be listed in this report.</p>
                </div>
                <p><a href="/jpanel/utilities/orders_without_adjustments" onclick="return confirm('Are you sure you want to run this function?');" class="btn btn-primary" role="button">Download List</a></p>
            </div>
        </div>
    </div>

</div>
