import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/free-solid-svg-icons'
import { Box, Button, Text, Columns, Column } from '@/aerosol'
import styles from './GivecloudExpressPlanPanel.scss'

const GivecloudExpressPlanPanel = () => {
  return (
    <Box isMarginless isFullHeight className={styles.root}>
      <Columns>
        <Column>
          <Text type='h4' isBold>
            Givecloud Plan
          </Text>
          <Text isSecondaryColour>Manage your Givecloud Subscription, payment method and platform fee statements.</Text>
        </Column>
        <Column>
          <Text>Your Plan</Text>
          <Text isSecondaryColour>Givecloud Express - $0/mon + 0%</Text>
          <Text>Payment Method</Text>
          <Text isSecondaryColour isMarginless>
            None Required
          </Text>
          <Text isSecondaryColour isMarginless>
            Billing through Stripe when donor covers the fee.
          </Text>
        </Column>
      </Columns>
      <Columns isMarginless className='justify-end'>
        <Column isPaddingless columnWidth='small'>
          <Button
            aria-label='upgrade your givecloud subscription'
            href='https://calendly.com/givecloud-sales/givecloud-upgrade-call'
            size='small'
            isOutlined
          >
            Upgrade <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
          </Button>
        </Column>
      </Columns>
    </Box>
  )
}

export { GivecloudExpressPlanPanel }
