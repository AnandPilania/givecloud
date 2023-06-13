import { useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import { useRecoilState } from 'recoil'
import { useHistory } from 'react-router-dom'
import classnames from 'classnames'
import appSourceState from '@/atoms/appSource'
import { Sidebar } from '@/screens/Layout/Sidebar'
import { TopBar } from '@/screens/Layout/TopBar'
import styles from './Layout.scss'

const Layout = ({ children }) => {
  const history = useHistory()
  const [pathname, setPathname] = useState(history?.location?.pathname)
  const [appSource, setAppSource] = useRecoilState(appSourceState)

  useEffect(() => {
    const historyRemoveListener = history?.listen?.((location) => {
      // Need to check if path changed since this callback fires on hash or query changes as well.
      const hasPathnameChanged = location?.pathname !== pathname

      if (appSource !== 'SPA' && hasPathnameChanged) {
        const mainContent = document.getElementById('mainContent')

        mainContent?.remove?.()
        setAppSource('SPA')
        setPathname(location.pathname)
      }
    })

    return () => {
      historyRemoveListener?.()
    }
  }, [history, appSource, setAppSource, pathname])

  return (
    <div className={styles.root}>
      <div className={styles.sidebarContainer}>
        <Sidebar />
      </div>

      <div className={classnames(styles.contentContainer)}>
        <TopBar />

        <main id='spaContent' className={styles.main} tabIndex='0'>
          {children}
        </main>
      </div>
    </div>
  )
}

Layout.propTypes = {
  children: PropTypes.node,
}

export { Layout }
