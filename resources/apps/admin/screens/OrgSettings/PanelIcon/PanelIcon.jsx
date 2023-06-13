import classNames from 'classnames'
import styles from './PanelIcon.scss'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'

const PanelIcon = ({ ...rest }) => {
  return (
    <div className={classNames(styles.root, rest?.className)}>
      <FontAwesomeIcon {...rest} className={classNames(styles.icon, rest?.className)} />
    </div>
  )
}

export { PanelIcon }
