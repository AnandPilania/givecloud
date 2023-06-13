import { useRecoilValue } from 'recoil'
import { Link, useHistory } from 'react-router-dom'
import { faEnvelope } from '@fortawesome/pro-light-svg-icons'
import Givecloud from 'givecloud'
import Button from '@/components/Button/Button'
import HerospaceIcon from '@/components/HerospaceIcon/HerospaceIcon'
import Screen from '@/components/Screen/Screen'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import useLocalization from '@/hooks/useLocalization'
import useSubstitution from '@/hooks/useSubstitution'
import configState from '@/atoms/config'
import contributionState from '@/atoms/contribution'
import { THANK_YOU } from '@/constants/pathConstants'
import styles from './EmailOptIn.scss'

const EmailOptIn = () => {
  const t = useLocalization('screens.email_opt_in')
  const substitute = useSubstitution('screens.email_opt_in')

  const config = useRecoilValue(configState)
  const contribution = useRecoilValue(contributionState)
  const formatCurrency = useCurrencyFormatter({ currencyCode: contribution.currency.code })
  const history = useHistory()

  const handleOnClick = () => {
    Givecloud.Cart(contribution.id).updateOptin(true)
    history.push(THANK_YOU)
  }

  const description = substitute('description_html', config.email_optin_description, {
    amount: formatCurrency(contribution.total_price),
  })

  return (
    <Screen className={styles.root} showBackButton={false}>
      <HerospaceIcon icon={faEnvelope} />

      <h3>{t('heading')}</h3>
      <p dangerouslySetInnerHTML={description} />

      <div className={styles.options}>
        <Button onClick={handleOnClick}>{t('keep_me_up_to_date')}</Button>
      </div>

      <Link className={styles.noThanks} to={THANK_YOU}>
        {t('no_thanks')}
      </Link>
    </Screen>
  )
}

export default EmailOptIn
