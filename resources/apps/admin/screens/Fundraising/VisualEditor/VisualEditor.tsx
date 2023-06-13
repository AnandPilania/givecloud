import type { FC, FormEvent } from 'react'
import { useEffect } from 'react'
import { Container, Modal, ToastContainer } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { DesktopVisualEditor } from './DesktopVisualEditor'
import { MobileVisualEditor } from './MobileVisualEditor'
import { useHistory } from 'react-router-dom'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'

interface Props {
  isOpen: boolean
  onSubmit: (e: FormEvent<HTMLFormElement>) => void
  isLoading: boolean
}

const VisualEditor: FC<Props> = ({ isOpen, onSubmit, isLoading }) => {
  const { medium } = useTailwindBreakpoints()
  const { replace } = useHistory()
  const { brandingValue } = useFundraisingFormState()

  useEffect(() => {
    setRootThemeColour({ colour: brandingValue.brandingColour.code })
  }, [brandingValue.brandingColour])

  const onClose = () => replace({ search: '' })

  const renderContent = () =>
    medium.lessThan ? (
      <MobileVisualEditor onClose={onClose} isLoading={isLoading} />
    ) : (
      <DesktopVisualEditor onClose={onClose} isLoading={isLoading} />
    )

  return (
    <Modal isOpen={isOpen}>
      <form data-public data-testid='fundraising-form' noValidate onSubmit={onSubmit}>
        <Container className='mt-4' containerWidth='large' aria-live='assertive' aria-busy={isLoading}>
          {renderContent()}
        </Container>
      </form>
      <ToastContainer containerId='visual-editor' />
    </Modal>
  )
}

export { VisualEditor }
