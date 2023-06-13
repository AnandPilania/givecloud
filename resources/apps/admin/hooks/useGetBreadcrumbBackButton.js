import { useRecoilValue } from 'recoil'
import { useLocation } from 'react-router-dom'
import configState from '@/atoms/config'

import {
  SUPPORTERS_PATH,
  CONTRIBUTIONS_PATH,
  ABANDONED_CARTS_PATH,
  SETTINGS_EMAILS_PATH,
  PRODUCTS_PATH,
  PRODUCTS_CATEGORIES_PATH,
  SPONSORSHIPS_PATH,
  FUNDRAISING_PAGES_PATH,
  RECURRING_PAYMENTS_PATH,
  TRIBUTE_TYPES_PATH,
  MESSENGER_CONVERSATIONS_PATH,
  VIRTUAL_EVENTS_PATH,
  PROMOTIONS_PATH,
  MEMBERSHIPS_PATH,
  FEEDS_PATH,
  KIOSKS_PATH,
  PAGES_PATH,
  USERS_PATH,
  IMPORT_PATH,
  SETTINGS_PAYMENT_PATH,
} from '@/constants/pathConstants'

const useGetBreadcrumbBackButton = () => {
  let matches = null

  const { sponsorshipLabel = '' } = useRecoilValue(configState)
  const { pathname } = useLocation() || {}

  const is = (regex) => {
    if (Array.isArray(regex)) {
      return regex.find((ex) => pathname.match(ex))
    }

    return pathname.match(regex)
  }

  if (is([/^\/supporters\/[0-9]+\/edit$/, /^\/supporters\/add$/])) {
    return {
      text: 'All Supporters',
      url: SUPPORTERS_PATH,
    }
  }

  if (is([/^\/contributions\/[0-9]+\/edit$/, /^\/contributions\/add$/])) {
    if (document.title.includes('Abandoned Cart')) {
      return {
        text: 'Abandoned Carts',
        url: ABANDONED_CARTS_PATH,
      }
    } else {
      return {
        text: 'All Contributions',
        url: CONTRIBUTIONS_PATH,
      }
    }
  }

  if (is([/^\/emails\/edit$/, /^\/emails\/add$/])) {
    return {
      text: 'All Automated Emails',
      url: SETTINGS_EMAILS_PATH,
    }
  }

  if (is([/^\/products\/edit$/, /^\/products\/add$/])) {
    return {
      text: 'Sell & Fundraise Items',
      url: PRODUCTS_PATH,
    }
  }

  if (is([/^\/products\/categories\/add$/, /^\/products\/categories\/edit$/])) {
    return {
      text: 'All Categories',
      url: PRODUCTS_CATEGORIES_PATH,
    }
  }

  if (is([/^\/sponsorship\/[0-9]+$/, /^\/sponsorship\/add$/])) {
    return {
      text: `All ${sponsorshipLabel}`,
      url: SPONSORSHIPS_PATH,
    }
  }

  if (is(/^\/fundraising\/forms\/(?:[A-Z0-9]+|deleted-forms)$/i)) {
    return {
      text: 'Fundraising',
      to: '/fundraising/forms',
    }
  }

  if ((matches = is(/^\/fundraising\/forms\/([A-Z0-9]+)\/edit$/i))) {
    return {
      text: 'Fundraising Experience',
      to: `/fundraising/forms/${matches[1]}`,
    }
  }

  if (is(/^\/fundraising-pages\/[0-9]+$/)) {
    return {
      text: 'Peer-to-Peer Fundraising Pages',
      url: FUNDRAISING_PAGES_PATH,
    }
  }

  if (is([/^\/recurring_payments\/(.*)$/, /^\/recurring_payments\/(.*)\/edit$/])) {
    return {
      text: 'Recurring Profiles',
      url: RECURRING_PAYMENTS_PATH,
    }
  }

  if (is([/^\/tribute_types\/[0-9]+\/edit$/, /^\/tribute_types\/add$/])) {
    return {
      text: 'Tribute Types',
      url: TRIBUTE_TYPES_PATH,
    }
  }

  if (is(/^\/messenger\/conversations\//)) {
    return {
      text: 'All Conversations',
      url: MESSENGER_CONVERSATIONS_PATH,
    }
  }

  if (is([/^\/virtual-events\/[0-9]+\/edit$/, /^\/virtual-events\/create$/])) {
    return {
      text: 'Virtual Events',
      url: VIRTUAL_EVENTS_PATH,
    }
  }

  if (is([/^\/promotions\/edit$/, /^\/promotions\/add$/, /^\/promotions\/(.*)\/edit$/])) {
    return {
      text: 'Promotions',
      url: PROMOTIONS_PATH,
    }
  }

  if (is([/^\/memberships\/edit$/, /^\/memberships\/add$/])) {
    return {
      text: 'Memberships',
      url: MEMBERSHIPS_PATH,
    }
  }

  if (is([/^\/feeds\/posts$/, /^\/feeds\/add$/])) {
    return {
      text: 'All Feeds & Blogs',
      url: FEEDS_PATH,
    }
  }

  if (is(/^\/kiosks\/[0-9]+$/)) {
    return {
      text: 'All Kiosks',
      url: KIOSKS_PATH,
    }
  }

  if (is(/^\/pages\/edit$/)) {
    return {
      text: 'Pages & Menus',
      url: PAGES_PATH,
    }
  }

  if (is([/^\/users\/edit$/, /^\/users\/add$/])) {
    return {
      text: 'All Users',
      url: USERS_PATH,
    }
  }

  if (is(/^\/import\/[0-9]+/)) {
    return {
      text: 'Import',
      url: IMPORT_PATH,
    }
  }

  if (is(/^\/settings\/payment\/\w+/)) {
    return {
      text: 'Payment Gateways',
      url: SETTINGS_PAYMENT_PATH,
    }
  }
}

export default useGetBreadcrumbBackButton
