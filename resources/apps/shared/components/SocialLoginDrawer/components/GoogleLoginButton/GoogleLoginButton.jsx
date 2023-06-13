import PropTypes from 'prop-types'
import GoogleLogo from './GoogleLogo.svg?react'
import styles from './GoogleLoginButton.scss'

const GoogleLoginButton = ({ onClick }) => {
  return (
    <button type='button' className={styles.root} onClick={() => onClick('google')}>
      <GoogleLogo />

      <span className={styles.text}>Continue with Google</span>
    </button>
  )
}

GoogleLoginButton.propTypes = {
  onClick: PropTypes.func.isRequired,
}

export default GoogleLoginButton
