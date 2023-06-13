import type { FC } from 'react'
import type { FloatProps } from '@headlessui-float/react'
import type { DropdownProps } from '@/aerosol/Dropdown'
import type { InputProps } from '@/aerosol/Input'
import classNames from 'classnames'
import { Dropdown, DropdownContent } from '@/aerosol/Dropdown'
import { Input } from '@/aerosol/Input'
import { Label } from '@/aerosol/Label'
import styles from './InputDropdown.styles.scss'

interface InputDropdownProps {
  dropdownValue: string
  inputValue: string
  isDisabled?: boolean
  label?: string
}

type Props = InputDropdownProps &
  Omit<InputProps, 'value'> &
  Partial<Omit<DropdownProps, 'value'>> &
  Pick<FloatProps, 'children'>

const InputDropdown: FC<Props> = ({
  placement,
  dropdownValue,
  inputValue,
  isMarginless,
  isPreventingCloseOnOutsideClick,
  isOpenByDefault,
  isDisabled,
  isResponsive,
  isAutoPlacement,
  children,
  label,
  name,
  ...rest
}) => {
  const renderLabel = () => (label ? <Label htmlFor={name}>{label}</Label> : null)

  return (
    <div>
      {renderLabel()}
      <div className={classNames(styles.root, !isMarginless && styles.margin)}>
        <Dropdown
          theme='secondary'
          placement={placement}
          value={dropdownValue}
          isPreventingCloseOnOutsideClick={isPreventingCloseOnOutsideClick}
          isOpenByDefault={isOpenByDefault}
          isDisabled={isDisabled}
          isResponsive={isResponsive}
          isAutoPlacement={isAutoPlacement}
          isMarginless
        >
          <DropdownContent>{children}</DropdownContent>
        </Dropdown>
        <Input {...rest} name={name} isMarginless className={styles.input} value={inputValue} />
      </div>
    </div>
  )
}

InputDropdown.defaultProps = {
  isMarginless: false,
  isDisabled: false,
  dropdownValue: '',
  inputValue: '',
  placement: 'bottom-end',
  isPreventingCloseOnOutsideClick: false,
  isResponsive: true,
  isAutoPlacement: false,
}

export { InputDropdown }
export type { Props as InputDropdownProps }
