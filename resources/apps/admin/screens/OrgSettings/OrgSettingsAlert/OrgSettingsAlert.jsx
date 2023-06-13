import { useRecoilValue } from 'recoil'
import { Alert, Text, Columns, Column } from '@/aerosol'
import { useAcceptedDonationSettingsState } from '@/screens/OrgSettings/AcceptDonationsPanel/useAcceptedDonationsSettingsState'
import { useOrgSettingsState } from '@/screens/OrgSettings/OrgPanel/useOrgSettingsState'
import config from '@/atoms/config'
import styles from './OrgSettingsAlert.scss'

const OrgSettingsAlert = () => {
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
            You can finish the setup by:
          </Text>
          <ul className={styles.list}>
            <li>
              <Text type='h5'>Connecting your Stripe account to receive donations</Text>
            </li>
            <li>
              <Text isMarginless type='h5'>
                Adding your organization's charity number
              </Text>
            </li>
          </ul>
        </div>
      )

    if (!isOrgCharityNumberValid && isStripeConnected)
      return (
        <Text isBold type='h5' isMarginless>
          Finish the setup by adding your organization's charity number.
        </Text>
      )

    if (!isStripeConnected && isOrgCharityNumberValid)
      return (
        <Text isBold type='h5' isMarginless>
          Finish the setup by connecting a Stripe account.
        </Text>
      )
  }

  return (
    <Columns isMarginless>
      <Column isPaddingless columnWidth='six'>
        <Alert type='error'>
          <Columns isMarginless>
            <Column columnWidth='six'>{alertMessage()}</Column>
          </Columns>
        </Alert>
      </Column>
    </Columns>
  )
}

export { OrgSettingsAlert }
