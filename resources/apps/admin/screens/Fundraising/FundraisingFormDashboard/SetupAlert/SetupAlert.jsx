import { useRecoilValue } from 'recoil'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import { Alert, Button, Text, Columns, Column } from '@/aerosol'
import { useAcceptedDonationSettingsState } from '@/screens/OrgSettings/AcceptDonationsPanel/useAcceptedDonationsSettingsState'
import { useOrgSettingsState } from '@/screens/OrgSettings/OrgPanel/useOrgSettingsState'
import config from '@/atoms/config'
import styles from './SetupAlert.scss'

const SetupAlert = () => {
  const { isGivecloudExpress } = useRecoilValue(config)
  const { acceptedDonationsValue } = useAcceptedDonationSettingsState()
  const { orgValue } = useOrgSettingsState()
  const isOrgCharityNumberValid = !!orgValue.orgLegalNumber
  const isStripeConnected = !!acceptedDonationsValue?.stripe?.isEnabled
  const isSetupComplete = isStripeConnected && isOrgCharityNumberValid

  if (isSetupComplete || !isGivecloudExpress) return null

  const alertMessage = () => {
    if (!isStripeConnected && !isOrgCharityNumberValid)
      return (
        <div>
          <Text isBold type='h4'>
            You need to do the following, before you can start fundraising:
          </Text>
          <ul className='pl-6 m-0'>
            <li>
              <Text type='h5'>Connect your Stripe account to receive donations</Text>
            </li>
            <li>
              <Text type='h5'>Add your organization's charity number</Text>
            </li>
          </ul>
        </div>
      )

    if (!isOrgCharityNumberValid && isStripeConnected)
      return (
        <Text isBold type='h5' isMarginless>
          We need your organization's charity number, before you can start fundraising.
        </Text>
      )

    if (!isStripeConnected && isOrgCharityNumberValid)
      return (
        <Text isBold type='h5' isMarginless>
          You need to connect a Stripe account, before you can start fundraising.
        </Text>
      )
  }

  return (
    <Columns isMarginless>
      <Column columnWidth='six'>
        <Alert type='error' isMarginless>
          <Columns isMarginless className={styles.columns}>
            <Column columnWidth='four'>{alertMessage()}</Column>
            <Column columnWidth='one' className={styles.column}>
              <Button theme='error' href='/jpanel/settings/general'>
                Finish setup
                <FontAwesomeIcon icon={faArrowRight} className={styles.icon} />
              </Button>
            </Column>
          </Columns>
        </Alert>
      </Column>
    </Columns>
  )
}

export { SetupAlert }
