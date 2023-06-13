import type { FC } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import { Button, Column, Columns, Text, Badge } from '@/aerosol'
import styles from './PayPalStatus.styles.scss'

interface Props {
  isConnected: boolean
}

const PayPalStatus: FC<Props> = ({ isConnected }) => {
  const renderCTA = () =>
    isConnected ? (
      <Button href='payment/paypalexpress' isOutlined size='small'>
        Disconnect
      </Button>
    ) : (
      <Button href='payment/paypalexpress' isOutlined size='small'>
        Connect
        <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
      </Button>
    )

  const renderBadge = () => (isConnected ? <Badge theme='success'>Connected</Badge> : null)

  return (
    <Columns isResponsive={false} className={styles.root}>
      <Column className={styles.column}>
        <Text className={styles.text} isMarginless>
          PayPal
        </Text>
        {renderBadge()}
      </Column>
      <Column columnWidth='small'>{renderCTA()}</Column>
    </Columns>
  )
}

export { PayPalStatus }
