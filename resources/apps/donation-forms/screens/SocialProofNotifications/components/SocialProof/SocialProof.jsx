import { memo, useState } from 'react'
import { useRecoilValue } from 'recoil'
import { shuffle } from 'lodash'
import PropTypes from 'prop-types'
import configState from '@/atoms/config'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import useLocalization from '@/hooks/useLocalization'
import useTimeAgo from '@/hooks/useTimeAgo'
import { assetUrl } from '@/utilities/assets'
import avatarIcon from './images/avatar.png'
import styles from './SocialProof.scss'

const reactionEmojis = ['👌', '❤️', '🔥', '💪', '🎉', '🙌', '💜', '💕', '💖', '😍', '🥰', '🥳']

const SocialProof = ({ socialProof, onClick }) => {
  const t = useLocalization('components.app.social_proof_notifications')
  const [reaction] = useState(shuffle(reactionEmojis)[0])

  const config = useRecoilValue(configState)
  const formatCurrency = useCurrencyFormatter({ abbreviate: true, showCurrencyCode: false })
  const timeAgo = useTimeAgo()

  const params = {
    name: socialProof.anonymous ? t('someone') : socialProof.name,
    initials: socialProof.anonymous ? t('someone') : socialProof.initials,
    location: (socialProof.location || '').replace(/, .+$/, ''),
    amount: formatCurrency(socialProof.amount, socialProof.currency),
    when: timeAgo(socialProof.date),
    reaction,
  }

  // prettier-ignore
  switch (config.social_proof.privacy_mode) {
    case 'initials': params.who = params.initials; break
    case 'geography': params.who = `${t('someone')} ${t('from')} ${params.location}`; break
    case 'initials-and-geography': params.who = `${params.initials} ${t('from')} ${params.location}`; break
    default: params.who = params.name
  }

  return (
    <div className={styles.root} onClick={onClick}>
      <div className={styles.icon}>
        <img src={assetUrl(avatarIcon)} alt='' />
        <span className={styles.reaction}>{params.reaction}</span>
      </div>
      <div className={styles.text} dangerouslySetInnerHTML={t(`${socialProof.type}_html`, params)}></div>
    </div>
  )
}

SocialProof.propTypes = {
  socialProof: PropTypes.any.isRequired,
  onClick: PropTypes.func,
}

export default memo(SocialProof)
