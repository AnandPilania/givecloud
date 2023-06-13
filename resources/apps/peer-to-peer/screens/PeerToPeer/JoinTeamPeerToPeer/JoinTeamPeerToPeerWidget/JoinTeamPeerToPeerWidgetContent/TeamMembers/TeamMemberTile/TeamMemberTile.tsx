import type { FC } from 'react'
import type { PeerToPeerCampaign } from '@/types'
import { Columns, Column, Thermometer } from '@/aerosol'
import { ClickableTile, HeroAvatar, Text } from '@/components'
import { formatMoney } from '@/shared/utilities'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { avatarMap } from '@/screens/PeerToPeer/svgs'
import styles from './TeamMemberTile.styles.scss'

interface Props {
  teamMember: PeerToPeerCampaign
}

const TeamMemberTile: FC<Props> = ({
  teamMember: { id, amount_raised, avatar_name, social_avatar, title, goal_amount, currency_code },
}) => {
  const link = `${window.location.origin}/fundraising/p2p/donate/${id}`
  const renderAvatar = () =>
    !!social_avatar ? (
      <HeroAvatar size='small' isMarginless preventAnimation src={social_avatar} />
    ) : (
      <HeroAvatar size='small' isMarginless preventAnimation>
        {avatarMap[avatar_name]}
      </HeroAvatar>
    )

  return (
    <ClickableTile href={link}>
      <Columns isResponsive={false} isStackingOnMobile={false} className={styles.tileColumns}>
        <Column columnWidth='small'>{renderAvatar()}</Column>
        <Column className={styles.tileContent}>
          <Text className={styles.tileHeading} type='footnote'>
            {title}’s Challenge <FontAwesomeIcon icon={faArrowRight} className='ml-1' />
          </Text>
          <div className={styles.tileThermometer}>
            <Thermometer
              initialPercentage={((amount_raised ?? 0) / goal_amount) * 100}
              additionalPercentage={0}
              className='mr-2'
              isThin
              theme='custom'
            />
            <Text type='footnote' isBold isMarginless>
              {formatMoney({
                amount: amount_raised ?? 0,
                notation: 'compact',
                showZero: true,
                currency: currency_code,
              })}
            </Text>
          </div>
        </Column>
      </Columns>
    </ClickableTile>
  )
}

export { TeamMemberTile }
