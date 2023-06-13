const toWindowFeatures = (features) => {
  const keyPairs = []

  // position the window in the center of the parent window
  features.top = window.top.outerHeight / 2 + window.top.screenY - features.height / 2
  features.left = window.top.outerWidth / 2 + window.top.screenX - features.width / 2

  Object.keys(features).forEach((name) => keyPairs.push(`${name}=${features[name]}`))

  return keyPairs.join(',')
}

export const openNewWindow = ({ url, name = '_blank', features = { width: 500, height: 600 }, onClose }) => {
  let windowUrl = url
  let resolveWindowUrl = null

  if (typeof url === 'function') {
    windowUrl = 'about:blank'
    resolveWindowUrl = url
  }

  const newWindow = window.open(windowUrl, name, toWindowFeatures(features))

  if (!newWindow) {
    console.warn('A new window could not be opened. Maybe it was blocked.')
    return
  }

  let windowClosed = false
  let windowCheckerInterval = null

  const releaseWindow = () => {
    if (windowClosed === false) {
      windowClosed = true
      clearInterval(windowCheckerInterval)
      onClose?.()
    }
  }

  const releaseClosedOrDestroyedWindow = () => {
    if (!newWindow || newWindow.closed) releaseWindow()
  }

  // when a new window uses content from a cross-origin there's no way to attach an event
  // to it. as a result we need to poll an check if the window has been closed or destroyed
  windowCheckerInterval = setInterval(releaseClosedOrDestroyedWindow, 50)

  if (resolveWindowUrl) {
    resolveWindowUrl().then((url) => (newWindow.location = url))
  }

  return newWindow
}
