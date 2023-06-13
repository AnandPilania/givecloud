import type { FC } from 'react'
import { JOIN_TEAM_PATH } from '@/constants/paths'
import { useState } from 'react'
import classNames from 'classnames'
import { Button, Column, Columns, Thermometer } from '@/aerosol'
import { Confetti, HeroAvatar, Text, WidgetContent, WidgetFooter, WidgetHeader } from '@/components'
import { InviteDrawer } from './InviteDrawer'
import { ShareDrawer } from './ShareDrawer'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { formatMoney } from '@/shared/utilities'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { avatarMapWithFallback } from '@/screens/PeerToPeer/svgs'
import styles from './TeamSummaryScreen.styles.scss'

interface Props {
  index: number
}

const TeamSummaryScreen: FC<Props> = ({ index }) => {
  const { peerToPeerValue, team } = usePeerToPeerState()
  const { fundraisingExperience } = useFundraisingExperienceState()
  const {
    logo_url,
    primary_colour,
    global_settings: { org_website },
  } = fundraisingExperience
  const { activeIndex } = useCarouselContext()
  const [isShareDrawerOpen, setIsShareDrawerOpen] = useState(false)
  const [isInviteDrawerOpen, setIsInviteDrawerOpen] = useState(false)
  const isInitAnimationOn = index === activeIndex

  const renderConfetti = () => (isInitAnimationOn ? <Confetti options={{ colors: [primary_colour] }} /> : null)

  return (
    <>
      {renderConfetti()}
      <WidgetHeader onCloseHref={org_website}>
        <img src={logo_url} className={styles.logo} alt='' />
      </WidgetHeader>
      <WidgetContent className={styles.root}>
        <HeroAvatar initAnimationOn={isInitAnimationOn} theme='primary'>
          {avatarMapWithFallback[peerToPeerValue.avatarName]}
        </HeroAvatar>
        <Text isBold type='h2' className={styles.text}>
          Your team is ready to fundraise!
        </Text>
        <div className={styles.thermometerContainer}>
          <Text isBold isMarginless className={classNames(styles.text)}>
            {formatMoney({ amount: 0, showZero: true, digits: 0, currency: peerToPeerValue.currencyCode })}
          </Text>
          <Thermometer
            initialPercentage={0}
            additionalPercentage={0}
            className={styles.thermometer}
            aria-hidden={true}
            theme='custom'
          />
          <Text isBold isMarginless className={styles.text}>
            {formatMoney({ amount: team.goalAmount, notation: 'compact', currency: peerToPeerValue.currencyCode })}
          </Text>
        </div>
      </WidgetContent>
      <WidgetFooter>
        <Columns isMarginless>
          <Column isPaddingless columnWidth='six'>
            <Button isFullWidth onClick={() => setIsInviteDrawerOpen(true)} theme='custom'>
              Invite Teammates
            </Button>
          </Column>
        </Columns>
        <Columns isResponsive={false} isStackingOnMobile={false} isMarginless>
          <Column className={styles.column}>
            <Button isFullWidth isOutlined onClick={() => setIsShareDrawerOpen(true)} theme='custom'>
              Share
            </Button>
          </Column>
          <Column columnWidth='small' className={classNames(styles.column, styles.left)}>
            <Button
              isFullWidth
              isOutlined
              to={{
                pathname: `${JOIN_TEAM_PATH}/${team.campaignId}`,
                search: team.joinCode ? `?joinCode=${team.joinCode}` : '',
              }}
              theme='custom'
            >
              Join
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
      <InviteDrawer
        isOpen={isInviteDrawerOpen}
        onClose={() => setIsInviteDrawerOpen(false)}
        joinCode={team.joinCode}
        name={team.name}
        shortUrl={team.shortUrl}
      />
    </>
  )
}

export { TeamSummaryScreen }
