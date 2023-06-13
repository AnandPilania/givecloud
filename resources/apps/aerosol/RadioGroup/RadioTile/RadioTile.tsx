import type { FC, HTMLProps, ReactNode } from 'react'
import type { RadioButtonChild } from '@/aerosol/RadioGroup/RadioButton'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheckCircle } from '@fortawesome/pro-regular-svg-icons'
import { useRadioGroupContext } from '@/aerosol/RadioGroup/RadioGroupContext'
import styles from './RadioTile.styles.scss'

type AdditionalProps = Pick<HTMLProps<HTMLDivElement>, 'className'> & RadioButtonChild

interface Props extends AdditionalProps {
  children: ReactNode
  disabled?: boolean
}

const RadioTile: FC<Props> = ({ isChecked, children, disabled, className }) => {
  const { isDisabled } = useRadioGroupContext()

  return (
    <div
      className={classNames(
        styles.root,
        isChecked ? styles.checked : styles.default,
        (isDisabled || disabled) && styles.disabled,
        className
      )}
    >
      <div className={styles.children}>{children}</div>
      <FontAwesomeIcon
        size='lg'
        className={classNames(styles.icon, isDisabled && styles.disabled, !isChecked && 'invisible')}
        icon={faCheckCircle}
        aria-hidden='true'
      />
    </div>
  )
}

export { RadioTile }
