
<form class="form-horizontal" action="/jpanel/settings/website/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Website

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
            </div>
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <div class="panel panel-default">
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-globe"></i> General Settings</div>
                    <div class="panel-sub-desc">

                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Default Page Title</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="defaultPageTitle" value="<?= e(sys_get('defaultPageTitle')) ?>" placeholder="My Organization">
                            <small class="text-muted">The page title that displays after every page name in the browser.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Website Domain</label>
                        <div class="col-md-8">
                            <div class="form-control-static"><a href="https://<?= e($site->subdomain) ?>" target="_blank">https://<?= e($site->subdomain) ?> <i class="fa fa-external-link"></i></a></div>
                            <small class="text-muted">This URL to your site will always work.</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-pencil"></i> Custom Domain</div>
                    <div class="panel-sub-desc bottom-gutter">
                        You can override your default domain with a custom domain name of your choice.

                        <br><br>
                        <span class="text-info">
                            <i class="fa fa-exclamation-circle fa-fw"></i> <strong>Hint:</strong> For each custom domain, be sure you've updated your DNS records to point to our servers.

                            <br><br>
                            <strong>Recommended:</strong><br>
                            <i class="fa fa-arrow-right fa-fw"></i> CNAME records point to <code><?= e(sys_get('ds_account_name')) ?>.givecloud.co</code>
                            <br><br>
                            <strong>Alternative:</strong><br>
                            <i class="fa fa-arrow-right fa-fw"></i> A records point to <code>104.196.66.237</code>
                        </span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Custom Domains</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control selectize-tags" name="_site_domains" value="<?= e($site->custom_domains->implode(', ')) ?>" placeholder="example.org, www.example.org, donate.example.org">
                            <small class="text-muted">A full list of all domains that are pointing to your site.<br>For example: example.org, www.example.org<br>DO NOT include the http:// prefix. We manage that for you :)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Primary Domain</label>
                        <div class="col-md-8">
                            <select name="clientDomain" class="form-control">
                                <option><?= e($site->subdomain) ?></option>
                                <?php foreach ($site->domains->where('status', 'VERIFIED')->pluck('name') as $domain): ?>
                                    <option <?= e(volt_selected($domain, sys_get('clientDomain'))); ?>><?= e($domain) ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="text-muted">This is the primary domain all traffic will be redirected to, regardless of what domain they visit. It's also the URL that will be used in all your links.</small>

                            <br><br>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="custom_domain_migration_mode" value="1" <?= e((sys_get('custom_domain_migration_mode') == 1) ? 'checked' : '') ?>> Do not force all traffic to this domain.
                                </label>
                            </div>

                            <small class="text-muted">If you recently updated your DNS records, this feature allows any domain (including your default domain) to resolve your site while your DNS records are propogating.</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-lock"></i> Lock Site</div>
                    <div class="panel-sub-desc">
                        Lock your site by providing a password so the public can't view your site.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Password</label>
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="site_password" value="<?= e(sys_get('site_password')) ?>" placeholder="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Lock Message</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="site_password_message" value="<?= e(sys_get('site_password_message')) ?>" placeholder="Site Locked">
                            <small class="text-muted">The message that displays on the lock screen.<br>For example: "Website coming soon!"</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-search"></i> Search Engines</div>
                    <div class="panel-sub-desc">
                        Allow search engines to index your site.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-6 col-md-offset-4">

                    <div class="radio">
                        <label><input name="web_allow_indexing" type="radio" value="1" <?= e((sys_get('web_allow_indexing') == 1) ? 'checked' : '') ?> > <i class="fa fa-check fa-fw"></i> Allow search engines <small>(recommended)</small></label><br>
                        <small class="text-muted">Search engines will be allowed to index all pages of your site.</small>
                    </div>

                    <br>
                    <div class="radio">
                        <label><input name="web_allow_indexing" type="radio" value="0" <?= e((sys_get('web_allow_indexing') == 0) ? 'checked' : '') ?> > <i class="fa fa-times fa-fw"></i> Block search engines</label><br>
                        <small class="text-muted">Search engines will be instructed NOT to index your site. This means that your site will not be displayed in any search engine results.</small>
                    </div>

                </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-line-chart"></i> Google Analytics Tracking</div>
                    <div class="panel-sub-desc">
                        Notify Google of page hits and purchase/donation conversions using ecommerce tracking. Provide your website property ID in order to activate this feature.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Property ID</label>
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="webStatsPropertyId" value="<?= e(sys_get('webStatsPropertyId')) ?>" placeholder="UA-XXXXX-Y">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


</div></div>

</form>
