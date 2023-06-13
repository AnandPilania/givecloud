import type { FC } from 'react'
import { LOGIN_PATH } from '@/constants/paths'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight, faExclamationTriangle } from '@fortawesome/pro-regular-svg-icons'
import { PrivacyDrawer } from '@/screens/PeerToPeer/PrivacyDrawer'
import { FAQDrawer } from '@/screens/PeerToPeer/FAQDrawer'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { Button } from '@/aerosol'
import { HeroAvatar, Text, Widget, WidgetContent, WidgetFooter } from '@/components'
import styles from './FallbackWidget.styles.scss'

const FallbackWidget: FC = () => {
  return (
    <Widget>
      <WidgetContent className={styles.root}>
        <HeroAvatar preventAnimation icon={faExclamationTriangle} />
        <Text className={styles.text} isBold type='h2'>
          We could not find your team!
        </Text>
        <Text className={styles.text}>Please check that your link is correct and try again.</Text>
      </WidgetContent>
      <WidgetFooter>
        <Button to={LOGIN_PATH} className={styles.button} theme='custom'>
          Continue to login
          <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
        </Button>
        <PeerToPeerFooter isOnWidget />
      </WidgetFooter>
      <FAQDrawer />
      <PrivacyDrawer />
    </Widget>
  )
}

export { FallbackWidget }
