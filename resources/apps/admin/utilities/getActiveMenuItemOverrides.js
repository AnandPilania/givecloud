const getActiveMenuItemOverrides = () => {
  return [
    {
      path: /^\/supporter_types\/add$/,
      key: 'settings_supporter',
    },
    {
      path: /^\/supporter_types\/[0-9]+\/edit$/,
      key: 'settings_supporter',
    },
    {
      path: /^\/aliases\/add$/,
      key: 'features_website_redirects',
    },
    {
      path: /^\/aliases\/[0-9]+\/edit$/,
      key: 'features_website_redirects',
    },
    {
      path: /^\/design\/customize\/add$/,
      key: 'features_website_design',
    },
    {
      path: /^\/check-ins$/,
      key: 'reports_event_check_ins',
    },
    {
      path: /^\/emails\/edit$/,
      key: 'communicate',
    },
    {
      path: /^\/emails\/add$/,
      key: 'communicate',
    },
    {
      path: /^\/feeds/,
      key: 'features_website_feeds_and_blogs',
    },
    {
      path: /^\/fundraising\/forms\/[A-Z0-9]+$/i,
      key: 'fundraising',
    },
    {
      path: /^\/fundraising-pages\/[0-9]+$/,
      key: 'features_peer_to_peer_fundraising_pages',
    },
    {
      path: /^\/kiosks$/,
      key: 'features_ios_kiosks',
    },
    {
      path: /^\/kiosks\/[0-9]+$/,
      key: 'features_ios_kiosks',
    },
    {
      path: /^\/supporters\/add$/,
      key: 'supporters',
    },
    {
      path: /^\/supporters\/[0-9]+\/edit$/,
      key: 'supporters',
    },
    {
      path: /^\/memberships\/add$/,
      key: 'features_memberships',
    },
    {
      path: /^\/memberships\/edit$/,
      key: 'features_memberships',
    },
    {
      path: /^\/contributions\/add$/,
      key: 'contributions',
    },
    {
      path: /^\/contributions\/[0-9]+\/edit$/,
      key: 'contributions',
    },
    {
      path: /^\/reports\/contribution-line-items$/,
      key: 'contributions_payments_by_line',
    },
    {
      path: /^\/pages\/add$/,
      key: 'features_website_pages',
    },
    {
      path: /^\/pages\/edit$/,
      key: 'features_website_pages',
    },
    {
      path: /^\/sponsorship\/payment_options\/add$/,
      key: 'features_sponsorship_payment_options',
    },
    {
      path: /^\/sponsorship\/payment_options\/edit$/,
      key: 'features_sponsorship_payment_options',
    },
    {
      path: /^\/settings\/payment$/,
      key: 'settings_payment_gateway',
    },
    {
      path: /^\/settings\/payment\/(.*)$/,
      key: 'settings_payment_gateway',
    },
    {
      path: /^\/products\/categories\/add$/,
      key: 'features_website_categories',
    },
    {
      path: /^\/products\/categories\/edit$/,
      key: 'features_website_categories',
    },
    {
      path: /^\/product\/detail$/,
      key: 'features_sell_and_fundraise',
    },
    {
      path: /^\/products\/add$/,
      key: 'features_sell_and_fundraise',
    },
    {
      path: /^\/products\/edit$/,
      key: 'features_sell_and_fundraise',
    },
    {
      path: /^\/promotions\/add$/,
      key: 'features_promotions',
    },
    {
      path: /^\/promotions\/edit$/,
      key: 'features_promotions',
    },
    {
      path: /^\/promotions\/(.*)\/edit$/,
      key: 'features_promotions',
    },
    {
      path: /^\/recurring_payments\/(.*)$/,
      key: 'contributions_recurring_profiles',
    },
    {
      path: /^\/recurring_payments\/(.*)\/edit$/,
      key: 'contributions_recurring_profiles',
    },
    {
      path: /^\/sponsorship\/segments\/add$/,
      key: 'features_sponsorship_custom_fields',
    },
    {
      path: /^\/sponsorship\/segments\/edit$/,
      key: 'features_sponsorship_custom_fields',
    },
    {
      path: /^\/sponsorship\/segments\/items\/add$/,
      key: 'features_sponsorship_custom_fields',
    },
    {
      path: /^\/sponsorship\/segments\/items\/edit$/,
      key: 'features_sponsorship_custom_fields',
    },
    {
      path: /^\/settings\/home$/,
      key: 'settings',
    },
    {
      path: /^\/shipping$/,
      key: 'features_sell_and_fundraise_configure_shipping',
    },
    {
      path: /^\/shipping\/add$/,
      key: 'features_sell_and_fundraise_configure_shipping',
    },
    {
      path: /^\/shipping\/edit$/,
      key: 'features_sell_and_fundraise_configure_shipping',
    },
    {
      path: /^\/shipping\/tiers\/add$/,
      key: 'features_sell_and_fundraise_configure_shipping',
    },
    {
      path: /^\/sponsor\/[0-9]+$/,
      key: 'features_sponsorship_sponsors',
    },
    {
      path: /^\/sponsor\/add\/[0-9]+$/,
      key: 'features_sponsorship_sponsors',
    },
    {
      path: /^\/sponsorship\/edit$/,
      key: 'features_sponsorship_children',
    },
    {
      path: /^\/sponsorship\/[0-9]+$/,
      key: 'features_sponsorship_children',
    },
    {
      path: /^\/sponsorship\/add$/,
      key: 'features_sponsorship_children',
    },
    {
      path: /^\/taxes\/add$/,
      key: 'features_sell_and_fundraise_configure_sales_tax',
    },
    {
      path: /^\/taxes\/edit$/,
      key: 'features_sell_and_fundraise_configure_sales_tax',
    },
    {
      path: /^\/tax_receipts\/consolidated-receipting$/,
      key: 'features_tax_receipts',
    },
    {
      path: /^\/tribute_types\/add$/,
      key: 'features_tributes_types',
    },
    {
      path: /^\/tribute_types\/[0-9]+\/edit$/,
      key: 'features_tributes_types',
    },
    {
      path: /^\/users\/add$/,
      key: 'settings_user',
    },
    {
      path: /^\/users\/edit$/,
      key: 'settings_user',
    },
    {
      path: /^\/profile$/,
      key: 'settings_user',
    },
    {
      path: /^\/virtual-events\/create$/,
      key: 'features_virtual_events',
    },
    {
      path: /^\/virtual-events\/[0-9]+\/edit$/,
      key: 'features_virtual_events',
    },
    {
      path: /^\/reports\/payments-old$/,
      key: 'contributions_payments',
    },
    {
      path: /^\/products\/[0-9]+\/contributions$/,
      key: 'features_sell_and_fundraise',
    },
    {
      path: /^\/import\/[0-9]+/,
      key: 'settings_advanced_import',
    },
    {
      path: /^\/imports\/wizard\/[0-9]+/,
      key: 'features_imports',
    },
  ]
}

export default getActiveMenuItemOverrides
