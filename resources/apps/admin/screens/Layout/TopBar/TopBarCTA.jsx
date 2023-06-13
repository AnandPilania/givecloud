import { useLocation } from 'react-router-dom'
import { useRecoilValue } from 'recoil'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationTriangle, faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import { Button } from '@/aerosol'
import { TopBarQuickStartGuideButton } from '@/screens/Layout/TopBar/TopBarQuickStartGuideButton'
import config from '@/atoms/config'

const TopBarCTA = () => {
  const { isGivecloudExpress, isTestMode, orgLegalNumber } = useRecoilValue(config)
  const isSetupComplete = !isTestMode && !!orgLegalNumber
  const isOnSettingsPage = !!useLocation()?.pathname.includes('/settings/general')
  const showQuickStartButton = isSetupComplete || isOnSettingsPage || !isGivecloudExpress

  return showQuickStartButton ? (
    <TopBarQuickStartGuideButton />
  ) : (
    <Button theme='error' href='/jpanel/settings/general'>
      <FontAwesomeIcon icon={faExclamationTriangle} className='mr-2' />
      Finish setup <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
    </Button>
  )
}

export { TopBarCTA }
