import getConfig from '@/utilities/config'

export const getDefaultPath = ({ standard, simplified }) => {
  const config = getConfig()

  const layoutPaths = { standard, simplified }

  return layoutPaths[config.layout]
}
