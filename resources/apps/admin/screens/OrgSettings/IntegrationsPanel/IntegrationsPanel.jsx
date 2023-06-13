import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/free-solid-svg-icons'
import { Box, Button, Text } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './IntegrationsPanel.scss'

const IntegrationsPanel = () => {
  const { large } = useTailwindBreakpoints()

  return (
    <Box isMarginless className={styles.box}>
      <div>
        <Text type='h4' isBold>
          Integrations
        </Text>
        <Text isSecondaryColour> Turbocharge your Givecloud account by connecting third-party apps.</Text>
      </div>
      <div className={styles.buttonContainer}>
        <Button
          aria-label='manage givecloud integrations'
          isFullWidth={large.lessThan}
          href='/jpanel/settings/integrations'
          size='small'
          isOutlined
        >
          Manage <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
        </Button>
      </div>
    </Box>
  )
}

export { IntegrationsPanel }
