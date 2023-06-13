import type { ImageData } from './ImagePicker'
import { act, render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import axios from 'axios'
import { ImagePicker } from './ImagePicker'

jest.mock('axios')

const mockedAxios = axios as jest.Mocked<typeof axios>

describe('<ImagePicker/>', () => {
  const mockCreateObjectURL = jest.fn()
  global.URL.createObjectURL = mockCreateObjectURL

  let mockImage: Blob | MediaSource | string
  let mockOnChange: (data: ImageData) => void
  let mockRemoveImage: () => void
  let mockId: string
  let mockLabel: string

  beforeEach(() => {
    mockImage = ''
    mockOnChange = jest.fn()
    mockRemoveImage = jest.fn()
    mockId = 'one'
    mockLabel = 'foo'
  })

  afterEach(() => {
    mockCreateObjectURL.mockReset()
  })

  const renderScreen = () =>
    render(
      <ImagePicker
        label={mockLabel}
        id={mockId}
        image={mockImage}
        onChange={mockOnChange}
        removeImage={mockRemoveImage}
      />
    )

  it('should render an label element for file inputs', () => {
    renderScreen()

    expect(screen.getByLabelText(mockLabel)).toBeInTheDocument()
  })

  it('should be able to upload an image', async () => {
    const files = [new File(['hello'], 'hello.png', { type: 'image/png' })]
    renderScreen()

    const input = screen.getByTestId('image-upload') as HTMLInputElement

    await act(async () => userEvent.upload(input, files))

    expect(input.files).toHaveLength(1)
    expect(input.files?.[0]).toStrictEqual(files[0])
  })

  it('should by default replace multiple images', async () => {
    const files = [
      new File(['hello'], 'hello.png', { type: 'image/png' }),
      new File(['there'], 'there.png', { type: 'image/png' }),
    ]

    renderScreen()

    const input = screen.getByTestId('image-upload') as HTMLInputElement

    mockedAxios.post.mockResolvedValueOnce({ data: { signed_upload_url: '' } })
    mockedAxios.put.mockResolvedValueOnce({ data: { id: '', public_url: '' } })

    await act(async () => userEvent.upload(input, files))

    expect(input.files).toHaveLength(1)
    expect(input.files?.[0]).toStrictEqual(files[0])
    expect(input.files?.[0]).toStrictEqual(files[0])
  })

  it('should remove the file, when clicking on the delete button', () => {
    mockImage = new File(['hello'], 'hello.png', { type: 'image/png' })

    renderScreen()

    const images = screen.getAllByRole('img')
    const buttons = screen.getAllByRole('button')

    expect(images).toHaveLength(1)
    userEvent.click(buttons[0])

    waitFor(() => expect(images).toHaveLength(0))
  })
})
