import { useState } from 'react'
import PropTypes from 'prop-types'
import { useRecoilValue } from 'recoil'
import Givecloud from 'givecloud'
import Button from '@/components/Button/Button'
import Drawer from '@/components/Drawer/Drawer'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import configState from '@/atoms/config'
import SocialLoginDrawer from '@/shared/components/SocialLoginDrawer/SocialLoginDrawer'
import styles from './SocialChallenge.scss'

const AerosolCompatibleDrawer = ({ children, isOpen, onClose }) => {
  return (
    <Drawer open={isOpen} onClose={onClose}>
      {children}
    </Drawer>
  )
}

AerosolCompatibleDrawer.propTypes = {
  children: PropTypes.node,
  isOpen: PropTypes.bool.isRequired,
  onClose: PropTypes.func.isRequired,
}

const SocialChallenge = () => {
  const config = useRecoilValue(configState)
  const [showLoginDrawer, setShowLoginDrawer] = useState(false)

  const onAuthenticated = () => {
    window.top.location.href = config.peer_to_peer.redirect_to
  }

  return (
    <div className={styles.root}>
      <Button onClick={() => setShowLoginDrawer(true)}>
        Start a Fundraiser &nbsp;
        <FontAwesomeIcon icon={faArrowRight} />
      </Button>

      <SocialLoginDrawer
        Drawer={AerosolCompatibleDrawer}
        isOpen={showLoginDrawer}
        onClose={() => setShowLoginDrawer(false)}
        host={Givecloud.config.host}
        onAuthenticated={onAuthenticated}
      />
    </div>
  )
}

export default SocialChallenge
