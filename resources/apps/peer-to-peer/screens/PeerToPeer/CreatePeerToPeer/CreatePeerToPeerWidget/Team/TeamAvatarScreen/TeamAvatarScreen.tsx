import type { FC } from 'react'
import { SCREENS } from '@/constants/screens'
import { faBat } from '@fortawesome/pro-regular-svg-icons'
import { HeroAvatar, WidgetContent, WidgetFooter, WidgetHeader } from '@/components'
import { AvatarTile, Button, Column, Columns, RadioButton, RadioGroup, Text, triggerToast } from '@/aerosol'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { useCreatePeerToPeerCampaignMutation } from '@/screens/PeerToPeer/CreatePeerToPeer/CreatePeerToPeerWidget/useCreatePeerToPeerCampaignMutation'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { useParams } from '@/shared/hooks'
import { avatarMapWithFallback } from '@/screens/PeerToPeer/svgs'
import styles from './TeamAvatarScreen.styles.scss'

interface Props {
  index: number
}

const TeamAvatarScreen: FC<Props> = ({ index }) => {
  const { setAndReplaceParams } = useParams()
  const { peerToPeerValue, team, setPeerToPeerState } = usePeerToPeerState()
  const {
    fundraisingExperience: {
      id: fundraising_form_id,
      logo_url,
      global_settings: { org_website },
    },
  } = useFundraisingExperienceState()
  const { activeIndex } = useCarouselContext()

  const { mutate, isLoading } = useCreatePeerToPeerCampaignMutation({
    onSuccess: ({ share_links, team_join_code: joinCode, id: campaignId, team_join_shortlink_url: shortUrl }) => {
      setPeerToPeerState({
        ...peerToPeerValue,
        shareLinks: share_links,
        team: {
          ...team,
          joinCode,
          campaignId,
          shortUrl,
        },
      })
      setAndReplaceParams(SCREENS.SCREEN, SCREENS.SUMMARY)
    },
    onError: () =>
      triggerToast({
        type: 'error',
        header: 'Something went wrong!',
        description: 'Try again later',
      }),
  })

  const handleClick = () => {
    mutate({
      fundraising_form_id,
      fundraiser_type: peerToPeerValue.fundraiserType,
      avatar_name: peerToPeerValue.avatarName,
      team_name: team.name,
      goal_amount: team.goalAmount,
      currency_code: peerToPeerValue.currencyCode,
    })
  }

  const handleOnChange = (avatarName: string) => setPeerToPeerState({ ...peerToPeerValue, avatarName })

  const renderAvatar = (avatar: string, index: number) => (
    <Column columnWidth='small' key={index} className={styles.column}>
      <RadioButton isMarginless id={avatar} value={avatar}>
        <AvatarTile>{avatarMapWithFallback[avatar]}</AvatarTile>
      </RadioButton>
    </Column>
  )

  const renderAvatars = () => Object.keys(avatarMapWithFallback).map(renderAvatar)

  return (
    <>
      <WidgetHeader indexToNavigate={SCREENS.GOAL} onCloseHref={org_website}>
        <img src={logo_url} className={styles.logo} alt='' />
      </WidgetHeader>
      <WidgetContent className={styles.root}>
        <HeroAvatar icon={faBat} initAnimationOn={index === activeIndex} />
        <Text isBold className={styles.text} type='h2'>
          Choose Your Team's Avatar
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
            {renderAvatars()}
          </Columns>
        </RadioGroup>
      </WidgetContent>
      <WidgetFooter>
        <Button isLoading={isLoading} onClick={handleClick} className='w-full' theme='custom'>
          Finish
        </Button>
        <PeerToPeerFooter isOnWidget />
      </WidgetFooter>
    </>
  )
}

export { TeamAvatarScreen }
