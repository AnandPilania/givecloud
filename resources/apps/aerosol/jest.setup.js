window.ResizeObserver = jest.fn().mockImplementation(() => ({
  observe: () => null,
  disconnect: () => null,
  unobserve: () => null,
}))
