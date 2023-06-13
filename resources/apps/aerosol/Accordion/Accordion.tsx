import type { FC, HTMLProps, ReactNode, SyntheticEvent } from 'react'
import type { AnimationControls } from 'framer-motion'
import { useEffect, Children, cloneElement, isValidElement } from 'react'
import { useAnimation } from 'framer-motion'
import classNames from 'classnames'
import styles from './Accordion.styles.scss'

export interface AccordionProps {
  isOpen: boolean
  setIsOpen: (event: SyntheticEvent) => void
}

interface Props extends HTMLProps<HTMLDivElement>, AccordionProps {
  children: ReactNode
  hasBorderTop?: boolean
  hasBorderBottom?: boolean
}

interface AccordionChild extends AccordionProps {
  controls: AnimationControls
}

const Accordion: FC<Props> = ({ children, isOpen, setIsOpen, className, hasBorderTop, hasBorderBottom }) => {
  const controls = useAnimation()
  const { start } = controls

  useEffect(() => {
    isOpen ? start('expanded') : start('collapsed')
  }, [isOpen])

  return (
    <div
      className={classNames(
        styles.root,
        hasBorderTop && styles.borderTop,
        hasBorderBottom && styles.borderBottom,
        className
      )}
    >
      {Children.map(children, (child) =>
        isValidElement<AccordionChild>(child) ? cloneElement(child, { controls, isOpen, setIsOpen }) : null
      )}
    </div>
  )
}

Accordion.defaultProps = {
  hasBorderTop: false,
  hasBorderBottom: false,
}

export { Accordion }
