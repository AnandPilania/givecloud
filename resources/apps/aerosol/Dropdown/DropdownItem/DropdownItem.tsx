import type { FC, HTMLProps, MouseEvent, ReactNode } from 'react'
import type { LinkProps } from 'react-router-dom'
import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import { Link } from 'react-router-dom'
import classnames from 'classnames'
import { Menu } from '@headlessui/react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck, faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'
import { Text } from '@/aerosol/Text'
import { useDropdownContext } from '@/aerosol/Dropdown/DropdownContext'
import styles from './DropdownItem.styles.scss'

type ReactLinkProps = Partial<Pick<LinkProps, 'to'>>
interface DropdownItemProps<T = HTMLProps<HTMLAnchorElement>> extends HTMLProps<T>, ReactLinkProps {
  value: string
  icon?: IconDefinition
  isDisabled?: boolean
  children?: ReactNode
  isError?: boolean
}

type Props = Omit<DropdownItemProps<HTMLButtonElement | HTMLAnchorElement>, 'ref'>

const DropdownItem: FC<Props> = ({
  children,
  onClick,
  value,
  to,
  icon,
  href,
  isDisabled,
  className,
  isError,
  ...rest
}) => {
  const { selected, setSelected, toggleIsOpen, theme = 'primary' } = useDropdownContext()
  const isSelected = value === selected

  const getCSS = (active: boolean) =>
    classnames(
      styles.root,
      active && styles.active,
      active && styles[theme],
      isError && styles.error,
      isDisabled && styles.disabled,
      className
    )

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    if (value) {
      setSelected(value)
      onClick?.(e)
    } else {
      onClick?.(e)
    }
    toggleIsOpen()
  }

  const renderIcon = () => {
    if (icon) return <FontAwesomeIcon aria-hidden='true' className={styles.icon} icon={icon} />
    if (isError)
      return (
        <>
          <span className='sr-only'>{value} has one or more errors.</span>
          <FontAwesomeIcon aria-hidden='true' className={styles.icon} icon={faExclamationCircle} />
        </>
      )

    return null
  }

  const renderCheckmark = () => {
    if (isSelected) return <FontAwesomeIcon aria-hidden='true' className={styles.icon} icon={faCheck} />
    return null
  }

  const renderButton = (active: boolean) => {
    return (
      <button {...rest} type='button' className={getCSS(active)} onClick={handleClick} value={value}>
        <Text role='option' aria-selected={isSelected} className='sr-only' isMarginless children={children ?? value} />
        {children ?? value}
        {renderIcon()}

        {renderCheckmark()}
      </button>
    )
  }

  const renderContent = (active: boolean) => {
    if (onClick) return renderButton(active)
    if (to) {
      return (
        <Link to={to} className={getCSS(active)}>
          <Text className={styles.center} isMarginless>
            {children}
            {renderIcon()}
          </Text>
        </Link>
      )
    }
    return (
      <a {...rest} href={href} className={getCSS(active)}>
        <Text className={styles.center} isMarginless>
          {children}
          {renderIcon()}
        </Text>
      </a>
    )
  }

  return <Menu.Item disabled={isDisabled}>{({ active }) => renderContent(active)}</Menu.Item>
}

export { DropdownItem }
