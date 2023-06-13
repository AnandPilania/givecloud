import { useCallback, useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinner } from '@fortawesome/pro-regular-svg-icons'
import { Drawer as AerosolDrawer } from '@/aerosol'
import { openNewWindow } from '@/shared/utilities'
import GoogleLoginButton from './components/GoogleLoginButton/GoogleLoginButton'
import FacebookLoginButton from './components/FacebookLoginButton/FacebookLoginButton'
import MicrosoftLoginButton from './components/MicrosoftLoginButton/MicrosoftLoginButton'
import styles from './SocialLoginDrawer.scss'

const SocialLoginDrawer = ({ Drawer = AerosolDrawer, isOpen, onClose, host = location.host, onAuthenticated }) => {
  const [newWindow, setNewWindow] = useState(null)

  const showContent = newWindow === null
  const showIndicator = !showContent

  const handleLoginButtonClick = (provider) => {
    const newWindow = openNewWindow({
      url: `https://${host}/account/social/transparent/${provider}`,
      name: 'socialChallengeLogin',
      onRelease: handleCloseButtonClick,
    })

    setNewWindow(newWindow)
  }

  const handleCloseButtonClick = useCallback(() => {
    newWindow && newWindow.close()
    onClose()
  }, [newWindow, onClose])

  useEffect(() => {
    const handleOnMessage = ({ data }) => {
      if (data.type !== 'social_login') {
        return
      }

      handleCloseButtonClick()

      if (data.payload?.successful) {
        onAuthenticated()
      }
    }

    window.addEventListener('message', handleOnMessage)

    return () => window.removeEventListener('message', handleOnMessage)
  }, [handleCloseButtonClick, onAuthenticated])

  const renderLoadingIndicator = () => (showIndicator ? <FontAwesomeIcon icon={faSpinner} spin size='2x' /> : null)

  const renderContent = () =>
    showContent ? (
      <>
        <p>Continue with...</p>

        <GoogleLoginButton onClick={handleLoginButtonClick} />
        <FacebookLoginButton onClick={handleLoginButtonClick} />
        <MicrosoftLoginButton onClick={handleLoginButtonClick} />
      </>
    ) : null

  return (
    <Drawer name='social login' isOpen={isOpen} onClose={handleCloseButtonClick}>
      <div className={classnames(styles.root, newWindow && styles.newWindow)}>
        {renderContent()}
        {renderLoadingIndicator()}
      </div>
    </Drawer>
  )
}

SocialLoginDrawer.propTypes = {
  Drawer: PropTypes.any,
  isOpen: PropTypes.bool.isRequired,
  onClose: PropTypes.func.isRequired,
  host: PropTypes.string,
  onAuthenticated: PropTypes.func.isRequired,
}

export default SocialLoginDrawer
