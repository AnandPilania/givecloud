<?php

namespace Ds\Repositories;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Kiosk\Models\Kiosk;
use Ds\Domain\Messenger\Models\ConversationRecipient;
use Ds\Domain\Settings\Integrations\Config\AbstractIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\IntegrationSettingsService;
use Ds\Models\AccountType;
use Ds\Models\Pledge;
use Ds\Models\PromoCode;
use Ds\Models\Tribute;
use Ds\Models\VirtualEvent;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminSidebarMenuRepository
{
    /** @var \Ds\Domain\Settings\Integrations\IntegrationSettingsService */
    private $integrationSettingsService;

    /** @var \Ds\Repositories\MembershipRepository */
    private $membershipRepository;

    public function __construct(MembershipRepository $membershipRepository, IntegrationSettingsService $integrationSettingsService)
    {
        $this->membershipRepository = $membershipRepository;
        $this->integrationSettingsService = $integrationSettingsService;
    }

    /**
     * Get menu items.
     */
    public function get(): array
    {
        return array_values($this->filterMenus([
            $this->getDashboardMenu(),
            $this->getSupportersMenu(),
            $this->getFundraisingMenu(),
            $this->getFeaturesMenu(),
            $this->getFundraiseMenu(),
            $this->getContributionsMenu(),
            $this->getCommunicateMenu(),
            $this->getReportsMenu(),
            $this->getSettingsMenu(),
        ]));
    }

    public function flat(): array
    {
        return $this->filterMenus([
            $this->getSupportersMenu(),
            $this->getContributionsMenu(),
            $this->getCommunicateMenu(),

            // Features
            $this->getWebsiteMenu(),
            $this->getSellAndFundraiseMenu(),
            $this->getFundraisingMenu(),
            $this->getSponsorshipMenu(),
            $this->getPeerToPeerMenu(),
            $this->getTextToGiveMenu(),
            $this->getPOSMenu(),
            $this->getPledgesMenu(),
            $this->getTributesMenu(),
            $this->getVirtualEventsMenu(),
            $this->getKiosksMenu(),
            $this->getTaxReceiptsMenu(),
            $this->getGiftAidMenu(),
            $this->getDonorCoverCostsMenu(),
            $this->getPromotionsMenu(),
            $this->getMemberShipsMenu(),

            $this->getReportsMenu(),
            $this->getSettingsMenu(),
        ]);
    }

    public function asPinnedItems(): array
    {
        $pinned = user()->metadata('pinned-menu-items', [
            'features_website_view_site',
            'contributions',
        ]);

        $items = [];

        foreach ($this->flat() as $menu) {
            if (in_array($menu['key'], $pinned, true)) {
                $menu['label'] = $menu['pinned_label'] ?? $menu['label'];
                $items[$menu['key']] = $menu;

                if (isset($menu['new_link'])) {
                    $items[$menu['key']]['label'] = $menu['new_link']->label;
                    $items[$menu['key']]['url'] = $menu['new_link']->url;
                    $items[$menu['key']]['icon'] = 'plus';
                    $items[$menu['key']]['is_external'] = $menu['new_link']->is_external ?? false;

                    unset($items[$menu['key']]['children'], $items[$menu['key']]['new_link']);
                }
            }

            if (! array_key_exists('children', $menu)) {
                continue;
            }

            foreach ($menu['children'] as $child) {
                if (! array_key_exists('key', $child)) {
                    continue;
                }
                if (in_array($child['key'], $pinned, true)) {
                    $child['icon'] = $child['icon'] ?? $menu['icon'];
                    $child['label'] = $child['pinned_label'] ?? $child['label'];
                    $items[$child['key']] = $child;
                }
            }
        }

        // Reorder as pinned order
        return array_values(array_replace(array_flip($pinned), $items));
    }

    private function filterMenus(array $menus): array
    {
        $menus = array_map(function ($menu) {
            if (Arr::exists($menu, 'children')) {
                $menu['children'] = $this->filterMenus($menu['children']);
            }

            return $menu;
        }, $menus);

        $menus = array_filter($menus, function ($menu) {
            return $this->shouldShowMenu($menu);
        });

        if ($this->hasArrayValues($menus)) {
            $menus = array_values($menus);
        }

        return $menus;
    }

    private function shouldShowMenu(array $menu): bool
    {
        if (Arr::exists($menu, 'show')) {
            return $menu['show'];
        }

        if (Arr::exists($menu, 'children')) {
            return ! empty($menu['children']) || ! empty($menu['url']);
        }

        return true;
    }

    /**
     * Verify if an array has only numeric keys.
     */
    private function hasArrayValues(array $menu): bool
    {
        return count(array_filter(array_keys($menu), 'is_string')) <= 0;
    }

    private function isMembershipsInUse(): bool
    {
        return feature('membership')
            && (bool) $this->membershipRepository->queryCreatedOrUpdatedAfterPurchase()->count();
    }

    private function getDashboardMenu(): array
    {
        return [
            'key' => 'dashboard',
            'label' => isGivecloudExpress() ? 'My Home' : 'Dashboard',
            'show' => user()->can('dashboard'),
            'url' => route('backend.session.index', [], true),
        ];
    }

    private function getSupportersMenu(): array
    {
        $accountTypes = AccountType::all(['id', 'is_organization']);

        [$organizationAccountTypeIds, $individualAccountTypeIds] = $accountTypes
            ->partition->is_organization
            ->map(function ($accountTypeIds) {
                return $accountTypeIds->map->id;
            });

        $twelveMonthsAgo = fromLocal('-1 year')->toDateString();
        $eightWeeksAgo = fromLocal('-8 weeks')->toDateString();

        return [
            'key' => 'supporters',
            'label' => 'Supporters',
            'show' => (feature('accounts') && user()->can('member')),
            'icon' => 'user-friends',
            'new_link' => (object) [
                'label' => 'New Supporter',
                'url' => route('backend.member.add', [], false),
            ],
            'children' => [
                [
                    'key' => 'supporters_all_supporters',
                    'label' => 'All Supporters',
                    'url' => route('backend.member.index', [], false),
                ], [
                    'key' => 'supporters_individuals',
                    'label' => 'Individuals',
                    'show' => feature('givecloud_pro'),
                    'url' => route('backend.member.index', ['ft' => $individualAccountTypeIds->implode(',')], false),
                ], [
                    'key' => 'supporters_organizations',
                    'label' => 'Organizations',
                    'show' => feature('givecloud_pro'),
                    'url' => route('backend.member.index', ['ft' => $organizationAccountTypeIds->implode(',')], false),
                ], [
                    'key' => 'supporters_sponsors',
                    'label' => 'Sponsors',
                    'show' => feature('sponsorship') && user()->can('sponsorship'),
                    'url' => route('backend.sponsors.index', ['fromMenu' => 'supporters_sponsors'], false),
                ], [
                    'key' => 'supporters_members',
                    'label' => 'Members',
                    'show' => $this->isMembershipsInUse() && user()->can('membership'),
                    'url' => route('backend.reports.members.index', [], false),
                ], [
                    'key' => 'supporters_new_donors',
                    'label' => 'New Donors',
                    'url' => route('backend.member.index', ['firstPaymentAfter' => $eightWeeksAgo], false),
                ], [
                    'key' => 'supporters_active_donors',
                    'label' => 'Active Donors',
                    'url' => route('backend.member.index', ['lastPaymentAfter' => $twelveMonthsAgo], false),
                ], [
                    'key' => 'supporters_recurring_donors',
                    'label' => 'Recurring Donors',
                    'show' => user()->can('recurringpaymentprofile'),
                    'url' => route('backend.member.index', ['rpp' => 'active'], false),
                ], [
                    'key' => 'supporters_slipping_recurring_donors',
                    'label' => 'Slipping Recurring Donors',
                    'show' => user()->can('recurringpaymentprofile'),
                    'url' => route('backend.member.index', ['is_slipping' => '1'], false),
                ], [
                    'key' => 'supporters_lapsed_donors',
                    'label' => 'Lapsed Donors',
                    'url' => route('backend.member.index', ['lastPaymentBefore' => $twelveMonthsAgo], false),
                ], [
                    'key' => 'supporters_marketing_opt_ins',
                    'label' => 'Marketing Opt-ins',
                    'url' => route('backend.member.index', ['fe' => '1'], false),
                ], [
                    'key' => 'supporters_text_to_give_users',
                    'label' => 'Text-to-Give Users',
                    'show' => feature('messenger') && user()->can('messenger') && ConversationRecipient::count(),
                    'url' => route('backend.member.index', ['used_text_to_give' => '1'], false),
                ], [
                    'key' => 'supporters_fundraisers',
                    'label' => 'Fundraisers',
                    'show' => feature('fundraising_pages') && sys_get('fundraising_pages_enabled') && user()->can('fundraisingpages'),
                    'url' => route('backend.member.index', ['fundraisers' => 'active,closed'], false),
                ], [
                    'key' => 'supporters_portal_users',
                    'label' => 'Portal Users',
                    'show' => feature('givecloud_pro'),
                    'url' => route('backend.member.index', ['has_login' => '1', 'has_logged_in' => '1'], false),
                ], [
                    'key' => 'supporters_archived',
                    'label' => 'Archived',
                    'pinned_label' => 'Archived Supporters',
                    'url' => route('backend.member.index', ['fA' => '0'], false),
                ],
            ],
        ];
    }

    private function getFeaturesMenu(): array
    {
        if (isGivecloudExpress()) {
            $children = collect([
                'Website',
                'Online Store',
                'Peer-to-Peer',
                'Text to Give',
                'Pledges',
                'Tributes',
                'Virtual Events',
                'iOS Kiosk',
                'Promotions',
                'Memberships',
                'Point of Sale',
                'Sponsorship',
            ]);

            return [
                'key' => 'features',
                'label' => 'Features',
                'flyout_pill_classes' => 'upgrade',
                'flyout_pill_label' => 'UPGRADE',
                'flyout_pill_url' => 'https://calendly.com/givecloud-sales/givecloud-upgrade-call',
                'flyout_pill_is_external' => true,
                'icon' => 'grip-horizontal',
                'children' => [
                    'available' => [
                        'label' => 'Available',
                        'children' => $children->map(function ($label) {
                            return [
                                'key' => 'features_available_' . Str::slug($label, '_'),
                                'label' => $label,
                                'is_external' => true,
                            ];
                        })->all(),
                    ],
                ],
            ];
        }

        $featureUseStatuses = $this->getFeaturesMenuItemsWithUsage();

        return [
            'key' => 'features',
            'label' => 'Features',
            'icon' => 'grip-horizontal',
            'children' => [
                'in_use' => [
                    'label' => 'In-Use',
                    'children' => $featureUseStatuses->filter->inUse->map->menu->values()->toArray(),
                ],
                'available' => [
                    'label' => 'Available',
                    'children' => $featureUseStatuses->reject->inUse->map->menu->values()->toArray(),
                ],
            ],
        ];
    }

    private function getFundraiseMenu(): array
    {
        return [
            'key' => 'fundraise',
            'label' => 'Fundraise',
            'pill_label' => 'EARLY ACCESS',
            'show' => ! feature('fundraising_forms') && user()->can('customize.edit'),
            'url' => route('backend.fundraise.splash', [], true),
        ];
    }

    private function getFundraisingMenu(): array
    {
        return [
            'key' => 'fundraising',
            'label' => isGivecloudExpress() ? 'Fundraising' : 'Fundraising 👈',
            'show' => feature('fundraising_forms') && user()->can('product.edit'),
            'url' => $url = route('backend.fundraising.forms', [], true),
            'to' => Str::after($url, '/jpanel'),
        ];
    }

    private function getDonorCoverCostsMenu(): array
    {
        return [
            'key' => 'features_dcc',
            'icon' => 'hand-holding-usd',
            'label' => 'Donor Covers Costs',
            'children' => [
                [
                    'key' => 'features_dcc_all_dcc_contributions',
                    'label' => 'All DCC Contributions',
                    'show' => user()->can('reports.donor_covers_costs'),
                    'url' => route('backend.reports.donor-covers-costs.index', [], false),
                ],
                [
                    'key' => 'features_dcc_configure',
                    'label' => 'Configure',
                    'pinned_label' => 'Configure DCC',
                    'show' => user()->can('admin.payments'),
                    'url' => route('backend.settings.dcc', [], false),
                ],
            ],
        ];
    }

    private function getGiftAidMenu(): array
    {
        return [
            'key' => 'features_gift_aid',
            'icon' => 'file-invoice-dollar',
            'label' => 'Gift Aid',
            'show' => user()->can('giftaid.edit'),
            'url' => route('backend.settings.gift_aid', [], false),
        ];
    }

    private function getKiosksMenu(): array
    {
        return [
            'key' => 'features_ios_kiosks',
            'icon' => 'desktop',
            'label' => 'iOS Kiosks',
            'show' => user()->can('kiosk.view'),
            'url' => route('backend.kiosks.index', [], false),
        ];
    }

    private function getMemberShipsMenu(): array
    {
        return [
            'key' => 'features_memberships',
            'icon' => 'id-card-alt',
            'label' => 'Memberships',
            'show' => user()->can('membership'),
            'url' => route('backend.memberships.index', [], false),
        ];
    }

    private function getPeerToPeerMenu(): array
    {
        return [
            'key' => 'features_peer_to_peer',
            'icon' => 'user-friends',
            'label' => 'Peer-to-Peer',
            'show' => feature('fundraising_pages') && user()->can('fundraisingpages'),
            'children' => [
                [
                    'key' => 'features_peer_to_peer_fundraising_pages',
                    'label' => 'Fundraising Pages',
                    'show' => user()->can('fundraisingpages'),
                    'url' => route('backend.fundraising_pages.index', [], false),
                ],
                [
                    'key' => 'features_peer_to_peer_configure',
                    'label' => 'Configure',
                    'pinned_label' => 'Configure Peer-to-Peer',
                    'show' => user()->can('fundraisingpages.edit'),
                    'url' => route('backend.settings.fundraising_pages', [], false),
                ],
            ],
        ];
    }

    private function getPledgesMenu(): array
    {
        return [
            'key' => 'features_pledges',
            'icon' => 'flag-checkered',
            'label' => 'Pledges',
            'show' => feature('pledges'),
            'children' => [
                [
                    'key' => 'features_pledges_pledges',
                    'label' => 'Pledges',
                    'show' => user()->can('pledges'),
                    'url' => route('backend.pledges.index', [], false),
                ],
                [
                    'key' => 'features_pledges_contributions',
                    'label' => 'Contributions',
                    'pinned_label' => 'Pledge Contributions',
                    'show' => user()->can('reports.pledge-campaigns'),
                    'url' => route('backend.reports.pledge-campaigns.index', [], false),
                ],
                [
                    'key' => 'features_pledges_campaigns',
                    'label' => 'Pledge Campaigns',
                    'show' => user()->can('pledgecampaigns'),
                    'url' => route('backend.campaign.index', [], false),
                ],
            ],
        ];
    }

    private function getPOSMenu(): array
    {
        return [
            'key' => 'features_pos',
            'icon' => 'money-check-alt',
            'label' => 'Point of Sale',
            'show' => user()->can('pos.edit'),
            'children' => [
                [
                    'key' => 'features_pos_pos',
                    'label' => 'Virtual Point of Sale',
                    'url' => route('backend.pos.index', [], false),
                    'is_external' => true,
                ], [
                    'key' => 'features_pos_configure',
                    'label' => 'Configure',
                    'pinned_label' => 'Configure POS',
                    'url' => route('backend.settings.pos', [], false),
                ],
            ],
        ];
    }

    private function getPromotionsMenu(): array
    {
        return [
            'key' => 'features_promotions',
            'icon' => 'ticket',
            'label' => 'Promotions',
            'show' => user()->can('promocode.view') && feature('promos'),
            'url' => route('backend.promotions.index', [], false),
        ];
    }

    private function getSellAndFundraiseMenu(): array
    {
        return [
            'key' => 'features_sell_and_fundraise',
            'icon' => 'shopping-cart',
            'label' => 'Sell & Fundraise',
            'children' => [
                [
                    'key' => 'features_sell_and_fundraise_items',
                    'label' => 'Items',
                    'pinned_label' => 'Sell & Fundraise Items',
                    'show' => user()->can('product'),
                    'url' => route('backend.products.index', [], false),
                ],
                [
                    'key' => 'features_sell_and_fundraise_configure_shipping',
                    'label' => 'Configure Shipping',
                    'show' => user()->can('shipping.edit'),
                    'url' => route('backend.settings.shipping', [], false),
                ],
                [
                    'key' => 'features_sell_and_fundraise_configure_sales_tax',
                    'label' => 'Configure Sales Tax',
                    'show' => feature('taxes') && user()->can('tax'),
                    'url' => route('backend.taxes.index', [], false),
                ],
            ],
        ];
    }

    private function getSponsorshipMenu(): array
    {
        return [
            'key' => 'features_sponsorship',
            'icon' => 'child',
            'label' => 'Sponsorship',
            'children' => [
                [
                    'key' => 'features_sponsorship_children',
                    'label' => sys_get('syn_sponsorship_children'),
                    'show' => user()->can('sponsorship'),
                    'url' => route('backend.sponsorship.index', [], false),
                ],
                [
                    'key' => 'features_sponsorship_sponsors',
                    'label' => (sys_get('sponsorship_database_name') ? 'Local ' : '') . 'Sponsors',
                    'show' => user()->can('sponsor.view'),
                    'url' => route('backend.sponsors.index', ['fromMenu' => 'features_sponsorship_sponsors'], false),
                ],
                [
                    'key' => 'features_sponsorship_custom_fields',
                    'label' => 'Custom Fields',
                    'show' => user()->can('segment'),
                    'url' => route('backend.segment.index', [], false),
                ],
                [
                    'key' => 'features_sponsorship_payment_options',
                    'label' => 'Payment Options',
                    'show' => user()->can('paymentoption'),
                    'url' => route('backend.sponsorship.payment_options.index', [], false),
                ],
                [
                    'key' => 'features_sponsorship_mature',
                    'label' => 'Mature Children',
                    'show' => user()->can('sponsor.mature'),
                    'url' => route('backend.sponsorship.index', ['is_mature' => '1'], false),
                ],
                [
                    'key' => 'features_sponsorship_configure',
                    'label' => 'Configure',
                    'pinned_label' => 'Configure Sponsorship',
                    'show' => user()->can('sponsorship.edit'),
                    'url' => route('backend.settings.sponsorship', [], false),
                ],
            ],
        ];
    }

    private function getTributesMenu(): array
    {
        return [
            'key' => 'features_tributes',
            'icon' => 'heart',
            'label' => 'Tributes',
            'show' => user()->can(['tribute', 'tributetype']),
            'children' => [
                [
                    'key' => 'features_tributes_all',
                    'label' => 'All Tributes',
                    'show' => user()->can('tribute'),
                    'url' => route('backend.tributes.index', [], false),
                ],
                [
                    'key' => 'features_tributes_types',
                    'label' => 'Tribute Types',
                    'show' => user()->can('tributetype.edit'),
                    'url' => route('backend.tribute_types.index', [], false),
                ],
            ],
        ];
    }

    private function getTaxReceiptsMenu(): array
    {
        return [
            'key' => 'features_tax_receipts',
            'icon' => 'file-invoice-dollar',
            'label' => 'Tax Receipts',
            'show' => user()->can(['taxreceipt', 'taxreceipt.edit']),
            'children' => [
                [
                    'key' => 'features_tax_receipts_all',
                    'label' => 'All Tax Receipts',
                    'show' => user()->can('taxreceipt'),
                    'url' => route('backend.tax_receipts.index', [], false),
                ],
                [
                    'key' => 'features_tax_receipts_configure',
                    'label' => 'Configure Tax Receipts',
                    'show' => user()->can('taxreceipt.edit'),
                    'url' => route('backend.settings.tax_receipts', [], false),
                ],
            ],
        ];
    }

    private function getTextToGiveMenu(): array
    {
        return [
            'key' => 'features_text_to_give',
            'icon' => 'comment-alt-dots',
            'label' => 'Text to Give',
            'show' => feature('messenger') && user()->can('messenger'),
            'url' => route('backend.messenger.conversations', [], false),
        ];
    }

    private function getVirtualEventsMenu(): array
    {
        return [
            'key' => 'features_virtual_events',
            'icon' => 'signal-stream',
            'label' => 'Virtual Events',
            'show' => user()->can('virtualevents.view'),
            'url' => route('backend.virtual-events.index', [], false),
        ];
    }

    private function getWebsiteMenu(): array
    {
        return [
            'key' => 'features_website',
            'label' => 'Website',
            'icon' => 'globe-americas',
            'show' => (user()->can(['node', 'posttype', 'post', 'productcategory', 'customize', 'template', 'alias', 'file', 'admin.website'])),
            'children' => [
                [
                    'key' => 'features_website_pages',
                    'label' => 'Pages & Menus',
                    'show' => user()->can('node'),
                    'url' => route('backend.page.index', [], false),
                ], [
                    'key' => 'features_website_feeds_and_blogs',
                    'label' => 'Feeds & Blogs',
                    'show' => user()->can('posttype'),
                    'url' => route('backend.feeds.index', [], false),
                ], [
                    'key' => 'features_website_categories',
                    'label' => 'Categories',
                    'show' => user()->can('productcategory'),
                    'url' => route('backend.product_category.index', [], false),
                ], [
                    'key' => 'features_website_design',
                    'label' => 'Site Design',
                    'show' => user()->can(['customize', 'template']),
                    'url' => route('backend.bucket.index', [], false),
                ], [
                    'key' => 'features_website_redirects',
                    'label' => 'Redirects',
                    'show' => user()->can('alias'),
                    'url' => route('backend.alias.index', [], false),
                ], [
                    'key' => 'features_website_image_library',
                    'label' => 'Image Library',
                    'show' => user()->can('file'),
                ], [
                    'key' => 'features_website_downloads_library',
                    'label' => 'Downloads Library',
                    'show' => feature('edownloads') && user()->can('file'),
                ], [
                    'key' => 'features_website_configure',
                    'label' => 'Configure',
                    'pinned_label' => 'Configure Website',
                    'show' => user()->can('admin.website'),
                    'url' => route('backend.settings.website', [], false),
                ], [
                    'key' => 'features_website_view_site',
                    'label' => 'View Live Site',
                    'url' => secure_site_url(),
                    'is_external' => true,
                ],
            ],
        ];
    }

    private function getFeaturesMenuItemsWithUsage(): Collection
    {
        return new Collection([
            [
                'menu' => $this->getWebsiteMenu(),
                'inUse' => true,
            ],
            [
                'menu' => $this->getSellAndFundraiseMenu(),
                'inUse' => true,
            ],
            [
                'menu' => $this->getSponsorshipMenu(),
                'inUse' => feature('sponsorship'),
            ],
            [
                'menu' => $this->getPeerToPeerMenu(),
                'inUse' => feature('fundraising_pages')
                    && sys_get('fundraising_pages_enabled'),
            ],
            [
                'menu' => $this->getTextToGiveMenu(),
                'inUse' => feature('messenger')
                    && ConversationRecipient::count(),
            ],
            [
                'menu' => $this->getPledgesMenu(),
                'inUse' => feature('pledges')
                    && Pledge::count(),
            ],
            [
                'menu' => $this->getTributesMenu(),
                'inUse' => Tribute::count(),
            ],
            [
                'menu' => $this->getVirtualEventsMenu(),
                'inUse' => feature('virtual_events')
                    && VirtualEvent::count(),
            ],
            [
                'menu' => $this->getKiosksMenu(),
                'inUse' => feature('kiosks')
                    && Kiosk::count(),
            ],
            [
                'menu' => $this->getTaxReceiptsMenu(),
                'inUse' => feature('tax_receipt'),
            ],
            [
                'menu' => $this->getGiftAidMenu(),
                'inUse' => sys_get('gift_aid') == 1,
            ],
            [
                'menu' => $this->getDonorCoverCostsMenu(),
                'inUse' => (bool) sys_get('dcc_enabled'),
            ],
            [
                'menu' => $this->getPromotionsMenu(),
                'inUse' => feature('promos')
                    && PromoCode::count(),
            ],
            [
                'menu' => $this->getMemberShipsMenu(),
                'inUse' => $this->isMembershipsInUse(),
            ],
            [
                'menu' => $this->getPOSMenu(),
                'inUse' => true,
            ],
            [
                'menu' => [
                    'key' => 'features_imports',
                    'label' => 'Imports',
                    'url' => route('backend.imports.index', [], true),
                    'show' => is_super_user() && feature('imports'),
                ],
                'inUse' => true,
            ],
        ]);
    }

    private function getContributionsMenu(): array
    {
        $newLink = (object) [
            'label' => 'New Contribution (POS)',
            'url' => route('backend.pos.index', [], false),
            'is_external' => true,
        ];

        return [
            'key' => 'contributions',
            'label' => 'Contributions',
            'icon' => 'thermometer-three-quarters',
            'new_link' => user()->can('pos.edit') ? $newLink : null,
            'children' => [
                [
                    'key' => 'contributions_all',
                    'label' => 'All Contributions',
                    'show' => user()->can('order'),
                    'url' => route('backend.orders.index', [], false),
                ], [
                    'key' => 'contributions_incomplete_transactions',
                    'label' => 'Unfulfilled Contributions',
                    'show' => user()->can('order') && sys_get('use_fulfillment') !== 'never',
                    'url' => route('backend.orders.index', ['c' => 0], false),
                ], [
                    'key' => 'contributions_recurring_transactions',
                    'label' => 'Recurring Transactions',
                    'show' => ! sys_get('rpp_donorperfect') && user()->can('recurringpaymentprofile.view'),
                    'url' => route('backend.reports.transactions.index', [], false),
                ], [
                    'key' => 'contributions_recurring_profiles',
                    'label' => 'Recurring Profiles',
                    'show' => user()->can('recurringpaymentprofile'),
                    'url' => route('backend.recurring_payments.index', [], false),
                ], [
                    'key' => 'contributions_payments',
                    'label' => 'Payments',
                    'show' => user()->can('reports.payments_details'),
                    'url' => route('backend.reports.payments.index', [], false),
                ], [
                    'key' => 'contributions_payments_by_line',
                    'label' => 'Contribution Line Items',
                    'show' => user()->can('reports.orders_by_product'),
                    'url' => route('backend.reports.contribution-line-items.index', [], false),
                ], [
                    'key' => 'contributions_by_product',
                    'label' => 'Contributions by Product',
                    'show' => user()->can('reports.orders_by_product'),
                    'url' => route('backend.reports.contributions-by-product.index', [], false),
                ], [
                    'key' => 'contributions_refunds',
                    'label' => 'Refunds',
                    'show' => user()->can('reports.payments_details'),
                    'url' => route('backend.reports.payments.index', ['fp' => 'refunded'], false),
                ], [
                    'key' => 'contributions_settlement_batches',
                    'label' => 'Settlement Batches',
                    'show' => user()->can('reports.settlements') && PaymentProvider::enabled()->whereIn('provider', ['nmi', 'safesave'])->exists(),
                    'url' => route('backend.reports.settlements.index', [], false),
                ], [
                    'key' => 'contributions_abandoned',
                    'label' => 'Abandoned Carts',
                    'show' => user()->can('reports.abandoned_carts'),
                    'url' => route('backend.orders.abandoned_carts', [], false),
                ],
            ],
        ];
    }

    private function getCommunicateMenu(): array
    {
        return [
            'key' => 'communicate',
            'label' => 'Communicate',
            'show' => user()->can(['email']),
            'icon' => 'envelope-open',
            'new_link' => (object) [
                'label' => 'New Automated Email',
                'url' => route('backend.emails.add', [], false),
            ],
            'children' => [
                [
                    'key' => 'communicate_all_emails',
                    'label' => 'All Automated Emails',
                    'url' => route('backend.settings.email', [], false),
                ],
            ],
        ];
    }

    private function getReportsMenu(): array
    {
        return [
            'key' => 'reports',
            'label' => 'Reports',
            'icon' => 'chart-bar',
            'children' => [
                [
                    'key' => 'reports_impact_by_supporter',
                    'label' => 'Impact by Supporter',
                    'show' => user()->can('reports') && user()->can('member'),
                    'url' => route('backend.reports.impact_by_supporter.index', [], false),
                ], [
                    'key' => 'reports_referral_sources',
                    'label' => 'Referral Sources',
                    'show' => user()->can('reports.referral_sources'),
                    'url' => route('backend.reports.referral_sources.index', [], false),
                ], [
                    'key' => 'reports_stock_levels',
                    'label' => 'Stock Levels',
                    'show' => user()->can('reports.stock_levels'),
                    'url' => route('backend.reports.stock.index', [], false),
                ], [
                    'key' => 'reports_shipping_reconciliation',
                    'label' => 'Shipping Reconciliation',
                    'show' => user()->can('reports.shipping'),
                    'url' => route('backend.reports.shipping.index', [], false),
                ], [
                    'key' => 'reports_tax_reconciliation',
                    'label' => 'Tax Reconciliation',
                    'show' => user()->can('reports.tax_reconciliation'),
                    'url' => route('backend.reports.tax.index', [], false),
                ], [
                    'key' => 'reports_event_check_ins',
                    'label' => 'Event Check-Ins',
                    'show' => user()->can('reports.check_ins') && feature('check_ins'),
                    'url' => route('backend.reports.check_ins.index', [], false),
                ],
            ],
        ];
    }

    private function getSettingsMenu(): array
    {
        if (isGivecloudExpress() && ! is_super_user()) {
            return [
                'key' => 'settings',
                'label' => 'Settings',
                'icon' => 'cog',
                'show' => user()->can('admin.general'),
                'url' => route('backend.settings.general', [], false),
            ];
        }

        $integrations = $this->integrationSettingsService->getInstalledAndAdministrable()
            ->map(function (AbstractIntegrationSettingsConfig $integration) {
                return [
                    'key' => 'settings_integrations_' . $integration->id,
                    'label' => $integration->name,
                    'url' => $integration->config_url,
                ];
            })->push([
                'key' => 'settings_integrations_more',
                'label' => 'Browse more...',
                'url' => route('backend.settings.integrations', [], false),
            ])->all();

        return [
            'key' => 'settings',
            'label' => 'Settings',
            'icon' => 'cog',
            'children' => [
                [
                    'key' => 'settings_billing_items',
                    'label' => 'Billing',
                    'icon' => 'file-invoice-dollar',
                    'children' => [
                        [
                            'key' => 'settings_billing',
                            'label' => 'Plan & Payment',
                            'show' => user()->can('admin.billing'),
                            'url' => route('backend.settings.billing', [], false),
                        ],
                        [
                            'key' => 'settings_chargebee_payment_methods',
                            'label' => 'Edit Payment Methods',
                            'show' => site()->ds_account_name === 'alphachiomegaok' && (is_super_user() || user()->email === 'updates@affinityconnection.com'),
                            'url' => 'javascript:j.openCustomerPortal(\'EDIT_PAYMENT_SOURCE\');',
                        ],
                        [
                            'key' => 'settings_billing_transaction_fees',
                            'label' => 'Platform Fee Statements',
                            'show' => user()->can('reports.transaction_fees'),
                            'url' => route('backend.reports.transaction_fees.index', [], false),
                        ],
                    ],
                ], [
                    'key' => 'settings_user',
                    'label' => 'Users',
                    'show' => user()->can('user'),
                    'url' => route('backend.users.index', [], false),
                ], [
                    'key' => 'settings_security',
                    'label' => 'Security',
                    'show' => user()->can('admin.security'),
                    'url' => route('backend.security.index', [], false),
                ], [
                    'key' => 'settings_supporter',
                    'label' => 'Supporter Preferences',
                    'show' => user()->can('admin.accounts'),
                    'url' => route('backend.settings.supporters', [], false),
                ], [
                    'key' => 'settings_payment_gateway',
                    'label' => 'Payment Gateway',
                    'show' => user()->can('admin.payments'),
                    'url' => route('backend.settings.payment', [], false),
                ], [
                    'key' => 'settings_payment',
                    'label' => 'Payment Preferences',
                    'show' => user()->can('admin.payments'),
                    'url' => route('backend.settings.payments', [], false),
                ], [
                    'key' => 'settings_integrations',
                    'label' => 'Integrations',
                    'icon' => 'plug',
                    'show' => user()->can(['hooks.edit', 'admin.dpo', 'admin.paypal', 'admin.gocardless']),
                    'children' => $integrations,
                ], [
                    'key' => 'settings_support_only',
                    'label' => 'Support Only',
                    'icon' => 'user-headset',
                    'show' => is_super_user(),
                    'children' => [
                        [
                            'key' => 'settings_advanced',
                            'label' => 'Advanced Settings',
                            'url' => route('backend.settings.index', [], false),
                        ], [
                            'key' => 'settings_advanced_import',
                            'label' => 'Import',
                            'url' => route('backend.import', [], false),
                        ], [
                            'key' => 'settings_advanced_import_sponsee_photos',
                            'label' => 'Sponsee Photo Importer',
                            'url' => route('backend.import_sponsee_photos.index', [], false),
                        ], [
                            'key' => 'settings_advanced_utilisites_media_force_download',
                            'label' => 'Media Force Download',
                            'url' => route('backend.utilities.media_force_download.index', [], false),
                        ], [
                            'key' => 'settings_advanced_transient_logs',
                            'label' => 'Transient Logs',
                            'url' => route('backend.reports.transient_logs.index', [], false),
                        ], [
                            'key' => 'settings_advanced_utilities',
                            'label' => 'Utilities',
                            'url' => route('backend.utilities', [], false),
                        ],
                    ],
                ],
            ],
        ];
    }
}
