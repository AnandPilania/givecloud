import type { FC } from 'react'
import { SCREENS } from '@/constants/screens'
import { CREATE_PERSONAL_PATH, CREATE_TEAM_PATH } from '@/constants/paths'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faUser, faUsers, faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import { Column, Columns, Button } from '@/aerosol'
import { Card, WidgetContent, WidgetFooter, Text } from '@/components'
import { SlideAnimation } from '@/shared/components/SlideAnimation'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { useParams, useTailwindBreakpoints } from '@/shared/hooks'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import styles from './SelectionScreen.styles.scss'
import { useSupporterState } from '@/screens/PeerToPeer/useSupporterState'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'

const paths = {
  team: CREATE_TEAM_PATH,
  personal: CREATE_PERSONAL_PATH,
} as const

type FundraiserType = keyof typeof paths

const SelectionScreen: FC = () => {
  const {
    fundraisingExperience: {
      global_settings: { org_legal_name },
    },
  } = useFundraisingExperienceState()
  const { large } = useTailwindBreakpoints()
  const { peerToPeerValue, setPeerToPeerState } = usePeerToPeerState()
  const { setAndReplaceParams } = useParams()
  const { supporter } = useSupporterState()

  const handleClick = (fundraiserType: FundraiserType) => {
    const hasSupporterFirstName = !!supporter.first_name.length
    const firstScreen = hasSupporterFirstName ? SCREENS.GOAL : SCREENS.NAME

    setPeerToPeerState({
      ...peerToPeerValue,
      avatarName: fundraiserType === 'personal' ? 'custom' : 'nature',
      fundraiserType,
    })
    setAndReplaceParams(
      SCREENS.SCREEN,
      fundraiserType === SCREENS.PERSONAL ? firstScreen : SCREENS.NAME,
      paths[fundraiserType]
    )
  }

  const renderFaqPrivacy = () =>
    large.lessThan ? (
      <WidgetFooter>
        <PeerToPeerFooter isOnWidget />
      </WidgetFooter>
    ) : null

  return (
    <>
      <WidgetContent className={styles.root}>
        <SlideAnimation slideInFrom='right'>
          <Card>
            <Columns isResponsive={false} isStackingOnMobile={false} isMarginless>
              <Column>
                <Text type='h4' theme='custom'>
                  Personal Fundraiser
                </Text>
                <Text type='footnote'>
                  Appeal to your friends and family to raise awareness and support for {org_legal_name}.
                </Text>
              </Column>
              <Column columnWidth='small'>
                <FontAwesomeIcon icon={faUser} className={styles.icon} />
              </Column>
            </Columns>
            <Column columnWidth='six'>
              <Button onClick={() => handleClick(SCREENS.PERSONAL)} theme='custom'>
                Personal Fundraiser
                <FontAwesomeIcon className='ml-2' icon={faArrowRight} />
              </Button>
            </Column>
          </Card>
          <Card isMarginless>
            <Columns isResponsive={false} isStackingOnMobile={false} isMarginless>
              <Column>
                <Text type='h4' theme='custom'>
                  Team Fundraiser
                </Text>
                <Text type='footnote'>
                  Team-up with friends, family and co-workers to raise money towards a collective goal.
                </Text>
              </Column>
              <Column columnWidth='small'>
                <FontAwesomeIcon className={styles.icon} icon={faUsers} />
              </Column>
            </Columns>
            <Column columnWidth='six'>
              <Button onClick={() => handleClick(SCREENS.TEAM)} theme='custom'>
                Team Fundraiser
                <FontAwesomeIcon className='ml-2' icon={faArrowRight} />
              </Button>
            </Column>
          </Card>
        </SlideAnimation>
      </WidgetContent>
      {renderFaqPrivacy()}
    </>
  )
}

export { SelectionScreen }
