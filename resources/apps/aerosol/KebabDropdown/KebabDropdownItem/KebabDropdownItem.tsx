import type { FC, HTMLProps } from 'react'
import type { LinkProps } from 'react-router-dom'
import classNames from 'classnames'
import { Menu } from '@headlessui/react'
import { Link } from 'react-router-dom'
import { Text } from '@/aerosol/Text'
import styles from './KebabDropdownItem.styles.scss'

type ReactLinkProps = Partial<Pick<LinkProps, 'to'>>
type AdditionalProps<T = HTMLButtonElement> = HTMLProps<T> & ReactLinkProps

interface Props extends AdditionalProps<HTMLButtonElement | HTMLAnchorElement> {
  isDisabled?: boolean
}

const KebabDropdownItem: FC<Props> = ({ children, onClick, to, href, isDisabled, ...rest }) => {
  const getActiveCss = (isActive: boolean) =>
    classNames(styles.root, rest.className, isActive && styles.active, isDisabled && styles.disabled)

  const renderItem = (isActive: boolean) => {
    if (onClick) {
      return (
        <button disabled={isDisabled} className={getActiveCss(isActive)} onClick={onClick}>
          <Text isTruncated isMarginless>
            {children}
          </Text>
        </button>
      )
    }
    if (to) {
      const linkProps = isDisabled ? { role: 'link', 'aria-disabled': true, to: '' } : { to }
      return (
        <Link {...linkProps} className={getActiveCss(isActive)}>
          <Text isTruncated isMarginless>
            {children}
          </Text>
        </Link>
      )
    }

    const linkProps = isDisabled ? { role: 'link', 'aria-disabled': true } : { href }
    return (
      <a {...linkProps} className={getActiveCss(isActive)}>
        <Text isTruncated isMarginless className='whitespace-pre'>
          {children}
        </Text>
      </a>
    )
  }
  return <Menu.Item>{({ active }) => renderItem(active)}</Menu.Item>
}

KebabDropdownItem.defaultProps = {
  isDisabled: false,
}

export { KebabDropdownItem }
