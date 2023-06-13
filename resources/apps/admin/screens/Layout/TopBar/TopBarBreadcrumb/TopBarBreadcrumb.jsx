import { useEffect, useState, useCallback } from 'react'
import isEmpty from 'lodash/isEmpty'
import throttle from 'lodash/throttle'
import { Transition } from '@headlessui/react'
import { Link } from '@/components'
import useGetBreadcrumbBackButton from '@/hooks/useGetBreadcrumbBackButton'
import styles from './TopBarBreadcrumb.scss'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faChevronRight, faArrowLeft } from '@fortawesome/pro-regular-svg-icons'

const TopBarBreadcrumb = () => {
  const backButton = useGetBreadcrumbBackButton() || {}
  const [showPageTitleText, setShowPageTitleText] = useState(false)

  const { text: backButtonText, to: backButtonTo, url: backButtonUrl } = backButton
  const hasBackButton = !isEmpty(backButton)
  const pageTitleEl = document.querySelector('.page-header .page-header-text')
  const pageTitleText = pageTitleEl?.textContent

  const handleScroll = useCallback(
    (e) => {
      const { y: pageTitleYPosition } = pageTitleEl?.getBoundingClientRect?.() || {}
      const scrollPosition = e?.target?.scrollTop

      if (pageTitleYPosition < scrollPosition) {
        if (!showPageTitleText && pageTitleText && typeof pageTitleText === 'string') {
          setShowPageTitleText(true)
        }
      } else if (showPageTitleText) {
        setShowPageTitleText(false)
      }
    },
    [pageTitleEl, showPageTitleText, pageTitleText]
  )

  useEffect(() => {
    if (hasBackButton) {
      const throttledHandleScroll = throttle(handleScroll, 100)

      window.addEventListener('scroll', throttledHandleScroll, true)

      return () => {
        window.removeEventListener('scroll', throttledHandleScroll, true)
      }
    }
  }, [hasBackButton, handleScroll])

  if (!hasBackButton) return null

  return (
    <div className={styles.root}>
      <Link href={backButtonUrl} to={backButtonTo}>
        <FontAwesomeIcon className={styles.backIcon} icon={faArrowLeft} />
        <span>{backButtonText}</span>
      </Link>

      <Transition
        className={styles.pageTitle}
        show={showPageTitleText}
        enter='transition duration-300 ease-in-out'
        enterFrom='translate-y-full opacity-0'
        enterTo='translate-y-0 opacity-100'
        leave='transition duration-300 ease-in-out'
        leaveFrom='translate-y-0 opacity-100'
        leaveTo='translate-y-full opacity-0'
      >
        <FontAwesomeIcon className={styles.pageTitleIcon} icon={faChevronRight} />

        <span className={styles.pageTitleText}>{pageTitleText}</span>
      </Transition>
    </div>
  )
}

export { TopBarBreadcrumb }
