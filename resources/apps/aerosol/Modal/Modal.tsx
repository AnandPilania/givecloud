import type { FC, ReactNode } from 'react'
import classNames from 'classnames'
import { Dialog, Transition } from '@headlessui/react'
import styles from './Modal.styles.scss'

interface Props {
  isOpen: boolean
  onClose?: () => void
  isOpaque?: boolean
  children: ReactNode
}

const Modal: FC<Props> = ({ isOpen, onClose = () => {}, isOpaque, children }) => {
  const opacityCss = isOpaque ? 'opacity-75' : 'opacity-100'

  return (
    <Transition.Root show={isOpen}>
      <Dialog as='div' className={styles.dialog} onClose={onClose}>
        <Transition.Child
          enter='ease-out duration-300'
          enterFrom='opacity-0'
          enterTo={opacityCss}
          leave='ease-in duration-200'
          leaveFrom={opacityCss}
          leaveTo='opacity-0'
        >
          <Dialog.Overlay className={classNames(styles.overlay, opacityCss)} />
        </Transition.Child>
        <Transition.Child
          enter='ease-out duration-300'
          enterFrom='opacity-0 scale-95'
          enterTo='opacity-100 scale-100'
          leave='ease-in duration-200'
          leaveFrom='opacity-100 scale-100'
          leaveTo='opacity-0 scale-95'
        >
          {children}
        </Transition.Child>
      </Dialog>
    </Transition.Root>
  )
}

Modal.defaultProps = {
  onClose: () => {},
}

export { Modal }
export { Props as ModalProps }
