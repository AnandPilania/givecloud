import { useRecoilValue } from 'recoil'
import Emoji from 'react-emoji-render'
import { Chip } from '@/aerosol'
import config from '@/atoms/config'
import { QUICKSTART_GUIDE } from '@/constants/pathConstants'
import styles from './TopBarQuickStartGuideButton.scss'

const TopBarQuickStartGuideButton = () => {
  const { isGivecloudExpress } = useRecoilValue(config)

  const chipProps = {
    invertTheme: true,
    children: <strong>Quick-Start</strong>,
    href: QUICKSTART_GUIDE,
  }

  if (isGivecloudExpress) {
    return null
  }

  return (
    <div className={styles.root}>
      <Chip {...chipProps} className={styles.desktop}>
        <span className='mr-2'>
          <strong>Quick-Start</strong>
        </span>
        <Emoji text='🚀' />
      </Chip>

      <Chip {...chipProps} className={styles.mobile}>
        <span className='mr-2'>
          <strong>Quick-Start</strong>
        </span>
        <Emoji text='🚀' />
      </Chip>
    </div>
  )
}

export { TopBarQuickStartGuideButton }
