import PropTypes from 'prop-types'
import { Button, Column, Columns, Dialog, DialogHeader, Text, triggerToast } from '@/aerosol'
import { useDisconnectAcceptedDonationsMutation } from './useDisconnectAcceptedDonationsMutation'
import { useAcceptedDonationSettingsState } from '@/screens/OrgSettings/AcceptDonationsPanel/useAcceptedDonationsSettingsState'
import styles from './DisconnectDialog.scss'

const DisconnectDialog = ({ isOpen, onClose }) => {
  const { mutate, isLoading } = useDisconnectAcceptedDonationsMutation()
  const { acceptedDonationsValue, setAcceptedDonationsValue } = useAcceptedDonationSettingsState()

  const handleClick = () => {
    mutate(null, {
      onSuccess: () => {
        triggerToast({
          type: 'success',
          header: 'Stripe disconnected!',
          options: { autoClose: false },
        })
        setAcceptedDonationsValue({
          ...acceptedDonationsValue,
          stripe: {
            ...acceptedDonationsValue.stripe,
            isEnabled: false,
          },
        })
      },
      onError: () =>
        triggerToast({
          type: 'error',
          header: `Sorry, something went wrong!`,
          options: { autoClose: false },
        }),
    })
  }

  return (
    <Dialog isOpen={isOpen} onClose={onClose}>
      <DialogHeader theme='error' onClose={onClose}>
        <Text type='h3' isMarginless>
          Disconnect Stripe
        </Text>
      </DialogHeader>
      <Columns>
        <Column>
          <Text type='h5' isSecondaryColour>
            Are you sure you want to disconnect Stripe? You will be unable to receive any donations.
          </Text>
        </Column>
      </Columns>
      <Columns isResponsive={false} className={styles.buttonContainer}>
        <Column columnWidth='small'>
          <Button isFullWidth theme='error' isOutlined onClick={() => onClose(false)}>
            Cancel
          </Button>
        </Column>
        <Column columnWidth='small'>
          <Button isLoading={isLoading} onClick={handleClick} theme='error'>
            Disconnect
          </Button>
        </Column>
      </Columns>
    </Dialog>
  )
}

DisconnectDialog.propTypes = {
  isOpen: PropTypes.bool,
  onClose: PropTypes.func,
}

export { DisconnectDialog }
