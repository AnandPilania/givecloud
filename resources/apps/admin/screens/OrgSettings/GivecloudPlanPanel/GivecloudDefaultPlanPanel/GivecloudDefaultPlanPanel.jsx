import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/free-solid-svg-icons'
import { Box, Button, Text } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './GivecloudDefaultPlanPanel.scss'

const GivecloudDefaultPlanPanel = () => {
  const { large } = useTailwindBreakpoints()
  return (
    <Box isMarginless isFullHeight className={styles.root}>
      <div>
        <Text type='h4' isBold>
          Givecloud Plan
        </Text>
        <Text isSecondaryColour>Manage your Givecloud plan and billing.</Text>
      </div>
      <div className={styles.buttonContainer}>
        <Button
          aria-label='manage givecloud subscriptions'
          isFullWidth={large.lessThan}
          href='/jpanel/settings/billing'
          size='small'
          isOutlined
        >
          Manage <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
        </Button>
      </div>
    </Box>
  )
}

export { GivecloudDefaultPlanPanel }
