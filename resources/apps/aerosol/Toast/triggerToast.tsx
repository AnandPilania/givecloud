import type { ToastContainerProps, ToastOptions, TypeOptions } from 'react-toastify'
import type { ToastButtonProps } from './Toast'
import { toast } from 'react-toastify'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck, faExclamationTriangle } from '@fortawesome/pro-regular-svg-icons'
import { Toast } from './Toast'
import { ToastCloseButton } from './ToastCloseButton'
import styles from './Toast.styles.scss'

type TriggerToastType = {
  header: string
  type?: TypeOptions
  options?: ToastOptions
  description?: string
  buttonProps?: ToastButtonProps
}

const triggerToast = ({ type = 'success', header, description, buttonProps = {}, options = {} }: TriggerToastType) => {
  const style = type === 'success' ? styles.successToast : styles.errorToast
  const icon = type === 'success' ? faCheck : faExclamationTriangle

  const toastOptions: ToastContainerProps = {
    position: 'top-center',
    autoClose: 5000,
    hideProgressBar: true,
    newestOnTop: false,
    closeButton: <ToastCloseButton buttonTheme={type} />,
    closeOnClick: true,
    rtl: false,
    pauseOnFocusLoss: false,
    draggable: false,
    containerId: 'app',
    ...options,
  }

  toast(<Toast header={header} description={description} buttonProps={buttonProps} type={type} />, {
    className: style,
    icon: <FontAwesomeIcon icon={icon} size='lg' />,
    ...toastOptions,
  })
}

export { triggerToast }
