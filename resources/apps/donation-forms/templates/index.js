import amountTilesRoutes from '@/templates/AmountTiles/routes'
import standardRoutes from '@/templates/Standard/routes'
import getConfig from '@/utilities/config'

const config = getConfig()

const routeMapping = {
  amount_tiles: amountTilesRoutes,
  standard: standardRoutes,
}

export const templateRoutes = routeMapping[config.template] ?? standardRoutes
