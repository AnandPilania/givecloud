
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <span class="page-header-text"><?= e($pageTitle) ?></span>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <?php if (user()->can('post.add')): ?>
                    <a href="/jpanel/feeds/posts/add?p=<?= e($feed->id) ?>" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span></a>
                <?php endif; ?>

                <?php if (user()->can('posttype.view')): ?>
                    <a href="/jpanel/feeds/edit?i=<?= e($feed->id) ?>" class="btn btn-default"><i class="fa fa-gear fa-fw"></i><span class="hidden-xs hidden-sm"> Settings</span></a>
                <?php endif; ?>
            </div>

            <?php if ($feed->sysname == 'blog'): ?>
                <div class="text-secondary">
                    <a href="<?= e($feed->absolute_url) ?>" target="_blank">
                        <?= e($feed->absolute_url) ?>
                        <i class="fa fa-external-link"></i>
                    </a>
                </div>
            <?php endif; ?>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<?php if ($feed->sysname === 'slide'): ?>

    <style>
        ul.feed_list { margin:0px; padding:0px; list-style-type:none; }
        ul.feed_list li { opacity:0.7; border:2px solid #c00; border-bottom:2px solid #600; border-right:2px solid #600; background-color:#fff; position:relative; margin:10px 0; padding:20px; list-style-type:none;  border-radius:3px; -moz-border-radius:3px; -webkit-border-radius:3px; }
        ul.feed_list li.enabled { opacity:1.0; border:1px solid #ccc; border-bottom:1px solid #999; border-right:1px solid #999; }
        ul.feed_list li:hover { position:relative; top:-4px; box-shadow:0px 4px 6px #444; -webkit-box-shadow:0px 4px 6px #444; -moz-box-shadow:0px 4px 6px #444; }
        ul.feed_list li .feed_list_li-wrap { position:relative; height:110px; }
        ul.feed_list li .feed_list_li-thumb { width:200px; height:110px; background-size:auto 110px; background-repeat:no-repeat; background-position:center center; background-color:#eee;  }
        ul.feed_list li .feed_list_li-description { position:absolute; top:0px; left:220px; font-size:10px; line-height:16px; color:#999;  }
        ul.feed_list li .feed_list_li-description a { color:#666;  }
        ul.feed_list li .feed_list_li-description a:hover { color:#333;  }
        ul.feed_list li .feed_list_li-label { color:#000; font-size:14px; line-height:18px; font-weight:bold; margin-bottom:10px; }
        ul.feed_list li .feed_list_li-label a { color:#00f; }
        ul.feed_list li .feed_list_li-label a:hover { color:#00f; }
        ul.feed_list li .feed_list_li-options { position:absolute; bottom:0px; right:0px; }
        ul.feed_list li .feed_list_li-move_anchor { cursor:move; }
        ul.feed_list li .feed_list-cover { position:absolute; top:0px; left:0px; width:100%; height:100%; background:transparent url('/jpanel/assets/images/loading.gif') no-repeat center center; }
        ul.feed_list li.sortable-placeholder { height:120px; background-color:#eee; border:1px solid #eee; }
        ul.feed_list .offline { font-size:10px; color:#fff; background-color:#c00; padding:2px 4px; text-transform: uppercase; margin:2px 0; border-radius:2px; -moz-border-radius:2px; -webkit-border-radius:2px; }
        ul.feed_list .bold { font-weight:bold; }
        ul.feed_list .red { color:#c00; }
    </style>

    <ul id="feed_list-slide" class="feed_list">
        <?php foreach ($posts as $post): ?>
            <li id="feed_list-post-<?= e($post->id) ?>" data-post_id="<?= e($post->id) ?>" <?= dangerouslyUseHTML((!$post->is_expired)?'class="enabled"':'') ?>>
                <div class="feed_list_li-wrap">
                    <div class="feed_list_li-thumb" style="background-image:url('<?= e(media_thumbnail($post->enclosure)) ?>');"></div>
                    <div class="feed_list_li-description">
                        <div class="feed_list_li-label"><a href="/jpanel/feeds/posts/edit?i=<?= e($post->id) ?>"><?= e($post->name) ?></a><?= dangerouslyUseHTML(($post->is_expired) ? ' <span class="offline">OFFLINE</span>' : '') ?></div>
                        <?php if (trim($post->url) !== ''): ?><a href="<?= e($post->url) ?>" target="_blank"><?= e($post->url) ?></a><br /><?php endif; ?>
                        <?php if ($post->postdatetime): ?><span class="<?= e((in_array('too early', $post->expired_reasons))?'bold red':'') ?>">Starts: <?= e($post->postdatetime) ?></span><br /><?php endif; ?>
                        <?php if ($post->expirydatetime): ?><span class="<?= e((in_array('too late', $post->expired_reasons))?'bold red':'') ?>">Ends: <?= e($post->expirydatetime) ?></span><br /><?php endif; ?>
                        <?php if ($post->expired_reasons && in_array('offline', $post->expired_reasons)): ?><span class="bold red">Status set to offline</span><br /><?php endif; ?>
                    </div>

                    <div class="feed_list_li-options">
                        <?php if ($post->userCan('edit')): ?>
                            <a href="/jpanel/feeds/posts/edit?i=<?= e($post->id) ?>" class="btn btn-xs btn-info"><i class="fa fa-pencil fa-fw"></i><span class="hidden-xs"> Edit</span></a>
                            <a href="javascript:void(0);" title="Click and drag..." class="feed_list_li-move_anchor btn btn-xs btn-info"><i class="fa fa-arrows fa-fw"></i><span class="hidden-xs"> Move</span></a>
                            <a href="javascript:void(0);" onclick="j.post.removeFromList(<?= e($post->id) ?>);" class="btn btn-xs btn-danger"><i class="fa fa-times fa-fw"></i><span class="hidden-xs"> Delete</span></a>
                        <?php else: ?>
                            <a href="/jpanel/feeds/posts/edit?i=<?= e($post->id) ?>" class="btn btn-xs btn-info"><i class="fa fa-search fa-fw"></i><span class="hidden-xs"> View</span></a>
                        <?php endif; ?>
                    </div>

                </div>
            </li>
        <?php endforeach; ?>
    </ul>

<?php elseif ($feed->sysname === 'snippet'): ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table id="post_list" class="table table-v2 table-striped responsive">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Shortcode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <div class="title"><a href="/jpanel/feeds/posts/edit?i=<?= e($post->id) ?>"><?= e($post->name) ?></a></div>
                                </td>
                                <td>
                                    <code class="copy-to-clipboard cursor-pointer noselect">[snippet id="<?= e($post->id) ?>"]</code> or
                                    <code class="copy-to-clipboard cursor-pointer noselect">[snippet name="<?= e($post->name) ?>"]</code>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php else: ?>

    <div class="row">
        <form class="datatable-filters">

            <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-search"></i></div>
                        <input type="text" class="form-control" name="search_term" id="search_term" value="<?= e(request('search_term')); ?>" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter posts by:<br><i class='fa fa-check'></i> Name." />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Categories</label>
                    <select class="selectize form-control" name="category_id" multiple placeholder="Categories..." size="1">
                        <?php foreach ($feed->categories as $category): ?>
                            <option value="<?= e($category->id) ?>" <?= e((request('category_id') == $category->id) ? 'selected' : '') ?>><?= e($category->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Published</label>
                    <div class="input-group input-daterange">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                        <input type="text" class="form-control" name="published_from" value="" placeholder="Published..." />
                        <span class="input-group-addon">to</span>
                        <input type="text" class="form-control" name="published_to" value="" />
                    </div>
                </div>

                <div class="form-group pt-1 px-2">
                    <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
                </div>

            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table id="post_list" class="table table-v2 table-striped table-hover responsive">
                    <thead>
                        <tr>
                            <th width="20"><input type="checkbox" class="master" name="selectedids_master" value="1" /></th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>URL</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    spaContentReady(function() {
        var posts_table = $('#post_list').DataTable({
            "dom": 'rtpi',
            "sErrMode":'throw',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[ 1, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": 0, "class" : "text-center" },
                { "orderable": true, "targets": 1, "class" : "text-center" },
                { "orderable": true, "targets": 2, "class" : "text-left" },
                { "orderable": true, "targets": 3, "class" : "text-center text-muted" }
            ],
            "stateSave": false,
            "ajax": {
                "url": "/jpanel/feeds/posts.ajax",
                "type": "POST",
                "data": function (d) {
                    d.feed_id = <?= e($feed->id) ?>;
                    d.search_term = $('input[name=search_term]').val();
                    d.published_from = $('input[name=published_from]').val();
                    d.published_to = $('input[name=published_to]').val();
                    d.category_id = $('select[name=category_id]').val();
                }
            },
            "drawCallback" : function(){
                j.ui.datatable.formatRows($('#post_list'));
                return true;
            },
            "initComplete" : function(){
                j.ui.datatable.formatTable($('#post_list'));
            }
        });

        $('.datatable-filters input, .datatable-filters select').each(function(i, input){
            if ($(input).data('datepicker'))
                $(input).on('changeDate', function () {
                    posts_table.draw();
                });

            else
                $(input).change(function(){
                    posts_table.draw();
                });
        });

        $('form.datatable-filters').on('submit', function(ev){
            ev.preventDefault();
        });

        j.ui.datatable.enableFilters(posts_table);

    });

    </script>
<?php endif;
