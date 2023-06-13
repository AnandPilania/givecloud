import type { FC, ReactNode } from 'react'
import type { ModalProps } from '@/aerosol/Modal'
import classNames from 'classnames'
import { Dialog as HeadlessUIDialog } from '@headlessui/react'
import { Box } from '@/aerosol/Box'
import { Modal } from '@/aerosol/Modal'
import { ToastContainer } from '@/aerosol/Toast'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './Dialog.styles.scss'

type Sizes = 'small' | 'medium' | 'large'

interface Props extends Omit<ModalProps, 'onClose'> {
  children: ReactNode
  onClose: () => void
  isOverflowVisible?: boolean
  toastContainerId?: string
  size?: Sizes
}

const Dialog: FC<Props> = ({
  isOpen,
  onClose,
  isOverflowVisible,
  children,
  isOpaque,
  toastContainerId,
  size = 'medium',
}) => {
  const { medium } = useTailwindBreakpoints()

  const renderToastContainer = () => (toastContainerId ? <ToastContainer containerId={toastContainerId} /> : null)

  return (
    <Modal isOpen={isOpen} onClose={onClose} isOpaque={isOpaque}>
      <div className={styles.root}>
        <HeadlessUIDialog.Panel className={classNames(styles.dialogPanel, styles[size])}>
          <Box isMarginless isFullscreen={medium.lessThan} isOverflowVisible={isOverflowVisible}>
            {children}
          </Box>
        </HeadlessUIDialog.Panel>
      </div>
      {renderToastContainer()}
    </Modal>
  )
}

Dialog.defaultProps = {
  size: 'medium',
  isOverflowVisible: false,
  isOpaque: true,
}

export { Dialog }
export type { Props as DialogProps }
