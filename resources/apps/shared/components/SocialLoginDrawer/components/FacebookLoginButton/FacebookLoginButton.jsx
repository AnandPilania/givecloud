import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faFacebook } from '@fortawesome/free-brands-svg-icons'
import styles from './FacebookLoginButton.scss'

const FacebookLoginButton = ({ onClick }) => {
  return (
    <button type='button' className={styles.root} onClick={() => onClick('facebook')}>
      <FontAwesomeIcon icon={faFacebook} className={styles.icon} />

      <span className={styles.text}>Continue with Facebook</span>
    </button>
  )
}

FacebookLoginButton.propTypes = {
  onClick: PropTypes.func.isRequired,
}

export default FacebookLoginButton
