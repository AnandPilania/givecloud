@if ($show_branding)
    <div style="display:block;background-color:#001d2b;color:#fff;text-align:center;padding:14px;font-size:14px;line-height:18px;font-family:'Open Sans',Arial,Sans Serif">
        {!! trans('layouts.gc_footer.free_website_by', [
            'url' => sprintf(
                '<a href="https://givecloud.co/?utm_src=site_footer&utm_keyword=%s"><img style="height:20px;margin:-1px 2px 0;vertical-align:top" src="%s" alt="Givecloud"></a>',
                sys_get('ds_account_name'),
                jpanel_asset_url('images/logo.svg')
            ),
        ]); !!}
        &bull;
        <a style="display:inline-block;margin-top:-1px;vertical-align:top;color:#0088ff;text-decoration:none!important" href="https://givecloud.co/?utm_src=site_footer&utm_keyword={{ sys_get('ds_account_name') }}">{{ trans('layouts.gc_footer.get_yours') }}</a>
    </div>
@endif


@if (feature('givecloud_pro') && $has_active_admin_login)

    @php
        $btnClasses = 'btn px-3 py-2 font-weight-normal btn-sm btn-light border text-nowrap';
    @endphp

    <div class="position-fixed slide-in-right mr-3 @if($product) mb-5 mb-md-0 @endif d-flex justify-content-end align-items-center" style="bottom: 0.5rem; right: 0;">
        <div id="admin-actions-panel" class="position-absolute @if($product) mb-4 mb-md-0 @endif btn-group d-none justify-content-center align-items-center slide-out-right" role="group" style="bottom:0;">
            @if ($has_site_design_permission)
                <a href="{{ route('backend.bucket.index') }}" class="{{ $btnClasses }}">
                    <i class="fa fa-paint-brush mr-1"></i> Site Design
                </a>
            @endif
            @if ($page)
                <a href="{{ route('backend.page.edit', ['i' => $page->id ]) }}" class="{{ $btnClasses }}">
                    <i class="fa fa-file-text-o mr-1"></i> Edit Page
                </a>
            @endif
            @if ($fundraising_page)
                <a href="{{ route('backend.fundraising-pages.view', [ 'id' => $fundraising_page->id ]) }}" class="{{ $btnClasses }}">
                    <i class="fa fa-file-text-o mr-1"></i> View Fundraiser
                </a>
            @endif
            @if ($post_type)
                <a href="{{ route('backend.post.index', ['i' => $post_type->id]) }}" class="{{ $btnClasses }}">
                    <i class="fa fa-rss mr-1"></i> Edit Feed
                </a>
            @endif
            @if ($post)
                <a href="{{ route('backend.posts.edit', ['i' => $post->id]) }}" class="{{ $btnClasses }}">
                    <i class="fa fa-newspaper-o mr-1"></i> Edit Post
                </a>
            @endif
            @if ($product)
                <a href="{{ route('backend.products.edit', ['i' => $product->id]) }}" class="{{ $btnClasses }}">
                    <i class="fa fa-dollar mr-1"></i> Edit Product
                </a>
            @endif
            @if ($sponsorship)
                <a href="{{ route('backend.sponsorship.view', ['id' => $sponsorship->id ]) }}" class="{{ $btnClasses }}">
                    <i class="fa fa-user mr-1"></i> Edit Sponsee
                </a>
            @endif
            <a href="{{ route('backend.session.index') }}" class="{{ $btnClasses }}">
                <i class="fa fa-cog mr-1"></i> Go to Admin
            </a>
            <a id="admin-actions-close" href="#" class="{{ $btnClasses }}">
                <i class="fa fa-close"></i> <div class="sr-only">Close</div>
            </a>
        </div>
        <a id="admin-actions-anchor" href="#" class="position-absolute btn px-3 py-2 font-weight-normal btn-sm btn-light {% if product.id %} mb-4 mb-md-0 {% endif %} d-flex justify-content-center align-items-center border rounded py-2 px-3" style="bottom:0;">
            <span class="pr-2" style="top:0; font-size:14px; line-height:21px">Admin</span>
            <img width="30" alt="Admin Menu (This only shows when you're logged in)" title="Admin Menu (This only shows when you're logged in)" src="https://storage.googleapis.com/cdn.givecloud.co/static/branding/logo/primary/logo_mark/full_color/digital/givecloud-logo-mark-full-color-rgb.png" />
        </a>
    </div>
@endif
