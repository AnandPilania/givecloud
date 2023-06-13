import type { FC } from 'react'
import { useState } from 'react'
import { Button, Column, Columns, Thermometer } from '@/aerosol'
import { Confetti, HeroAvatar, Text, WidgetContent, WidgetFooter, WidgetHeader } from '@/components'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { ShareDrawer } from './ShareDrawer'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { avatarMapWithFallback } from '@/screens/PeerToPeer/svgs'
import { formatMoney } from '@/shared/utilities'
import styles from './JoinSummaryScreen.styles.scss'

interface Props {
  index: number
}

const JoinSummaryScreen: FC<Props> = ({ index }) => {
  const { extraSmall } = useTailwindBreakpoints()
  const { peerToPeerValue } = usePeerToPeerState()
  const {
    fundraisingExperience: {
      logo_url,
      primary_colour,
      global_settings: { org_website },
    },
  } = useFundraisingExperienceState()
  const { activeIndex } = useCarouselContext()
  const [isShareDrawerOpen, setIsShareDrawerOpen] = useState(false)
  const isInitAnimationOn = index === activeIndex

  const link = `${window.location.origin}/fundraising/p2p/donate/${peerToPeerValue.id}`

  const renderConfetti = () => (isInitAnimationOn ? <Confetti options={{ colors: [primary_colour] }} /> : null)

  const renderCustomAvatar = () =>
    peerToPeerValue.socialAvatar ? (
      <HeroAvatar src={peerToPeerValue.socialAvatar} initAnimationOn={isInitAnimationOn} theme='primary' />
    ) : (
      <HeroAvatar initAnimationOn={isInitAnimationOn} initials={peerToPeerValue.supporterInitials} />
    )

  const renderAvatar = () =>
    peerToPeerValue.avatarName === 'custom' ? (
      renderCustomAvatar()
    ) : (
      <HeroAvatar initAnimationOn={isInitAnimationOn} theme='primary'>
        {avatarMapWithFallback[peerToPeerValue.avatarName]}
      </HeroAvatar>
    )

  const renderSpacingColumn = () => (extraSmall.greaterThan ? <Column columnWidth='small' /> : null)

  return (
    <>
      {renderConfetti()}
      <WidgetHeader onCloseHref={org_website}>
        <img src={logo_url} className={styles.logo} alt='' />
      </WidgetHeader>
      <WidgetContent className={styles.root}>
        {renderAvatar()}
        <Text isBold type='h2' className={styles.text}>
          Your challenge is ready to fundraise!
        </Text>
        <div className={styles.thermometerContainer}>
          <Text isBold isMarginless>
            {formatMoney({ amount: 0, showZero: true, digits: 0, currency: peerToPeerValue.currencyCode })}
          </Text>
          <Thermometer
            initialPercentage={0}
            additionalPercentage={0}
            className={styles.thermometer}
            aria-hidden={true}
            theme='custom'
          />
          <Text isBold isMarginless>
            {formatMoney({
              amount: peerToPeerValue.team.teamMemberGoalAmount,
              notation: 'compact',
              currency: peerToPeerValue.currencyCode,
            })}
          </Text>
        </div>
      </WidgetContent>
      <WidgetFooter>
        <Columns isMarginless isResponsive={false}>
          <Column className={styles.column}>
            <Button isFullWidth onClick={() => setIsShareDrawerOpen(true)} theme='custom'>
              Share
            </Button>
          </Column>
          {renderSpacingColumn()}
          <Column className={styles.column}>
            <Button isFullWidth isOutlined href={link} rel='noreferrer' target='_blank' theme='custom'>
              View
            </Button>
          </Column>
        </Columns>
        <PeerToPeerFooter isOnWidget />
      </WidgetFooter>
      <ShareDrawer
        isOpen={isShareDrawerOpen}
        onClose={() => setIsShareDrawerOpen(false)}
        links={peerToPeerValue.shareLinks}
      />
    </>
  )
}

export { JoinSummaryScreen }
