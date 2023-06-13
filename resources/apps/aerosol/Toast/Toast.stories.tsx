import { ComponentMeta, ComponentStory } from '@storybook/react'
import { ToastContainer } from './ToastContainer'
import { Button } from '@/aerosol/Button'
import { triggerToast } from './triggerToast'

import 'react-toastify/dist/ReactToastify.css'

const ToastTriggerContainer = ({ type, label, onClick }) => (
  <div>
    <Button theme={type} onClick={onClick}>
      {label}
    </Button>
    <ToastContainer containerId='storybook' />
  </div>
)

export default {
  title: 'Aerosol/Toast',
  component: ToastTriggerContainer,
} as ComponentMeta<typeof ToastTriggerContainer>

export const Default: ComponentStory<typeof ToastTriggerContainer> = () => {
  const handleTriggerToast = () => {
    triggerToast({ type: 'success', header: 'Boom, success!', options: { containerId: 'storybook' } })
  }

  return <ToastTriggerContainer type='success' label='Trigger Success Toast' onClick={handleTriggerToast} />
}

export const ErrorToast: ComponentStory<typeof ToastTriggerContainer> = () => {
  const handleTriggerToast = () => {
    triggerToast({
      type: 'error',
      header: 'Error, abort abort!',
      options: { autoClose: false, containerId: 'storybook' },
    })
  }

  return <ToastTriggerContainer type='error' label='Trigger Error Toast' onClick={handleTriggerToast} />
}

export const withCTA: ComponentStory<typeof ToastTriggerContainer> = () => {
  const handleTriggerToast = () => {
    triggerToast({
      type: 'success',
      header: 'Successfully completed XYZ',
      buttonProps: {
        children: 'Continue',
        to: '#',
      },
      options: { autoClose: false, containerId: 'storybook' },
    })
  }

  return <ToastTriggerContainer type='success' label='Trigger CTA Toast' onClick={handleTriggerToast} />
}
