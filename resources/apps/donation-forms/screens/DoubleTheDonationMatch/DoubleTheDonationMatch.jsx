import { useHistory } from 'react-router-dom'
import { useEffectOnce } from 'react-use'
import { useRecoilValue } from 'recoil'
import { faSparkles } from '@fortawesome/pro-light-svg-icons'
import ReactConfetti from 'react-confetti'
import Screen from '@/components/Screen/Screen'
import HerospaceIcon from '@/components/HerospaceIcon/HerospaceIcon'
import useLocalization from '@/hooks/useLocalization'
import companyState from '@/atoms/company'
import configState from '@/atoms/config'
import confettiOptionsState from '@/atoms/confettiOptions'
import { EMAIL_OPT_IN, THANK_YOU } from '@/constants/pathConstants'
import styles from './DoubleTheDonationMatch.scss'

const DoubleTheDonationMatch = () => {
  const t = useLocalization('screens.double_the_donation_match')

  const config = useRecoilValue(configState)
  const company = useRecoilValue(companyState)
  const confettiOptions = useRecoilValue(confettiOptionsState)
  const history = useHistory()

  const emailOptInPath = config.email_optin_enabled && EMAIL_OPT_IN

  useEffectOnce(() => setTimeout(() => history.push(emailOptInPath || THANK_YOU), 5000))

  return (
    <Screen className={styles.root} showBackButton={false}>
      <div className={styles.confetti}>
        <ReactConfetti {...confettiOptions} />
      </div>

      <HerospaceIcon icon={faSparkles} />

      <h3>{t('heading')}</h3>
      <p>{t('description')}</p>

      <div className={styles.company}>
        <div className={styles.details}>
          <div>{company.companyName}</div>
          {company.parentCompanyName && <small>{company.parentCompanyName}</small>}
        </div>
      </div>
    </Screen>
  )
}

export default DoubleTheDonationMatch
