import type { FC, HTMLProps, PropsWithChildren } from 'react'
import type { AccordionProps } from '@/aerosol/Accordion'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faChevronRight } from '@fortawesome/pro-regular-svg-icons'
import styles from './AccordionHeader.styles.scss'

interface Props extends PropsWithChildren<HTMLProps<HTMLDivElement>>, Partial<AccordionProps> {
  isIconVisible?: boolean
}

const AccordionHeader: FC<Props> = ({ setIsOpen, children, isOpen, className, isIconVisible = true, ...rest }) => {
  const renderIcon = () =>
    isIconVisible ? (
      <div className={styles.wrapper}>
        <FontAwesomeIcon
          size='sm'
          className={classNames(styles.icon, isOpen && styles.open)}
          icon={faChevronRight}
          aria-hidden='true'
        />
      </div>
    ) : null

  return (
    <div
      {...rest}
      aria-expanded={isOpen}
      role='button'
      tabIndex={0}
      onClick={setIsOpen}
      onKeyDown={setIsOpen}
      className={classNames(styles.button, className)}
    >
      {renderIcon()}
      <div className={classNames(styles.content, isIconVisible && styles.marginLeft)}>{children}</div>
    </div>
  )
}

export { AccordionHeader }
