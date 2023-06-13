import type { ReactNode, FC, ComponentPropsWithRef, KeyboardEvent } from 'react'
import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import { forwardRef, useMemo } from 'react'
import classnames from 'classnames'
import { Menu } from '@headlessui/react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faChevronDown } from '@fortawesome/pro-regular-svg-icons'
import { useDropdownContext } from '@/aerosol/Dropdown/DropdownContext'
import styles from './DropdownButton.styles.scss'

type AtLeast<T, K extends keyof T> = Partial<T> & Pick<T, K>

interface DropdownButtonProps extends ComponentPropsWithRef<'button'> {
  children: ReactNode
  isClean?: boolean
  isOutlined?: boolean
  isIconVisible?: boolean
  icon?: IconDefinition
}

type Props = AtLeast<DropdownButtonProps, 'children'>

const DropdownButton: FC<Props> = forwardRef(
  ({ children, isClean, isOutlined, isIconVisible, icon, className, ...rest }, ref) => {
    const { isDisabled, toggleIsOpen, setIsOpen, errors, isFullWidth, theme = 'primary' } = useDropdownContext()
    const hasErrors = !!errors?.length

    const css = classnames(
      styles.root,
      !!children ? styles.padding : styles.paddingNoChildren,
      styles[theme],
      isClean && styles.clean,
      isOutlined && styles.outlined,
      hasErrors && styles.error,
      isDisabled && styles.disabled,
      isFullWidth && styles.fullWidth,
      className
    )

    const renderIcon = () =>
      isIconVisible ? <FontAwesomeIcon className={styles.icon} icon={icon ?? faChevronDown} /> : null

    const keydownActions = useMemo(
      () => ({
        Enter: () => toggleIsOpen(),
        Escape: () => setIsOpen(false),
        Tab: () => setIsOpen(false),
      }),
      []
    )

    const handleKeyDown = ({ key }: KeyboardEvent<HTMLButtonElement>) => keydownActions?.[key]?.()

    return (
      <Menu.Button
        {...rest}
        ref={ref}
        type='button'
        tabIndex={0}
        onKeyDown={handleKeyDown}
        onClick={toggleIsOpen}
        disabled={isDisabled}
        className={css}
      >
        <span className='truncate'>{children}</span>
        {renderIcon()}
      </Menu.Button>
    )
  }
)

DropdownButton.displayName = 'DropdownButton'

DropdownButton.defaultProps = {
  isIconVisible: true,
}

export { DropdownButton }
