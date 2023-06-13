import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import ReactConfetti from 'react-confetti'
import AnimatedCheckmark from '@/components/AnimatedCheckmark/AnimatedCheckmark'
import Screen from '@/components/Screen/Screen'
import SocialLink from './components/SocialLink'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import useLocalization from '@/hooks/useLocalization'
import useSubstitution from '@/hooks/useSubstitution'
import configState from '@/atoms/config'
import contributionState from '@/atoms/contribution'
import confettiOptionsState from '@/atoms/confettiOptions'
import SocialChallenge from './components/SocialChallenge/SocialChallenge'
import styles from './ThankYou.scss'

const ThankYou = () => {
  const t = useLocalization('screens.thank_you')
  const substitute = useSubstitution('screens.thank_you')

  const confettiOptions = useRecoilValue(confettiOptionsState)
  const [showConfetti, setShowConfetti] = useState(true)

  const config = useRecoilValue(configState)
  const contribution = useRecoilValue(contributionState)
  const formatCurrency = useCurrencyFormatter({ currencyCode: contribution.currency.code })

  const params = {
    name: contribution.billing_address.first_name,
    amount: formatCurrency(contribution.total_price),
  }

  const description = substitute(
    'description_html',
    contribution.recurring_items ? config.thank_you_onscreen_monthly_message : config.thank_you_onscreen_message,
    params
  )

  return (
    <Screen className={styles.root}>
      {showConfetti && (
        <div className={styles.confetti}>
          <ReactConfetti {...confettiOptions} onConfettiComplete={() => setShowConfetti(false)} />
        </div>
      )}

      <AnimatedCheckmark />

      <h3>{t('heading', params)}</h3>
      <p dangerouslySetInnerHTML={description} />

      <h4>
        <span>{t('spread_the_word')}</span>
      </h4>

      <ul className={styles.socialLinks}>
        {Object.keys(contribution.share_links).map((platform) => (
          <SocialLink key={platform} platform={platform} href={contribution.share_links[platform]} />
        ))}
      </ul>

      {config.peer_to_peer.enabled && <SocialChallenge />}
    </Screen>
  )
}

export default ThankYou
