import type { FC } from 'react'
import { SCREENS } from '@/constants/screens'
import { faBat } from '@fortawesome/pro-regular-svg-icons'
import { HeroAvatar, WidgetContent, WidgetFooter, WidgetHeader } from '@/components'
import { AvatarTile, Button, Column, Columns, RadioButton, RadioGroup, Text, triggerToast } from '@/aerosol'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useParams } from '@/shared/hooks'
import { useJoinTeamMutation } from '@/screens/PeerToPeer/JoinPeerToPeer/JoinPeerToPeerWidget/useJoinTeamMutation'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { avatarMap, fallbackAvatar } from '@/screens/PeerToPeer/svgs'
import styles from './JoinAvatarScreen.styles.scss'

interface Props {
  index: number
}

const JoinAvatarScreen: FC<Props> = ({ index }) => {
  const { peerToPeerValue, team, setPeerToPeerState } = usePeerToPeerState()

  const {
    fundraisingExperience: {
      logo_url,
      global_settings: { org_website },
    },
  } = useFundraisingExperienceState()
  const { activeIndex } = useCarouselContext()
  const { id, setAndReplaceParams } = useParams()

  const { mutate, isLoading } = useJoinTeamMutation({
    onSuccess: ({ id, share_links }) => {
      setPeerToPeerState({ ...peerToPeerValue, shareLinks: share_links, id })
      setAndReplaceParams(SCREENS.SCREEN, SCREENS.SUMMARY)
    },
    onError: () =>
      triggerToast({ type: 'error', header: 'There was an error adding you to the team. Please try again later.' }),
  })

  const handleOnChange = (avatarName: string) => setPeerToPeerState({ ...peerToPeerValue, avatarName })

  const handleClick = () => {
    if (id) {
      mutate({
        id,
        campaign: {
          avatar_name: peerToPeerValue.avatarName,
          goal_amount: team.teamMemberGoalAmount,
          team_goal_amount: team.goalAmount,
          team_join_code: team.joinCode,
        },
      })
    }
  }

  const renderAvatar = (avatar: string, index: number) => (
    <Column columnWidth='small' key={index} className={styles.column}>
      <RadioButton isMarginless id={avatar} value={avatar}>
        <AvatarTile>{avatarMap[avatar]}</AvatarTile>
      </RadioButton>
    </Column>
  )

  const renderAvatars = () => Object.keys(avatarMap).map(renderAvatar)

  const renderAvatarContent = () =>
    peerToPeerValue.socialAvatar ? (
      <RadioButton isMarginless value='custom' id='custom'>
        <AvatarTile>
          <img src={peerToPeerValue.socialAvatar} alt='your social media avatar' className={styles.imgAvatar} />
        </AvatarTile>
      </RadioButton>
    ) : peerToPeerValue.supporterInitials ? (
      <RadioButton isMarginless value='custom' id='custom'>
        <AvatarTile>
          <div className={styles.initialsAvatar} aria-label='avatar of your initials'>
            {peerToPeerValue.supporterInitials}
          </div>
        </AvatarTile>
      </RadioButton>
    ) : (
      <RadioButton isMarginless value={fallbackAvatar.name} id={fallbackAvatar.name}>
        <AvatarTile>{fallbackAvatar.img}</AvatarTile>
      </RadioButton>
    )

  const renderCustomAvatar = () => (
    <Column columnWidth='small' key={index} className={styles.column}>
      {renderAvatarContent()}
    </Column>
  )

  return (
    <>
      <WidgetHeader indexToNavigate={SCREENS.GOAL} onCloseHref={org_website}>
        <img src={logo_url} className={styles.logo} alt='' />
      </WidgetHeader>
      <WidgetContent className={styles.root}>
        <HeroAvatar icon={faBat} initAnimationOn={index === activeIndex} />
        <Text isBold className={styles.text} type='h2'>
          Choose Your Avatar
        </Text>
        <RadioGroup
          showInput={false}
          isLabelVisible={false}
          label='avatars'
          name='avatars'
          checkedValue={peerToPeerValue.avatarName}
          onChange={handleOnChange}
        >
          <Columns isResponsive={false} isStackingOnMobile={false} isMarginless isWrapping className={styles.columns}>
            {renderCustomAvatar()}
            {renderAvatars()}
          </Columns>
        </RadioGroup>
      </WidgetContent>
      <WidgetFooter>
        <Button isLoading={isLoading} onClick={handleClick} isFullWidth theme='custom'>
          Finish
        </Button>
        <PeerToPeerFooter isOnWidget />
      </WidgetFooter>
    </>
  )
}

export { JoinAvatarScreen }
