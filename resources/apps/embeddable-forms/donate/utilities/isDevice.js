import { isAndroid, isWinPhone, isIOS } from 'react-device-detect'

const isDevice = () => {
  return isAndroid || isWinPhone || isIOS ? true : false
}

export default isDevice
