import type { FC, HTMLProps, ReactNode } from 'react'
import { useEffect, useState } from 'react'
import { Transition } from '@headlessui/react'
import styles from './SlideTransition.styles.scss'

interface Props extends Pick<HTMLProps<HTMLDivElement>, 'className'> {
  isOpen?: boolean
  children: ReactNode
  isOpenOnMounted?: boolean
}

const SlideTransition: FC<Props> = ({ isOpen, children, isOpenOnMounted, className }) => {
  if (isOpenOnMounted) {
    useEffect(() => {
      setIsMounted(true)
      return () => {
        setIsMounted(false)
      }
    }, [])

    const [isMounted, setIsMounted] = useState(false)
    return (
      <Transition
        className={className}
        show={isMounted}
        enter={styles.enter}
        enterFrom={styles.enterFrom}
        enterTo={styles.enterTo}
        leave={styles.leave}
        leaveFrom={styles.leaveFrom}
        leaveTo={styles.leaveTo}
      >
        {children}
      </Transition>
    )
  }

  return (
    <Transition
      className={className}
      show={isOpen}
      enter={styles.enter}
      enterFrom={styles.enterFrom}
      enterTo={styles.enterTo}
      leave={styles.leave}
      leaveFrom={styles.leaveFrom}
      leaveTo={styles.leaveTo}
    >
      {children}
    </Transition>
  )
}

export { SlideTransition }
