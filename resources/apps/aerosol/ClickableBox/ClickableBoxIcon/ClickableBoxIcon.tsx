import type { FC, HTMLProps } from 'react'
import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import styles from './ClickableBoxIcon.styles.scss'

type PlacementType = 'top' | 'center' | 'bottom' | 'static'

interface Props extends Pick<HTMLProps<HTMLDivElement>, 'className'> {
  placement?: PlacementType
  icon?: IconDefinition
}

const ClickableBoxIcon: FC<Props> = ({ placement = 'center', icon = faArrowRight, className, ...rest }) => (
  <div className={classNames(styles.root, 'icon-container', styles[placement], className)}>
    <FontAwesomeIcon {...rest} icon={icon} className={classNames(styles.icon, 'icon')} />
  </div>
)

ClickableBoxIcon.defaultProps = {
  placement: 'center',
  icon: faArrowRight,
}

export { ClickableBoxIcon }
