import { useRecoilValue } from 'recoil'
import classnames from 'classnames'
import configState from '@/atoms/config'
import { ExternalLinkIcon } from '@/components'
import { GIVECLOUD_UPDATES_URL } from '@/constants/urlConstants'
import styles from './TopBarMenuUpdates.scss'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faClock } from '@fortawesome/pro-regular-svg-icons'

const TopBarMenuUpdates = () => {
  const { updates = [] } = useRecoilValue(configState)
  const hasUpdates = updates?.length > 0

  return (
    <div className={styles.root}>
      {hasUpdates ? (
        <ul className={styles.list}>
          {updates.map((update = {}) => {
            const { id, type = '', headline = '', summary = '', is_beta = false, is_new = false } = update
            const typeUppercase = type.toUpperCase()
            const typeNoSpaces = type.replace(' ', '', 'g')

            return (
              <li key={id} className={classnames(styles.listItem, is_new ? styles.is_unread : styles.is_read)}>
                <div className={styles.header}>
                  <a className={styles.link} href={GIVECLOUD_UPDATES_URL} target='_blank' rel='noreferrer'>
                    {!!is_new && <span className={styles.bullet}>•</span>}
                    {headline}
                  </a>

                  <span className={classnames(styles.type, styles[typeNoSpaces])}>{typeUppercase}</span>
                </div>

                <p className={styles.summary}>{summary}</p>

                {!!is_beta && (
                  <div className={styles.betaTag}>
                    <span className={styles.betaLabel}>BETA</span>

                    <span className={styles.betaNote}>Contact support to request access.</span>
                  </div>
                )}
              </li>
            )
          })}
        </ul>
      ) : (
        <div className={styles.noUpdates}>
          <FontAwesomeIcon className={styles.icon} icon={faClock} />

          <span>No Recent Updates</span>
        </div>
      )}

      <div className={styles.viewAllUpdates}>
        <a href={GIVECLOUD_UPDATES_URL} target='_blank' rel='noreferrer'>
          <span>View All Updates</span>

          <ExternalLinkIcon className={styles.externalLinkIcon} />
        </a>
      </div>
    </div>
  )
}

export { TopBarMenuUpdates }
