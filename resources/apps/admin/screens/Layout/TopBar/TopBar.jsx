import { Suspense, useState } from 'react'
import { useLocation } from 'react-router-dom'
import { Drawer } from '@/components'
import { Skeleton, Button } from '@/aerosol'
import { Sidebar } from '@/screens/Layout/Sidebar'
import { TopBarCTA } from './TopBarCTA'
import { GivecloudLogo } from '@/components/GivecloudLogo'
import { TopBarMenu } from '@/screens/Layout/TopBar/TopBarMenu'
import { TopBarBreadcrumb } from '@/screens/Layout/TopBar/TopBarBreadcrumb'
import { TopBarMenuMobileButton } from '@/screens/Layout/TopBar/TopBarMenuMobileButton'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './TopBar.scss'

const ButtonSkeleton = () => (
  <div className={styles.buttonSkeletonContainer}>
    <Skeleton isMarginless width='full' height='medium' className={styles.buttonSkeleton} />
  </div>
)

const TopBar = () => {
  const { medium } = useTailwindBreakpoints()
  const [menuButtonRef, setMenuButtonRef] = useState()
  const location = useLocation()

  const renderCTA = () =>
    location.pathname !== '/' ? (
      <Suspense fallback={<ButtonSkeleton />}>
        <TopBarCTA />
      </Suspense>
    ) : null

  const renderTopBarMenu = () => (medium.greaterThan ? <TopBarMenu /> : <TopBarMenuMobileButton />)

  return (
    <div className={styles.root}>
      <Drawer toggleElementRef={menuButtonRef} isFullScreen>
        <Sidebar isMobile />
      </Drawer>

      <div className={styles.mainContent}>
        <div className={styles.mainContentLeft}>
          <div className={styles.mobileMenuButtonContainer}>
            <Button ref={setMenuButtonRef} size='medium' isClean aria-label='Open sidebar'>
              <GivecloudLogo className={styles.givecloudLogo} />
            </Button>
          </div>

          <div className={styles.breadcrumbContainer}>
            <TopBarBreadcrumb />
          </div>
        </div>

        <div className={styles.mainContentRight}>
          {renderTopBarMenu()}
          {renderCTA()}
        </div>
      </div>

      <div className={styles.mobileBreadcrumbContainer}>
        <TopBarBreadcrumb />
      </div>

      <div className={styles.gradient} />
    </div>
  )
}

export { TopBar }
