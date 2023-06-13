import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import PropTypes from 'prop-types'
import styles from './Box.scss'

const Box = ({ icon, text, dangerouslySetInnerHTML }) => {
  const renderTextSpan = () =>
    dangerouslySetInnerHTML ? <span dangerouslySetInnerHTML={dangerouslySetInnerHTML}></span> : <span>{text}</span>

  return (
    <p className={styles.root}>
      <FontAwesomeIcon icon={icon} />
      {renderTextSpan()}
    </p>
  )
}

Box.propTypes = {
  icon: PropTypes.object,
  text: PropTypes.string,
  dangerouslySetInnerHTML: PropTypes.object,
}

export default Box
