import type { FC, PropsWithChildren, ReactNode } from 'react'
import { useTailwindBreakpoints } from '@/shared/hooks'
import classNames from 'classnames'
import { Container, Columns, Column, SlideTransition } from '@/aerosol'
import { SlideAnimation } from '@/shared/components/SlideAnimation'
import styles from './Layout.styles.scss'

interface Props extends PropsWithChildren {
  widget?: ReactNode
  image?: string
  initWidgetAnimation?: boolean
}

const Layout: FC<Props> = ({ children, widget, image, initWidgetAnimation = true }) => {
  const { large } = useTailwindBreakpoints()

  const renderImage = () => {
    if (large.lessThan) return null
    return (
      <div className={styles.imageContainer}>
        <img className={styles.image} src={image} alt='' />
      </div>
    )
  }

  const renderWidget = () => {
    if (large.lessThan && initWidgetAnimation) {
      return (
        <SlideTransition className={styles.transition} isOpenOnMounted>
          {widget}
        </SlideTransition>
      )
    }

    if (large.greaterThan && initWidgetAnimation) {
      return (
        <SlideAnimation slideInFrom='right' className={styles.transition}>
          {widget}
        </SlideAnimation>
      )
    }

    return widget
  }

  const renderContent = () => {
    if (large.lessThan) {
      return (
        <div className={styles.mobileContent}>
          <div className={styles.mobileImage} style={{ backgroundImage: `url(${image})` }} />
          {renderWidget()}
        </div>
      )
    }
    return (
      <Container containerWidth='large' className={styles.container}>
        <div className={styles.wrapper}>
          <Columns className='h-full overflow-hidden' isMarginless isResponsive={false} isStackingOnMobile={false}>
            <Column isPaddingless className={styles.contentContainer}>
              {children}
            </Column>
            <Column className={styles.column}>{renderWidget()}</Column>
          </Columns>
          <svg className={classNames(styles.svg)} viewBox='0 0 100 100' preserveAspectRatio='none' aria-hidden='true'>
            <polygon points='0,0 90,0 50,100 0,100' />
          </svg>
        </div>
      </Container>
    )
  }
  return (
    <div className={styles.root}>
      {renderContent()}
      {renderImage()}
    </div>
  )
}

export { Layout }
