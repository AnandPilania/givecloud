import isArray from 'lodash/isArray'
import keys from 'lodash/keys'
import getActiveMenuItemOverrides from '@/utilities/getActiveMenuItemOverrides'
import { BASE_ADMIN_PATH } from '@/constants/pathConstants'

const isActivePathOverride = ({ menuItem = {}, location = {} }) => {
  const { key } = menuItem
  const { pathname } = location
  const activeMenuItemOverrides = getActiveMenuItemOverrides()

  return !!activeMenuItemOverrides.find(({ path: overridePath, key: overrideKey }) => {
    return pathname?.match(overridePath) && key === overrideKey
  })
}

const isPathMatch = ({ menuItem = {}, location = {} }) => {
  const { pathname = '', search = '' } = location

  const computedMenuItemPath = menuItem?.url
    ? menuItem?.url?.split?.(BASE_ADMIN_PATH)?.pop() || '/'
    : menuItem?.url

  const isSamePath = `${pathname}${search}` === computedMenuItemPath

  return isSamePath || isActivePathOverride({ menuItem, location })
}

const getIsActiveMenuItem = ({ menuItem = {}, location = {} }) => {
  const children = menuItem?.children
  let isActive = isPathMatch({ menuItem, location })

  if (!isActive && isArray(children) && children?.length) {
    for (let i = 0; i < children?.length; i++) {
      const child = children?.[i]

      isActive = getIsActiveMenuItem({ menuItem: child, location })

      if (isActive) break
    }
  } else if (!isActive && keys(children)?.length) {
    for (let i = 0; i < keys(children)?.length; i++) {
      const section = children?.[keys(children)?.[i]]

      for (let j = 0; j < section?.children?.length; j++) {
        const child = section?.children?.[j]

        isActive = getIsActiveMenuItem({ menuItem: child, location })

        if (isActive) break
      }

      if (isActive) break
    }
  }

  return isActive
}

export default getIsActiveMenuItem
