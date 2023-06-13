import type { FC, HTMLProps, ReactNode } from 'react'
import type { FloatProps } from '@headlessui-float/react'
import { Menu } from '@headlessui/react'
import classNames from 'classnames'
import { useEffect, useState } from 'react'
import { DropdownContext } from '@/aerosol/Dropdown/DropdownContext'
import { useOnClickOutside } from '@/shared/hooks'
import styles from './Dropdown.styles.scss'

type AtLeast<T, K extends keyof T> = Partial<T> & Pick<T, K>
type ErrorType = string[] | null | undefined
type DropdownThemes = 'custom' | 'primary' | 'secondary'

interface DropdownProps {
  theme: DropdownThemes
  value: string
  isFullWidth: boolean
  isDisabled: boolean
  placement: FloatProps['placement']
  isOpenByDefault: boolean
  isPreventingCloseOnOutsideClick: boolean
  errors: ErrorType
  isMarginless: boolean
  isResponsive: boolean
  isAutoPlacement: boolean
  children: ReactNode
}

type Props = Omit<HTMLProps<HTMLElement>, 'as' | 'ref' | 'value'> & AtLeast<DropdownProps, 'value' | 'children'>

const Dropdown: FC<Props> = ({
  theme,
  isFullWidth,
  children,
  value,
  isDisabled,
  placement,
  isOpenByDefault = false,
  isPreventingCloseOnOutsideClick,
  errors,
  isMarginless,
  isResponsive,
  isAutoPlacement,
  ...rest
}) => {
  const [selected, setSelected] = useState(value)
  const [isOpen, setIsOpen] = useState(isOpenByDefault)
  const [ref, setDropdownRef] = useState<HTMLDivElement | null>(null)

  useEffect(() => setSelected(value), [value])

  const onClickOutside = () => {
    if (isOpen && !isPreventingCloseOnOutsideClick) {
      setIsOpen(false)
    }
  }

  useOnClickOutside<HTMLDivElement>({ ref: ref as HTMLDivElement, onClickOutside })

  const toggleIsOpen = () => setIsOpen((prevState) => !prevState)

  return (
    <DropdownContext.Provider
      value={{
        theme,
        selected,
        setSelected,
        isDisabled,
        placement,
        isOpen,
        setIsOpen,
        toggleIsOpen,
        errors,
        isFullWidth,
        isResponsive,
        isAutoPlacement,
      }}
    >
      <Menu
        as='div'
        ref={setDropdownRef}
        className={classNames(styles.root, isFullWidth && styles.fullWidth, !isMarginless && styles.marginBottom)}
        {...rest}
      >
        {children}
      </Menu>
    </DropdownContext.Provider>
  )
}

Dropdown.defaultProps = {
  value: '',
  placement: 'bottom',
  isOpenByDefault: false,
  isFullWidth: false,
  isPreventingCloseOnOutsideClick: false,
  isResponsive: true,
  isAutoPlacement: false,
}

export { Dropdown }
export { DropdownProps }
