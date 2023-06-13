import { useState } from 'react'
import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import { Alert, Button, Column, Columns, Text, Badge } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { DisconnectDialog } from './DisconnectDialog'
import styles from './StripeStatus.scss'

const StripeStatus = ({ isConnected, connectUrl }) => {
  const { extraSmall } = useTailwindBreakpoints()
  const [isDisconnectDialogOpen, setIsDisconnectDialogOpen] = useState(false)

  if (!isConnected) {
    return (
      <Alert iconPosition='center' type='error' isMarginless size='lg'>
        <Columns isMarginless isResponsive={false} className={styles.columns}>
          <Column columnWidth='six' className={styles.column}>
            <Text isMarginless={extraSmall.greaterThan}>Credit Cards</Text>
            <Button isFullWidth={extraSmall.lessThan} size='small' theme='error' href={connectUrl}>
              Connect Stripe
              <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
            </Button>
          </Column>
        </Columns>
      </Alert>
    )
  }
  return (
    <Columns isResponsive={false} className='items-center'>
      <Column className={styles.column}>
        <Text className={styles.text} isMarginless>
          Credit Cards
        </Text>
        <Badge theme='success'>Connected</Badge>
      </Column>
      <Column columnWidth='small'>
        <Button onClick={() => setIsDisconnectDialogOpen(true)} isOutlined size='small'>
          Disconnect Stripe
        </Button>
      </Column>
      <DisconnectDialog isOpen={isDisconnectDialogOpen} onClose={() => setIsDisconnectDialogOpen(false)} />
    </Columns>
  )
}

StripeStatus.propTypes = {
  isConnected: PropTypes.bool,
  connectUrl: PropTypes.string,
}

StripeStatus.defaultProps = {
  isConnected: false,
}

export { StripeStatus }
