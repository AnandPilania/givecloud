import { renderHook } from '@testing-library/react-hooks'
import useOnClickOutside from '@/hooks/useOnClickOutside'

test('calls provided callback if clicked outside of scope of ref', () => {
  const onClickOutside = jest.fn()
  const ref = { contains: () => false }

  renderHook(() =>
    useOnClickOutside({
      ref,
      onClickOutside,
    })
  )

  document.dispatchEvent(new Event('click'))

  expect(onClickOutside.mock.calls).toHaveLength(1)
})

test('does not call provided callback if clicked inside the scope of the ref', () => {
  const onClickOutside = jest.fn()
  const ref = { contains: () => true }

  renderHook(() =>
    useOnClickOutside({
      ref,
      onClickOutside,
    })
  )

  document.dispatchEvent(new Event('click'))

  expect(onClickOutside.mock.calls).toHaveLength(0)
})

test('does not call provided callback if no ref', () => {
  const onClickOutside = jest.fn()

  renderHook(() =>
    useOnClickOutside({
      onClickOutside,
    })
  )

  expect(onClickOutside.mock.calls).toHaveLength(0)
})

test('removes click event listener on cleanup', () => {
  const onClickOutside = jest.fn()
  const ref = { contains: () => false }

  const { unmount } = renderHook(() =>
    useOnClickOutside({
      ref,
      onClickOutside,
    })
  )

  document.dispatchEvent(new Event('click'))

  unmount()

  document.dispatchEvent(new Event('click'))

  expect(onClickOutside.mock.calls).toHaveLength(1)
})
