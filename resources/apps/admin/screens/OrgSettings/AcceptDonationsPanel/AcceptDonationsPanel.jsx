import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/free-solid-svg-icons'
import { Box, Button, Column, Columns, Text, Badge, triggerToast } from '@/aerosol'
import { PayPalStatus } from './PayPalStatus'
import { StripeStatus } from './StripeStatus'
import { WalletPayStatus } from './WalletPayStatus'
import { MultiCurrencyStatus } from './MultiCurrencyStatus'
import { useAcceptedDonationSettingsState } from './useAcceptedDonationsSettingsState'
import { useUpdateAcceptedDonationsMutation } from './useUpdateAcceptedDonationsMutation'
import config from '@/atoms/config'
import styles from './AcceptDonationsPanel.scss'

const AcceptDonationsPanel = () => {
  const { isGivecloudExpress } = useRecoilValue(config)
  const [updatedField, setUpdatedField] = useState('')
  const { acceptedDonationsValue, setAcceptedDonationsValue } = useAcceptedDonationSettingsState()
  const { stripe, paypal } = acceptedDonationsValue ?? {}
  const isStripeConnected = !!stripe?.isEnabled
  const { mutate, isLoading } = useUpdateAcceptedDonationsMutation()
  const isMutationLoading = (field) => field === updatedField && isLoading

  const toggleStripeSettings = (field) => {
    setUpdatedField(field)
    mutate(
      {
        [field]: !stripe[field],
      },
      {
        onSuccess: ({ data: { data: response } }) => setAcceptedDonationsValue(response),
        onError: () =>
          triggerToast({
            type: 'error',
            header: `Sorry, something went wrong!`,
            options: { autoClose: false },
          }),
      }
    )
  }

  const renderOptionalDonationMethods = () => {
    if (isStripeConnected) {
      return (
        <>
          <WalletPayStatus
            isLoading={isMutationLoading('isWalletPayAllowed')}
            isEnabled={!!stripe?.isWalletPayAllowed}
            setIsEnabled={() => toggleStripeSettings('isWalletPayAllowed')}
          />
          <PayPalStatus isConnected={paypal?.isEnabled} />
          <MultiCurrencyStatus
            isLoading={isMutationLoading('isMulticurrencySupported')}
            isEnabled={!!stripe?.isMulticurrencySupported}
            setIsEnabled={() => toggleStripeSettings('isMulticurrencySupported')}
          />
        </>
      )
    }
    return null
  }

  const renderBadge = () =>
    !isStripeConnected && isGivecloudExpress ? (
      <Badge theme='error' className='mb-2'>
        <Text isMarginless type='footnote' className='uppercase'>
          required
        </Text>
      </Badge>
    ) : null

  const renderContent = () =>
    isGivecloudExpress ? (
      <Column columnWidth='four'>
        <StripeStatus connectUrl={stripe?.connectUrl} isConnected={isStripeConnected} />
        {renderOptionalDonationMethods()}
      </Column>
    ) : (
      <Column columnWidth='four' className='lg:items-end'>
        <Button href='/jpanel/settings/payment' size='small' className='mb-4 text-center whitespace-normal' isOutlined>
          Manage Payment Gateways <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
        </Button>
        <Button href='/jpanel/settings/payments' size='small' className='text-center whitespace-normal' isOutlined>
          Manage Payment Preferences <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
        </Button>
      </Column>
    )

  return (
    <Box>
      <Columns>
        <Column>
          <div className={styles.root}>
            <Text type='h4' isBold className={styles.text}>
              Accept Donations
            </Text>
            {renderBadge()}
          </div>
          <Text isMarginless isSecondaryColour>
            Connect and manage the payment gateways that will process your online donations.
          </Text>
        </Column>
        {renderContent()}
      </Columns>
    </Box>
  )
}
export { AcceptDonationsPanel }
