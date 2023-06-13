import type { FC } from 'react'
import type { TypeOptions } from 'react-toastify'
import { useEffect } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faXmark } from '@fortawesome/free-solid-svg-icons'
import { ToastButton } from './ToastButton'

interface Props {
  closeToast?: () => void
  buttonTheme: TypeOptions
}

const ToastCloseButton: FC<Props> = ({ closeToast, buttonTheme }) => {
  useEffect(() => {
    return () => closeToast?.()
  }, [])

  return (
    <ToastButton aria-label='close toast' onClick={closeToast} theme={buttonTheme} className='mx-3'>
      <FontAwesomeIcon icon={faXmark} size='lg' />
    </ToastButton>
  )
}

ToastCloseButton.defaultProps = {
  buttonTheme: 'success',
}

export { ToastCloseButton }
