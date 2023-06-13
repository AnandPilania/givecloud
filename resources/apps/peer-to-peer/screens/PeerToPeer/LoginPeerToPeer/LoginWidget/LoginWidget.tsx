import type { FC } from 'react'
import { CREATE_PATH } from '@/constants/paths'
import { useState } from 'react'
import Givecloud from 'givecloud'
import { useHistory } from 'react-router-dom'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight, faSignIn } from '@fortawesome/pro-regular-svg-icons'
import { PrivacyDrawer } from '@/screens/PeerToPeer/PrivacyDrawer'
import { FAQDrawer } from '@/screens/PeerToPeer/FAQDrawer'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import SocialLoginDrawer from '@/shared/components/SocialLoginDrawer/SocialLoginDrawer'
import { Button } from '@/aerosol'
import { HeroAvatar, Text, Widget, WidgetContent, WidgetFooter } from '@/components'
import { useSupporterState } from '@/screens/PeerToPeer/useSupporterState'
import styles from './LoginWidget.styles.scss'

const LoginWidget: FC = () => {
  const history = useHistory()
  const [showLoginDrawer, setShowLoginDrawer] = useState(false)
  const { setSupporter } = useSupporterState()

  const onAuthenticated = async () => {
    const { account } = await Givecloud.Account.get()
    setSupporter(account)
    history.push(CREATE_PATH)
  }

  return (
    <Widget>
      <WidgetContent className={styles.root}>
        <HeroAvatar preventAnimation icon={faSignIn} />
        <Text className={styles.text} isBold type='h2'>
          Start a Social Challenge
        </Text>
        <Text className={styles.text}>Lorem ipsum, dolor sit amet consectetur adipisicing elit.</Text>
      </WidgetContent>
      <WidgetFooter>
        <Button onClick={() => setShowLoginDrawer(true)} isFullWidth theme='custom'>
          Start a Challenge
          <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
        </Button>
        <PeerToPeerFooter isOnWidget />
      </WidgetFooter>
      <SocialLoginDrawer
        isOpen={showLoginDrawer}
        onClose={() => setShowLoginDrawer(false)}
        onAuthenticated={onAuthenticated}
      />
      <FAQDrawer />
      <PrivacyDrawer />
    </Widget>
  )
}

export { LoginWidget }
