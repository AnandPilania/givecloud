import { render, screen, act } from '@/mocks/setup'
import userEvent from '@testing-library/user-event'
import { SharingCard } from './SharingCard'

describe('<SharingCard />', () => {
  const mockComponent = () => render(<SharingCard />)

  it('should display text inputs for the link title and description', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByRole('textbox', { name: /link title/i })).toBeInTheDocument()
    expect(screen.getByRole('textbox', { name: /link description/i })).toBeInTheDocument()
  })

  it('should display errors when text inputs are empty', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    const titleInput = screen.getByRole('textbox', { name: /link title/i })
    const descriptionInput = screen.getByRole('textbox', { name: /link description/i })

    userEvent.clear(titleInput)

    userEvent.tab()

    userEvent.clear(descriptionInput)

    userEvent.tab()

    expect(titleInput).toBeInvalid()
    expect(descriptionInput).toBeInvalid()
    expect(screen.getAllByTestId('error-0')).toHaveLength(2)
  })

  it('should upload a link preview image', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    const files = [new File(['preview'], 'preview.png', { type: 'image/png' })]

    const previewImgPicker = screen.getByTestId('image-upload') as HTMLInputElement

    await act(async () => userEvent.upload(previewImgPicker, files))

    expect(previewImgPicker.files).toHaveLength(1)
    expect(previewImgPicker.files?.[0]).toStrictEqual(files[0])
  })
})
