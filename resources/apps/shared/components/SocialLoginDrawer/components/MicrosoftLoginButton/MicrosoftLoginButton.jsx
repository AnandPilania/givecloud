import PropTypes from 'prop-types'
import MicrosoftLogo from './MicrosoftLogo.svg?react'
import styles from './MicrosoftLoginButton.scss'

const MicrosoftLoginButton = ({ onClick }) => {
  return (
    <button type='button' className={styles.root} onClick={() => onClick('microsoft')}>
      <MicrosoftLogo />

      <span className={styles.text}>Continue with Microsoft</span>
    </button>
  )
}

MicrosoftLoginButton.propTypes = {
  onClick: PropTypes.func.isRequired,
}

export default MicrosoftLoginButton
