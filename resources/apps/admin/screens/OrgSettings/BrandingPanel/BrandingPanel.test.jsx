import { act } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { screen, render, waitFor } from '@/mocks/setup'
import { mockBrandingSettings } from '@/mocks/data'
import { BrandingPanel } from './BrandingPanel'
import { BLUE, PURPLE } from '@/shared/constants/theme'

describe('<BrandingPanel />', () => {
  const mockComponent = () => render(<BrandingPanel />)

  describe('when updating the branding logo', () => {
    it('should upload a logo', async () => {
      const { renderScreen, waitForLoadingToBeFinished, setBrandingSettings } = mockComponent()

      setBrandingSettings({ ...mockBrandingSettings, orgLogo: '' })

      const files = [new File(['hello'], 'hello.png', { type: 'image/png' })]

      renderScreen()

      await waitForLoadingToBeFinished()

      const input = screen.getByTestId('image-upload')

      await act(async () => userEvent.upload(input, files))

      expect(input.files).toHaveLength(1)
      expect(input.files?.[0]).toStrictEqual(files[0])
    })

    it('should remove the logo when the remove button is pressed', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      const images = screen.getAllByRole('img')
      expect(images).toHaveLength(1)

      userEvent.click(screen.getByRole('button', { name: 'remove brand-logo image' }))

      waitFor(() => expect(images).toHaveLength(0))
    })
  })

  describe('when updating the branding colour', () => {
    it('should render the colour picker with the default colour', async () => {
      const { renderScreen, waitForLoadingToBeFinished, setBrandingSettings } = mockComponent()

      setBrandingSettings({ ...mockBrandingSettings, orgPrimaryColor: PURPLE.code })

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(screen.getByLabelText(`${PURPLE.value} colour tile`)).toBeInTheDocument()
    })

    it('should open the Colour Picker when the colour tile is clicked', async () => {
      const { renderScreen, waitForLoadingToBeFinished, setBrandingSettings } = mockComponent()

      setBrandingSettings({ ...mockBrandingSettings, orgPrimaryColor: PURPLE.code })

      renderScreen()

      await waitForLoadingToBeFinished()

      userEvent.click(screen.getByRole('button', { name: `${PURPLE.value} colour tile` }))

      expect(screen.getByRole('tooltip', { name: 'update branding colour' })).toBeInTheDocument()
    })

    it('should update the branding colour when the user selects a different recommended colour', async () => {
      const { renderScreen, waitForLoadingToBeFinished, setBrandingSettings } = mockComponent()

      setBrandingSettings({ ...mockBrandingSettings, orgPrimaryColor: PURPLE.code })

      renderScreen()

      await waitForLoadingToBeFinished()

      userEvent.click(screen.getByRole('button', { name: `${PURPLE.value} colour tile` }))

      const [firstRadio, ...ignoredRestofRadios] = screen.getAllByRole('radio')

      userEvent.click(firstRadio)
      userEvent.click(document.body)

      expect(screen.getByLabelText(`${BLUE.value} colour tile`)).toBeInTheDocument()
    })
  })

  it('should render a disabled save button by default', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByRole('button', { name: 'Save Branding Settings' })).toHaveAttribute('aria-disabled', 'true')
  })

  it('should enable the save button when the user has changed the branding colour', async () => {
    const { renderScreen, waitForLoadingToBeFinished, setBrandingSettings } = mockComponent()

    setBrandingSettings({ ...mockBrandingSettings, orgPrimaryColor: PURPLE.code })

    renderScreen()

    await waitForLoadingToBeFinished()

    userEvent.click(screen.getByRole('button', { name: `${PURPLE.value} colour tile` }))

    const [ignoredFirstRadio, secondRadio, ...ignoredRestofRadios] = screen.getAllByRole('radio')

    userEvent.click(secondRadio)
    userEvent.click(document.body)

    expect(screen.getByRole('button', { name: 'Save Branding Settings' })).not.toHaveAttribute('aria-disabled', 'true')
  })
})
