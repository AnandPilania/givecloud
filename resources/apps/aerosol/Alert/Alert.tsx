import type { HTMLProps, ReactNode, FC } from 'react'
import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import type { FontAwesomeIconProps } from '@fortawesome/react-fontawesome'
import type { ThemeType } from '@/shared/constants/theme'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { SUCCESS, INFO } from '@/shared/constants/theme'
import {
  faExclamationCircle,
  faCircleCheck,
  faLightbulbOn,
  faExclamationTriangle,
} from '@fortawesome/pro-regular-svg-icons'
import { useTailwindBreakpoints } from '@/shared/hooks/useTailwindBreakpoints'
import classnames from 'classnames'
import styles from './Alert.styles.scss'

type IconPosition = 'top' | 'center' | 'bottom'
type AdditionalProps = Omit<FontAwesomeIconProps, 'ref' | 'icon'> & Pick<HTMLProps<HTMLDivElement>, 'className'>

interface Props extends AdditionalProps {
  iconPosition?: IconPosition
  icon?: IconDefinition
  type?: ThemeType
  isIconVisible?: boolean
  isOutlined?: boolean
  isMarginless?: boolean
  children: ReactNode
}

const icons = {
  info: faLightbulbOn,
  warning: faExclamationTriangle,
  success: faCircleCheck,
  error: faExclamationCircle,
}

const Alert: FC<Props> = ({
  children,
  type = SUCCESS,
  isIconVisible = true,
  isOutlined,
  isMarginless,
  icon,
  iconPosition = 'top',
  ...rest
}) => {
  const { className, ...remainder } = rest
  const { extraSmall } = useTailwindBreakpoints()
  const ariaLive = type === INFO ? 'polite' : 'assertive'

  const css = classnames(
    styles.root,
    styles[type],
    !isMarginless && styles.margin,
    isOutlined && styles.outlined,
    className
  )

  const renderIcon = () =>
    isIconVisible && extraSmall.greaterThan ? (
      <div className={classnames(styles.iconContainer, styles[iconPosition])}>
        <FontAwesomeIcon
          className={classnames(styles.icon, styles[type])}
          aria-hidden='true'
          icon={icon ?? icons[type]}
          size='lg'
          {...remainder}
        />
      </div>
    ) : null

  return (
    <div aria-live={ariaLive} role='alert' className={css}>
      {renderIcon()}
      <div className={classnames(styles.mainContent)}>{children}</div>
    </div>
  )
}

Alert.defaultProps = {
  type: SUCCESS,
  isIconVisible: true,
  isOutlined: false,
  iconPosition: 'top',
}

export { Alert }
