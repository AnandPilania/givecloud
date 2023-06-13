import { Fragment, memo, useEffect, useRef } from 'react'
import { useEffectOnce, useWindowSize } from 'react-use'
import { useRecoilState, useRecoilValue } from 'recoil'
import { MemoryRouter, Redirect, Route } from 'react-router-dom'
import classnames from 'classnames'
import { AnimatePresence } from 'framer-motion'
import routes, { DEFAULT_PATH } from '@/routes'
import { SCREEN_LARGE, SCREEN_XS } from '@/constants/breakpointConstants'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import SocialProofNotifications from '@/screens/SocialProofNotifications/SocialProofNotifications'
import StandardLayout from '@/components/StandardLayout/StandardLayout'
import Header from '@/components/Screen/components/Header/Header'
import FrequentlyAskedQuestions from '@/screens/FAQAndPrivacyLegal/FrequentlyAskedQuestions/FrequentlyAskedQuestions'
import PrivacyAndLegal from '@/screens/FAQAndPrivacyLegal/PrivacyAndLegal/PrivacyAndLegal'
import useAnalytics from '@/hooks/useAnalytics'
import configState from '@/atoms/config'
import confettiOptionsState from '@/atoms/confettiOptions'
import styles from './Layout.scss'

const Layout = () => {
  const { width: windowWidth, height: windowHeight } = useWindowSize()

  const config = useRecoilValue(configState)
  const collectEvent = useAnalytics({ collectOnce: true, hostedPageOnly: true })

  const innerDivRef = useRef()
  const [confettiOptions, setConfettiOptions] = useRecoilState(confettiOptionsState)

  const isLargeScreen = windowWidth >= SCREEN_LARGE && windowHeight > SCREEN_XS
  const isStandardLayout = config.layout === 'standard'
  const isLargeScreenStandardLayout = isStandardLayout && isLargeScreen

  const setAppHeight = () => {
    if (innerDivRef.current) {
      setConfettiOptions({
        ...confettiOptions,
        width: innerDivRef.current.clientWidth,
        height: innerDivRef.current.clientHeight,
      })
    }

    document.documentElement?.style?.setProperty?.('--app-height', `${window.innerHeight}px`)
  }

  useEffectOnce(() => setAppHeight())

  useEffect(() => setRootThemeColour({ colour: config.primary_colour }), [config.primary_colour])

  useEffect(() => {
    window.addEventListener('resize', setAppHeight)

    return () => {
      window.removeEventListener('resize', setAppHeight)
    }
  })

  const renderRoute = ({ location }) => {
    const redirect = isLargeScreen ? DEFAULT_PATH.desktop : DEFAULT_PATH.mobile

    if (location.pathname === '/') {
      return <Route render={() => <Redirect to={redirect} />} />
    }

    return (
      <>
        <AnimatePresence initial={false}>{routes(location)}</AnimatePresence>
        <SocialProofNotifications />
        {isLargeScreenStandardLayout && (
          <>
            <FrequentlyAskedQuestions />
            <PrivacyAndLegal />
          </>
        )}
      </>
    )
  }

  collectEvent({ event_name: 'pageview' })

  const LayoutComponent = isLargeScreenStandardLayout ? StandardLayout : Fragment

  return (
    <>
      {config.widget_type === 'hosted_page' && isStandardLayout && <div className={styles.background} />}
      <div className={classnames(styles.root, config.widget_type === 'inline_embed' && styles.inlineWidgetType)}>
        <MemoryRouter>
          <LayoutComponent>
            <div
              ref={innerDivRef}
              className={classnames(styles.inner, isLargeScreenStandardLayout && styles.standardLayout)}
            >
              <Header />
              <Route render={renderRoute} />
              <div id='layout-portal' />
            </div>
          </LayoutComponent>
        </MemoryRouter>
      </div>
    </>
  )
}

export default memo(Layout)
