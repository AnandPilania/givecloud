import type { FC } from 'react'
import { Text, Thermometer } from '@/aerosol'
import { WidgetContent, HeroAvatar } from '@/components'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { avatarMapWithFallback } from '@/screens/PeerToPeer/svgs'
import { formatMoney } from '@/shared/utilities'
import { TeamMembers } from './TeamMembers'
import styles from './JoinTeamPeerToPeerWidgetContent.styles.scss'

const JoinTeamPeerToPeerWidgetContent: FC = () => {
  const { peerToPeerValue, team } = usePeerToPeerState()

  const renderMembers = () =>
    !!team.members.length ? (
      <TeamMembers team={team.members} />
    ) : (
      <Text isMarginless type='h2' className={styles.text} isSecondaryColour>
        Be the first teammate!
      </Text>
    )

  return (
    <WidgetContent className={styles.root}>
      <HeroAvatar preventAnimation theme='primary'>
        {avatarMapWithFallback[peerToPeerValue.avatarName]}
      </HeroAvatar>
      <Text isMarginless isBold type='h2' className={styles.text}>
        {peerToPeerValue.team.name}
      </Text>
      <div className={styles.thermometerContainer}>
        <Text isBold isMarginless className={styles.text}>
          {formatMoney({
            amount: team.amountRaised ?? 0,
            showZero: true,
            digits: 0,
            currency: peerToPeerValue.currencyCode,
          })}
        </Text>
        <Thermometer
          initialPercentage={((team.amountRaised ?? 0) / team.goalAmount) * 100}
          additionalPercentage={0}
          className={styles.thermometer}
          aria-label='team fundraising goal thermometer'
          theme='custom'
        />
        <Text isMarginless className={styles.text}>
          {formatMoney({ amount: team.goalAmount, notation: 'compact', currency: peerToPeerValue.currencyCode })}
        </Text>
      </div>
      {renderMembers()}
    </WidgetContent>
  )
}

export { JoinTeamPeerToPeerWidgetContent }
