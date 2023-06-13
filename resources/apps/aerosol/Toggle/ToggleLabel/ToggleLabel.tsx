import type { FC, HTMLProps } from 'react'
import type { ToggleProps } from '@/aerosol/Toggle'
import classNames from 'classnames'
import { Switch } from '@headlessui/react'
import styles from './ToggleLabel.styles.scss'

type Props = HTMLProps<HTMLLabelElement> & Partial<Pick<ToggleProps, 'children' | 'isEnabled' | 'labelPosition'>>

const ToggleLabel: FC<Props> = ({ children, isEnabled, labelPosition = 'left' }) => (
  <Switch.Label
    className={classNames(styles.root, styles[labelPosition], isEnabled ? styles.enabledText : styles.disabledText)}
  >
    {children}
  </Switch.Label>
)

export { ToggleLabel }
