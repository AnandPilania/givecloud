import type { FC } from 'react'
import { useState, useEffect } from 'react'
import { SCREENS } from '@/constants/screens'
import { Button, Column, Columns } from '@/aerosol'
import { Widget, WidgetFooter, WidgetHeader } from '@/components'
import { FAQDrawer } from '@/screens/PeerToPeer/FAQDrawer'
import { PrivacyDrawer } from '@/screens/PeerToPeer/PrivacyDrawer'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { JoinCodeDrawer } from './JoinCodeDrawer'
import { JoinShareDrawer } from './JoinShareDrawer'
import {
  SkeletonJoinTeamPeerToPeerWidgetContent,
  JoinTeamPeerToPeerWidgetContent,
} from './JoinTeamPeerToPeerWidgetContent'
import { useParams, useTailwindBreakpoints } from '@/shared/hooks'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import styles from './JoinTeamPeerToPeerWidget.styles.scss'

interface Props {
  isLoading: boolean
  isError: boolean
}

const JoinTeamPeerToPeerWidget: FC<Props> = ({ isError, isLoading }) => {
  const { extraSmall } = useTailwindBreakpoints()
  const { setAndReplaceParams, deleteAndReplaceParams } = useParams()
  const {
    fundraisingExperience: {
      logo_url,
      global_settings: { org_website },
    },
  } = useFundraisingExperienceState()
  const [isJoinCodeDrawerOpen, setIsJoinCodeDrawerOpen] = useState(false)
  const [isJoinShareDrawerOpen, setIsJoinShareDrawerOpen] = useState(false)

  useEffect(() => {
    if (!isJoinCodeDrawerOpen) deleteAndReplaceParams([SCREENS.DRAWER])

    return () => setIsJoinCodeDrawerOpen(false)
  }, [])

  const handleJoinTeamDrawer = () => {
    setAndReplaceParams(SCREENS.DRAWER, '0')
    setIsJoinCodeDrawerOpen(true)
  }

  const renderContent = () => {
    if (isLoading || isError) return <SkeletonJoinTeamPeerToPeerWidgetContent />
    return <JoinTeamPeerToPeerWidgetContent />
  }

  const renderSpacingColumn = () => (extraSmall.greaterThan ? <Column columnWidth='small' /> : null)

  return (
    <Widget>
      <WidgetHeader onCloseHref={org_website} className={styles.header}>
        <img src={logo_url} className={styles.logo} alt='' />
      </WidgetHeader>
      {renderContent()}
      <WidgetFooter className={styles.footer}>
        <Columns isMarginless isResponsive={false}>
          <Column className={styles.column}>
            <Button
              isLoading={isLoading || isError}
              isFullWidth
              isOutlined
              onClick={handleJoinTeamDrawer}
              theme='custom'
            >
              Join Team
            </Button>
          </Column>
          {renderSpacingColumn()}
          <Column className={styles.column}>
            <Button
              isLoading={isLoading || isError}
              isFullWidth
              onClick={() => setIsJoinShareDrawerOpen(true)}
              theme='custom'
            >
              Share
            </Button>
          </Column>
        </Columns>
        <PeerToPeerFooter isOnWidget />
      </WidgetFooter>
      <JoinCodeDrawer isOpen={isJoinCodeDrawerOpen} onClose={() => setIsJoinCodeDrawerOpen(false)} />
      <JoinShareDrawer isOpen={isJoinShareDrawerOpen} onClose={() => setIsJoinShareDrawerOpen(false)} />
      <FAQDrawer />
      <PrivacyDrawer />
    </Widget>
  )
}

export { JoinTeamPeerToPeerWidget }
