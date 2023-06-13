import { isIE } from 'react-device-detect'

const isCompatibleOS = () => {
  if (isIE) {
    return false
  }

  if (typeof window !== 'undefined' && typeof window.navigator !== 'undefined') {
    if (/iP(hone|od|ad)/.test(window.navigator.platform)) {
      // supports iOS 2.0 and later: <http://bit.ly/TJjs1V>
      const navigatorVersion = window.navigator.appVersion.match(/OS (\d+)_(\d+)_?(\d+)?/)

      if (parseInt(navigatorVersion[1], 10) <= 9) {
        return false
      }
    }
  }

  return true
}

export default isCompatibleOS
