import userEvent from '@testing-library/user-event'
import { render, screen, act } from '@/mocks/setup'
import { LayoutCard } from './LayoutCard'

describe('<LayoutCard />', () => {
  let mockFeatureFlag: Record<string, boolean>

  const mockComponent = () => render(<LayoutCard />, { config: { ...mockFeatureFlag } })

  describe('when the standard layout feature flag is off', () => {
    beforeAll(() => (mockFeatureFlag = { isFundraisingFormsStandardLayoutEnabled: false }))
    afterAll(() => (mockFeatureFlag = {}))

    it('should have the simplified layout option selected by default', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(screen.getByRole('radio', { name: /simplified/i })).toBeChecked()
    })

    it('should have the standard layout option disabled by default', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(screen.getByRole('radio', { name: /standard/i })).not.toBeChecked()
      expect(screen.getByRole('radio', { name: /standard/i })).toBeDisabled()
    })

    it('should not display text inputs', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(screen.queryAllByRole('textbox')).toHaveLength(0)
    })
  })

  describe('when the standard layout feature flag is on', () => {
    beforeAll(() => (mockFeatureFlag = { isFundraisingFormsStandardLayoutEnabled: true }))
    afterAll(() => (mockFeatureFlag = {}))

    it('should have the standard layout selected by default', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(screen.getByRole('radio', { name: /standard/i })).toBeChecked()
      expect(screen.getByRole('radio', { name: /simplified/i })).not.toBeChecked()
      expect(screen.getByRole('radio', { name: /simplified/i })).toBeEnabled()
    })

    it('should disable text inputs when the simplified layout is selected', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      userEvent.click(screen.getByRole('radio', { name: /simplified/i }))

      const inputs = screen.getAllByRole('textbox')

      expect(inputs).toHaveLength(2)
      expect(inputs[0]).toBeDisabled()
      expect(inputs[1]).toBeDisabled()
    })

    it('should disable text inputs when the standard layout is selected', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      const inputs = screen.getAllByRole('textbox')

      expect(inputs).toHaveLength(2)
      expect(inputs[0]).toBeEnabled()
      expect(inputs[1]).toBeEnabled()
    })

    it('should display errors when text inputs are empty', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      const headlineInput = screen.getByRole('textbox', { name: 'Headline' })
      const descriptionInput = screen.getByRole('textbox', { name: 'Description' })

      userEvent.clear(headlineInput)

      userEvent.tab()

      userEvent.clear(descriptionInput)

      userEvent.tab()

      expect(headlineInput).toBeInvalid()
      expect(descriptionInput).toBeInvalid()
      expect(screen.getAllByTestId('error-0')).toHaveLength(2)
    })

    it('should upload a backgroud image', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      const files = [new File(['background'], 'background.png', { type: 'image/png' })]

      const backgroundImgPicker = screen.getByTestId('image-upload') as HTMLInputElement

      await act(async () => userEvent.upload(backgroundImgPicker, files))

      expect(backgroundImgPicker.files).toHaveLength(1)
      expect(backgroundImgPicker.files?.[0]).toStrictEqual(files[0])
    })
  })
})
