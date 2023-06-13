import type { FC, HTMLProps, ReactNode } from 'react'
import type { ThemeType } from '@/shared/constants/theme'
import { isValidElement, cloneElement, Children } from 'react'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinner } from '@fortawesome/pro-regular-svg-icons'
import { Switch } from '@headlessui/react'
import { PRIMARY } from '@/shared/constants/theme'
import { ToggleLabel } from './ToggleLabel'
import styles from './Toggle.styles.scss'

type LabelPosition = 'right' | 'left'

export interface ToggleProps {
  isEnabled: boolean
  setIsEnabled: (checked: boolean) => void
  theme?: ThemeType[number]
  name: string
  labelPosition?: LabelPosition
  children?: ReactNode
  isLoading?: boolean
  isDisabled?: boolean
}

type Props = Pick<HTMLProps<HTMLDivElement>, 'className'> & ToggleProps

const ToggleLabelType = (<ToggleLabel />).type

type ToggleLabelChild = Pick<ToggleProps, 'isEnabled' | 'labelPosition'>

const Toggle: FC<Props> = ({
  isEnabled,
  setIsEnabled,
  theme = PRIMARY,
  className,
  name,
  labelPosition = 'left',
  children,
  isLoading,
  isDisabled,
}) => {
  const renderLabel = () => {
    if (children) {
      return Children.map(children, (child) =>
        isValidElement<ToggleLabelChild>(child) && child.type === ToggleLabelType
          ? cloneElement(child, { isEnabled, labelPosition })
          : null
      )
    }
    return null
  }

  const renderButton = () => {
    if (isLoading) {
      return (
        <span
          aria-hidden='true'
          className={classnames(styles.button, isEnabled ? styles.enabledPostition : styles.disabledPosition)}
        >
          <FontAwesomeIcon size='xs' icon={faSpinner} spin className='font-bold text-blue-600' />
        </span>
      )
    }
    return (
      <span
        aria-hidden='true'
        className={classnames(styles.button, isEnabled ? styles.enabledPostition : styles.disabledPosition)}
      />
    )
  }

  return (
    <Switch.Group as='div' className={classnames(styles.root, styles[labelPosition + 'Label'], className)}>
      {renderLabel()}
      <Switch
        checked={isEnabled}
        onChange={setIsEnabled}
        className={classnames(styles.toggle, isEnabled ? styles[theme] : styles.off, isDisabled && styles.disabled)}
        disabled={isDisabled}
      >
        <span className='sr-only'>toggle for {name}</span>
        {renderButton()}
      </Switch>
    </Switch.Group>
  )
}

Toggle.defaultProps = {
  theme: PRIMARY,
  isEnabled: false,
  isDisabled: false,
  labelPosition: 'left',
}

export { Toggle }
