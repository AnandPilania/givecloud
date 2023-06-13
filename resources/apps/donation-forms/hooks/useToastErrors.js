import { toast } from 'react-toastify'
import { flatten } from 'lodash'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationTriangle } from '@fortawesome/pro-regular-svg-icons'
import { applySubstitutions } from './useErrorBag'
import useLocalization from './useLocalization'
import styles from './useToastErrors.scss'

const useToastErrors = () => {
  const t = useLocalization()

  const toastError = (message) => {
    toast.error(message || t('unknown_error_521'), {
      autoClose: 5000,
      className: styles.root,
      closeButton: false,
      closeOnClick: true,
      draggable: false,
      icon: <FontAwesomeIcon icon={faExclamationTriangle} size='lg' />,
      hideProgressBar: true,
      newestOnTop: false,
      pauseOnFocusLoss: false,
      position: 'top-center',
      rtl: false,
    })
  }

  return (error = null) => {
    error && console.error(error)

    try {
      // prettier-ignore
      let message =
             error?.response?.data?.error
          || error?.response?.data?.errors
          || error?.response?.data?.message
          || error?.response?.message
          || error?.error?.message
          || error?.data?.error
          || error?.data?.errors
          || error?.data?.message
          || error?.message
          || error?.error
          || error

      if (Array.isArray(message)) {
        message = flatten(Object.values(message))?.[0]
      }

      toastError(applySubstitutions(message))
    } catch (err) {
      toastError()
    }
  }
}

export default useToastErrors
