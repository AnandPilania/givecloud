import type { FC, HTMLProps, PropsWithChildren, ReactNode } from 'react'
import type { ButtonProps } from '@/aerosol'
import classNames from 'classnames'
import { Button, CarouselButton } from '@/aerosol'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowLeft, faClose } from '@fortawesome/pro-regular-svg-icons'
import { useParams, useTailwindBreakpoints } from '@/shared/hooks'
import styles from './WidgetHeader.styles.scss'

interface Props extends PropsWithChildren, Pick<ButtonProps, 'to'>, Pick<HTMLProps<HTMLDivElement>, 'className'> {
  onCloseHref: string
  indexToNavigate?: string
  onBackButtonClick?: () => void
}

const WidgetHeader: FC<Props> = ({ onCloseHref, indexToNavigate, to, children, onBackButtonClick, className }) => {
  const { large } = useTailwindBreakpoints()
  const { setAndReplaceParams } = useParams()

  const renderBackButton = () => {
    if (indexToNavigate) {
      const handleClick = () => setAndReplaceParams('screen', indexToNavigate)

      return (
        <CarouselButton theme='custom' className='border-none' isClean onClick={handleClick} aria-label='go back'>
          <FontAwesomeIcon icon={faArrowLeft} />
        </CarouselButton>
      )
    }

    if (to || onBackButtonClick)
      return (
        <Button theme='custom' to={to} onClick={onBackButtonClick} isClean>
          <FontAwesomeIcon icon={faArrowLeft} />
        </Button>
      )

    return <div className={styles.spacingDiv} />
  }

  const closeButton = (
    <Button theme='custom' isClean href={onCloseHref} aria-label='go back to home page'>
      <FontAwesomeIcon icon={faClose} />
    </Button>
  )

  const renderComponent = (component: ReactNode) => (large.lessThan ? component : null)

  return (
    <div className={classNames(styles.root, className)}>
      {renderBackButton()}
      {renderComponent(children)}
      {renderComponent(closeButton)}
    </div>
  )
}

export { WidgetHeader }
