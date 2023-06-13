

<!-- /.row -->
<div class="row">

    <div class="col-lg-12 col-md-12 top-gutter">

            <div class="row wow flipInX wowHide">
                <div class="col-lg-12">
                    <h1 class="page-header">Dashboard</h1>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">

                    <div data-mh="dash-top-row">

                        <div class="row">
                            <div class="col-lg-3 col-md-6 wow flipInX wowHide">
                                <div class="panel panel-stat">
                                    <div class="panel-body">
                                        <div class="flex justify-between">
                                           <div class="huge"><?= e(numeralFormat($orders_count, '0[.]0A')) ?></div>
                                           <div class="huge"><i class="fa fa-shopping-cart"></i></div>
                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                        Incomplete Contributions
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 wow flipInX wowHide" data-wow-delay="0.2s">

                                <div class="panel panel-stat">
                                    <div class="panel-body">
                                        <div class="flex justify-between">
                                            <div class="huge"><?= e(currency()->symbol) ?><?= e(numeralFormat($revenue_this_month, '0[.]0A')) ?></div>
                                            <div class="huge"><i class="fa fa-money"></i></div>
                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                        Revenue in <strong><?= e(toLocalFormat('now', 'F')) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 wow flipInX wowHide" data-wow-delay="0.4s">
                                <div class="panel panel-stat">
                                    <div class="panel-body">
                                        <div class="flex justify-between">
                                            <div class="huge"><?= e(numeralFormat($storage_space, '0[.]0b')) ?></div>
                                            <div class="huge"><i class="fa fa-cloud-upload"></i></div>
                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                        Cloud Storage Used
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 wow flipInX wowHide" data-wow-delay="0.6s">
                                <div class="panel panel-stat">
                                    <div class="panel-body">
                                        <div class="flex justify-between">
                                            <div class="huge"><?= e(numeralFormat($user_accounts, '0[.]0A')) ?></div>
                                            <div class="huge"><i class="fa fa-group"></i></div>
                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                        User Accounts
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- /.row -->
                        <div class="row">

                            <style>
                                .engagement-placeholder { background:#fff url('/jpanel/assets/images/engagement.jpg') no-repeat center center; }
                                .best-sellers-placeholder { background:#fff url('/jpanel/assets/images/best-sellers.jpg') no-repeat center center; }
                                .account-growth-placeholder { background:#fff url('/jpanel/assets/images/account-growth.jpg') no-repeat center center; }
                                .geo-placeholder { background:#fff url('/jpanel/assets/images/geo.jpg') no-repeat center center; }
                                .sales-placeholder { background:#fff url('/jpanel/assets/images/sales.jpg') no-repeat center center; }
                            </style>

                            <div class="col-xs-12 wow fadeInUp wowHide" data-wow-delay="0.8s">
                                <div class="panel panel-basic">
                                    <div class="panel-heading">
                                        <i class="fa fa-money fa-fw"></i> Revenue <small>60 Days</small>
                                    </div>
                                    <!-- /.panel-heading -->
                                    <?php if ($revenue_chart !== false): ?>
                                        <div class="panel-body" style="height:250px;">
                                            <div id="30day-sales-chart" style="height:230px;"></div>
                                            <script type="application/json" id="30day-sales-chart-data" data-currency-symbol="<?= e(currency()->symbol) ?>"><?= dangerouslyUseHTML(json_encode($revenue_chart)) ?></script>
                                        </div>
                                    <?php else: ?>
                                        <div class="panel-body sales-placeholder" style="height:350px;"></div>
                                    <?php endif; ?>
                                    <!-- /.panel-body -->
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="row">

                <div class="col-md-4 wow fadeInUp wowHide" data-wow-delay="1s">
                    <div class="panel panel-basic">
                        <div class="panel-heading">
                            <i class="fa fa-shopping-cart fa-fw"></i> Today's Engagement
                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu slidedown">
                                    <li><a href="<?= e(route('backend.orders.abandoned_carts')) ?>" >View Abandoned Carts Report</a></li>
                                </ul>
                            </div>
                        </div>
                        <!-- /.panel-heading -->
                        <?php if ($engagement_chart_data !== false): ?>
                            <div class="panel-body" style="height:240px;">
                                <div id="engagement-chart" style="height:220px;"></div>
                                <script type="application/json" id="engagement-chart-data"><?= dangerouslyUseHTML(json_encode($engagement_chart_data)) ?></script>
                            </div>
                        <?php else: ?>
                            <div class="panel-body engagement-placeholder" style="height:240px;"></div>
                        <?php endif; ?>
                        <!-- /.panel-body -->
                    </div>
                </div>

                <div class="col-md-4 wow fadeInUp wowHide" data-wow-delay="1.2s">
                    <div class="panel panel-basic">
                        <div class="panel-heading">
                            <i class="fa fa-tag fa-fw"></i> Best Sellers <small>12 months</small>
                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu slidedown">
                                    <li><a href="/jpanel/reports/products" >View Contributions by Product</a></li>
                                </ul>
                            </div>
                        </div>
                        <!-- /.panel-heading -->
                        <?php if ($best_seller_chart_data !== false): ?>
                            <div class="panel-body" style="height:240px;">
                                <div id="best-sellers-chart" style="height:220px;"></div>
                                <script type="application/json" id="best-sellers-chart-data"><?= dangerouslyUseHTML(json_encode($best_seller_chart_data)) ?></script>
                            </div>
                        <?php else: ?>
                            <div class="panel-body best-sellers-placeholder" style="height:240px;"></div>
                        <?php endif; ?>
                        <!-- /.panel-body -->
                    </div>
                </div>

                <div class="col-md-4 wow fadeInUp wowHide" data-wow-delay="1.4s">
                    <div class="panel panel-basic">
                        <div class="panel-heading">
                            <i class="fa fa-users fa-fw"></i> User Account Growth <small>30 Days</small>
                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu slidedown">
                                    <li><a href="<?= e(route('backend.member.index')) ?>">View Supporters</a></li>
                                </ul>
                            </div>
                        </div>
                        <!-- /.panel-heading -->
                        <?php if ($account_growth_chart_data_30day !== false): ?>
                            <div class="panel-body" style="height:240px;">
                                <div id="account-growth-chart" style="height:220px;"></div>
                                <script type="application/json" id="account-growth-chart-data"><?= dangerouslyUseHTML(json_encode($account_growth_chart_data_30day)) ?></script>
                            </div>
                        <?php else: ?>
                            <div class="panel-body account-growth-placeholder" style="height:240px;"></div>
                        <?php endif; ?>
                        <!-- /.panel-body -->
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="col-xs-12 wow fadeInUp wowHide">
                    <div class="panel panel-basic">
                        <div class="panel-heading">
                            <i class="fa fa-globe fa-fw"></i> Contributions by Geography <small>Last 12 Months</small>
                        </div>
                        <!-- /.panel-heading -->
                        <?php if ($geo_chart_data !== false): ?>
                            <div class="panel-body" style="height:350px;">
                                <div class="geo-chart" id="geo-chart" data-geo-options="<?= e(json_encode($geo_chart_opts)) ?>" data-geo-data="<?= e(json_encode($geo_chart_data)) ?>"></div>
                            </div>
                        <?php else: ?>
                            <div class="panel-body geo-placeholder" style="height:350px;"></div>
                        <?php endif; ?>
                        <!-- /.panel-body -->
                    </div>
                </div>
            </div>

    </div>
</div>
