import type { FC, HTMLProps, ReactNode } from 'react'
import { useEffect, useRef, useState, isValidElement } from 'react'
import classNames from 'classnames'
import { ProgressBar } from '@/aerosol/ProgressBar'
import styles from './Container.styles.scss'

enum CONTAINER_WIDTH {
  extraSmall = 'extraSmall',
  small = 'small',
  medium = 'medium',
  large = 'large',
  full = 'full',
}

const headerOnPageHeight = 92

interface Props extends HTMLProps<HTMLDivElement> {
  containerWidth: keyof typeof CONTAINER_WIDTH
  adjustHeight: number
  staticContent: ReactNode
  initialCompletion: number
  isTopBarVisible: boolean
  isScrollable: boolean
  isScrollShadowVisible: boolean
}

const Container: FC<Partial<Props>> = ({
  containerWidth = 'small',
  staticContent,
  isScrollable,
  isTopBarVisible = true,
  adjustHeight = 0,
  initialCompletion = 5,
  isScrollShadowVisible,
  children,
  className,
  ...rest
}) => {
  const containerRef = useRef<HTMLDivElement | null>(null)
  const staticRef = useRef<HTMLDivElement | null>(null)
  const [completion, setCompletion] = useState(initialCompletion)
  const [screenHeight, setScreenHeight] = useState(window.innerHeight)
  const [headerHeight, setHeaderHeight] = useState<number | undefined>(0)
  const [isProgressBarVisisble, setIsProgressBarVisible] = useState(false)
  const height = screenHeight - (isTopBarVisible ? headerOnPageHeight : 0) - (headerHeight ?? 0)

  const handleScrollBarVisiblity = (scrollHeight?: number, clientHeight?: number) =>
    scrollHeight === clientHeight ? setIsProgressBarVisible(false) : setIsProgressBarVisible(true)

  useEffect(() => {
    if (isScrollable) {
      setHeaderHeight(staticRef?.current?.clientHeight)

      const handleWindowResize = () => {
        setScreenHeight(window.innerHeight)
        setHeaderHeight(staticRef?.current?.clientHeight)
        handleScrollBarVisiblity(containerRef?.current?.scrollHeight, containerRef?.current?.clientHeight)
      }

      window.addEventListener('resize', handleWindowResize)

      return () => window.removeEventListener('resize', handleWindowResize)
    }
  }, [staticRef?.current?.clientHeight])

  const onScroll = ({ target }) => {
    const { scrollTop, scrollHeight, clientHeight } = target
    handleScrollBarVisiblity(scrollHeight, clientHeight)
    const remainingScrollHeight = scrollHeight - clientHeight
    setCompletion(Number((scrollTop / remainingScrollHeight).toFixed(2)) * 100)
  }

  useEffect(() => {
    if (isScrollable) {
      onScroll({
        target: {
          scrollTop: containerRef?.current?.scrollTop,
          scrollHeight: containerRef?.current?.scrollHeight,
          clientHeight: containerRef?.current?.clientHeight,
        },
      })
    }
  }, [containerRef?.current])

  useEffect(() => {
    if (isScrollable) {
      if (containerRef?.current?.scrollTop === 0 && !!initialCompletion) setCompletion(initialCompletion)
    }
  }, [containerRef?.current?.scrollTop])

  if (isScrollable) {
    const renderStaticContent = () => {
      if (isValidElement(staticContent)) return <div className={styles.static}>{staticContent}</div>
      return null
    }

    const getHeight = () => height - adjustHeight

    const renderScrollBar = () =>
      isProgressBarVisisble ? <ProgressBar theme='primary' completion={completion} /> : null
    return (
      <>
        <div
          ref={staticRef}
          className={classNames(styles.root, styles.container, styles[containerWidth], isProgressBarVisisble && 'pb-4')}
        >
          {renderStaticContent()}
          {renderScrollBar()}
        </div>
        <div
          style={{
            height: getHeight(),
          }}
          ref={containerRef}
          onScroll={onScroll}
          className={classNames(styles.root, styles.scroll, isScrollShadowVisible && styles.scrollShadow, className)}
          {...rest}
        >
          <div className={classNames(styles.root, styles.container, styles[containerWidth], styles.padding)}>
            {children}
          </div>
        </div>
      </>
    )
  }

  return (
    <div
      className={classNames(styles.root, styles.container, styles[containerWidth], styles.padding, className)}
      {...rest}
    >
      {children}
    </div>
  )
}

Container.defaultProps = {
  containerWidth: 'small',
  adjustHeight: 0,
  isTopBarVisible: true,
  initialCompletion: 5,
}
export { Container }
