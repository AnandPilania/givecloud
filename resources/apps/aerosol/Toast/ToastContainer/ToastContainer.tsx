import type { FC } from 'react'
import type { ToastContainerProps } from 'react-toastify'
import { ToastContainer as ToastifyContainer } from 'react-toastify'
import styles from './ToastContainer.styles.scss'

type Props = Pick<ToastContainerProps, 'containerId'>

const ToastContainer: FC<Props> = ({ containerId }) => (
  <ToastifyContainer enableMultiContainer className={styles.toast} containerId={containerId} />
)

export { ToastContainer }
