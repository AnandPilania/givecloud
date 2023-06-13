import getConfig from './config'

const config = getConfig()
const baseUrl = config.asset_url.replace(/^(https?:\/\/[^/]+)?.*$/, '$1')

export const assetUrl = (path) => {
  if (path[0] === '/') {
    return baseUrl + path
  }

  return `${config.asset_url}/${path}`
}
