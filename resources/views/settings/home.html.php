
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Settings &amp; Admin
        </h1>
    </div>
</div>

<style>
    .app-grid .app { position:relative; text-align:center; display:inline-block; width:118px; margin:4px 4px 12px 4px; vertical-align: top }
    .app-grid .app a:hover { text-decoration:none; }
    .app-grid .app .app-title { font-size:16px; display:block;}
    .app-badge { border-radius:16px; font-size:14px; position:absolute; top:-4px; left:65px; padding:3px 7px; display:inline-block; padding:7px; background-color:#666; color:#fff; }
    .app-badge-danger { background-color:#d9534f; }

    .flex-masonry { column-count:2; column-gap:25px; }
    .flex-box { break-inside: avoid; }
</style>

<!--<div class="alert alert-info">
    <i class="pull-left fa fa-thumbs-up fa-4x"></i> <strong>Welcome to the NEW Settings Panel!</strong><br/>
    We're slowly moving all administrative functions and settings into our new, more intuitive settings panel. This will centralize all admin functions in one place and give us space to provide on-screen help.<br><br>Miss the old settings panel?  <strong><a href="/jpanel/settings">Find it here.</a></strong>
</div>-->

<div class="flex-masonry">

    <?php if (user()->can(['admin.general', 'admin.advanced', 'user'])): ?>
        <div class="flex-box">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-gear fa-fw"></i> General
                </div>
                <div class="panel-body">
                    <div class="app-grid">

                        <?php if (user()->can('admin.general')): ?>
                        <div class="app">
                            <a href="/jpanel/settings/general">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-building-o fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Organization</span>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if (user()->can('admin.billing')): ?>
                        <div class="app">
                            <a href="/jpanel/settings/billing">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-money fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Billing</span>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if (user()->can('user')): ?>
                            <div class="app">
                                <a href="/jpanel/users">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-lock fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Users</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (user()->can('admin.advanced')): ?>
                        <div class="app">
                            <a href="/jpanel/settings">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-cogs fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Advanced</span>
                            </a>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (user()->can(['admin.accounts', 'membership'])): ?>
        <div class="flex-box">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-users fa-fw"></i> Supporters
                </div>
                <div class="panel-body">
                    <div class="app-grid">

                        <?php if (user()->can('admin.accounts')): ?>
                            <div class="app">
                                <a href="<?= e(route('backend.settings.supporters')) ?>">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-user fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Supporters</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (feature('membership') && user()->can('membership')): ?>
                            <div class="app">
                                <a href="/jpanel/memberships">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-users fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title"><?= e(sys_get('syn_groups')) ?></span>
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (user()->can(['admin.website','posttype','alias'])): ?>
        <div class="flex-box">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-globe fa-fw"></i> Website
                </div>
                <div class="panel-body">
                    <div class="app-grid">

                        <?php if (user()->can('admin.website')): ?>
                            <div class="app">
                                <a href="/jpanel/settings/website">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-globe fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Website</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (user()->can('posttype')): ?>
                            <div class="app">
                                <a href="<?= e(route('backend.feeds.index')) ?>">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-rss fa-stack-1x fa-inverse"></i>
                                </span>
                                    <span class="app-title">Feeds &amp; Blogs</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (user()->can('alias')): ?>
                            <div class="app">
                                <a href="/jpanel/aliases">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-exchange fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Redirects</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (user()->can('admin.security')): ?>
                        <div class="app">
                            <a href="/jpanel/settings/security">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-shield fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Security</span>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (user()->can(['admin.payments','shipping','taxreceipt.edit','tributetype.edit','email','sponsorship.edit','membership'])): ?>
        <div class="flex-box">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-shopping-cart fa-fw"></i> Sell &amp; Fundraise
                </div>
                <div class="panel-body">
                    <div class="app-grid">

                        <?php if (user()->can('admin.payments')): ?>
                        <div class="app">
                            <a href="/jpanel/settings/payments">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-credit-card fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Payments</span>
                            </a>
                        </div>
                        <div class="app">
                            <a href="/jpanel/settings/payment">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-credit-card fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Payment Gateways</span>
                            </a>
                        </div>
                        <div class="app">
                            <a href="/jpanel/settings/dcc">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-leaf fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Donor Covers Costs</span>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if (feature('shipping') && user()->can('shipping')): ?>
                        <div class="app">
                            <a href="/jpanel/settings/shipping">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-truck fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Shipping</span>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if(feature('tax_receipt') && user()->can('taxreceipt.edit')): ?>
                            <div class="app">
                                <a href="/jpanel/settings/tax_receipts">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-university fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Tax Receipts</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if(user()->can('giftaid.edit')): ?>
                            <div class="app">
                                <a href="/jpanel/settings/gift_aid">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-university fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Gift Aid (UK)</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="app">
                            <a href="/jpanel/settings/pos">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-calculator fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">POS</span>
                            </a>
                        </div>

                        <?php if(user()->can('tributetype.edit')): ?>
                            <div class="app">
                                <a href="/jpanel/tribute_types">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-gift fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Tributes</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (user()->can('email')): ?>
                        <div class="app">
                            <a href="/jpanel/settings/email">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-envelope fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Email</span>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if (feature('sponsorship') && user()->can('sponsorship.edit')): ?>
                            <div class="app">
                                <a href="/jpanel/settings/sponsorship">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-child fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Sponsorship</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (feature('fundraising_pages') && user()->can('fundraisingpages.edit')): ?>
                            <div class="app">
                                <a href="/jpanel/settings/fundraising-pages">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-users fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Fundraising Pages</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if(user()->can('promocode') && feature('promos')): ?>
                            <div class="app">
                                <a href="/jpanel/promotions">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-dollar fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Promo Codes</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if(user()->can('tax') && feature('taxes')): ?>
                            <div class="app">
                                <a href="/jpanel/taxes">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-bank fa-stack-1x fa-inverse"></i>
                                    </span>
                                    <span class="app-title">Sales Tax</span>
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (user()->can(['hooks.edit', 'admin.dpo', 'admin.paypal', 'admin.gocardless'])): ?>
        <div class="flex-box">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-cloud fa-fw"></i> Integrations
                </div>
                <div class="panel-body">
                    <div class="app-grid">

                        <div class="app">
                            <a href="/jpanel/settings/integrations">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-stack-1x fa-plus fa-inverse"></i>
                                </span>
                                <span class="app-title">All Integrations</span>
                            </a>
                        </div>



                        <?php foreach ($integrations as $integration): ?>
                            <div class="app">
                                <a href="<?= e($integration->config_url) ?>">
                                    <span class="app-icon fa-stack fa-lg fa-2x">
                                        <i class="fa fa-square fa-stack-2x"></i>
                                        <i class="fa fa-stack-1x fa-cloud fa-inverse"></i>
                                    </span>
                                    <span class="app-title"><?= e($integration->name) ?></span>
                                </a>
                            </div>
                        <?php endforeach ?>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (is_super_user()): ?>
        <div class="flex-box">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <i class="fa fa-life-ring fa-fw"></i> Support Only
                </div>
                <div class="panel-body">
                    <div class="app-grid">

                        <div class="app">
                            <a href="/jpanel/import">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-upload fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Import</span>
                            </a>
                        </div>

                        <div class="app">
                            <a href="<?= e(route('backend.import_sponsee_photos.index')) ?>">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-child fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Sponsee Photo Importer</span>
                            </a>
                        </div>

                        <div class="app">
                            <a href="<?= e(route('backend.utilities.media_force_download.index')) ?>">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-photo fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Media Force Download</span>
                            </a>
                        </div>

                        <div class="app">
                            <a href="<?= e(route('backend.reports.transient_logs.index')) ?>">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-file-code-o fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Transient Logs</span>
                            </a>
                        </div>

                        <div class="app">
                            <a href="/jpanel/utilities">
                                <span class="app-icon fa-stack fa-lg fa-2x">
                                    <i class="fa fa-square fa-stack-2x"></i>
                                    <i class="fa fa-puzzle-piece fa-stack-1x fa-inverse"></i>
                                </span>
                                <span class="app-title">Utilities</span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
