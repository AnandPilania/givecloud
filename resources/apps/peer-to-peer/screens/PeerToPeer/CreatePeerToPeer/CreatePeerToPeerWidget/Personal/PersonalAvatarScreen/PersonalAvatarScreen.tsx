import type { FC } from 'react'
import { SCREENS } from '@/constants/screens'
import { faBat } from '@fortawesome/pro-regular-svg-icons'
import { HeroAvatar, WidgetContent, WidgetFooter, WidgetHeader, Text } from '@/components'
import { Column, RadioButton, AvatarTile, Columns, RadioGroup, triggerToast, Button } from '@/aerosol'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useParams } from '@/shared/hooks'
import { useCreatePeerToPeerCampaignMutation } from '@/screens/PeerToPeer/CreatePeerToPeer/CreatePeerToPeerWidget/useCreatePeerToPeerCampaignMutation'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { avatarMap, fallbackAvatar } from '@/screens/PeerToPeer/svgs'
import styles from './PersonalAvatarScreen.styles.scss'

interface Props {
  index: number
}

const PersonalAvatarScreen: FC<Props> = ({ index }) => {
  const { peerToPeerValue, setPeerToPeerState } = usePeerToPeerState()

  const {
    fundraisingExperience: {
      id: fundraising_form_id,
      logo_url,
      global_settings: { org_website },
    },
  } = useFundraisingExperienceState()
  const { activeIndex } = useCarouselContext()
  const { setAndReplaceParams } = useParams()

  const { mutate, isLoading } = useCreatePeerToPeerCampaignMutation({
    onSuccess: ({ id, share_links: shareLinks }) => {
      setPeerToPeerState({ ...peerToPeerValue, shareLinks, id })
      setAndReplaceParams(SCREENS.SCREEN, SCREENS.SUMMARY)
    },
    onError: () =>
      triggerToast({
        type: 'error',
        header: 'Something went wrong!',
        description: 'Try again later',
      }),
  })

  const handleClick = () =>
    mutate({
      fundraising_form_id,
      fundraiser_type: peerToPeerValue.fundraiserType,
      avatar_name: peerToPeerValue.avatarName,
      goal_amount: peerToPeerValue.personal.goalAmount,
      currency_code: peerToPeerValue.currencyCode,
    })

  const handleOnChange = (avatarName: string) =>
    setPeerToPeerState({
      ...peerToPeerValue,
      avatarName,
    })

  const renderAvatar = (avatar: string, index: number) => (
    <Column className={styles.column} columnWidth='small' key={index}>
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
        <Text isBold type='h2'>
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
        <Button isLoading={isLoading} onClick={handleClick} className='w-full' theme='custom'>
          Finish
        </Button>
        <PeerToPeerFooter isOnWidget />
      </WidgetFooter>
    </>
  )
}

export { PersonalAvatarScreen }
