import type { FC } from 'react'
import classNames from 'classnames'
import { SCREENS } from '@/constants/screens'
import { FAQ_PATH, PRIVACY_PATH } from '@/constants/paths'
import { Link } from '@/components'
import { useParams, useTailwindBreakpoints } from '@/shared/hooks'
import styles from './PeerToPeerFooter.styles.scss'

interface Props {
  isOnWidget?: boolean
}

const PeerToPeerFooter: FC<Props> = ({ isOnWidget = false }) => {
  const { large } = useTailwindBreakpoints()
  const { pathname, params } = useParams()

  const getParams = (path: string) => {
    if (params.get(SCREENS.DRAWER)) params.delete(SCREENS.DRAWER)

    params.append(SCREENS.DRAWER, path)
    return params.toString()
  }

  const renderFooter = (isWithinBreakPoint: boolean) =>
    isWithinBreakPoint ? (
      <div className={styles.root}>
        <Link to={{ pathname, search: getParams(FAQ_PATH) }} className={styles.text}>
          FAQ
        </Link>
        <span className={classNames(styles.separator, styles.text)}>|</span>
        <Link to={{ pathname, search: getParams(PRIVACY_PATH) }} className={styles.text}>
          Privacy & Legal
        </Link>
      </div>
    ) : null

  if (isOnWidget) {
    return renderFooter(large.lessThan)
  }
  return renderFooter(!large.lessThan)
}

export { PeerToPeerFooter }
