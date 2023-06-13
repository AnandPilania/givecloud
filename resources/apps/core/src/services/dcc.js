import { round } from '@core/utils'

class DccService {
  constructor(app) {
    this.$app = app
  }

  getCost(amount, type) {
    if (!this.$app.config.processing_fees.using_ai) {
      type = 'more_costs'
    }

    return this.getCosts(amount)?.[type] || 0
  }

  getCosts(amount) {
    const [minimumCosts, moreCosts, mostCosts] = this.$getCosts(amount)

    return {
      minimum_costs: minimumCosts,
      more_costs: moreCosts,
      most_costs: mostCosts,
    }
  }

  $getCosts(amount) {
    const fees = this.$app.config.processing_fees

    if (!fees.using_ai) {
      const costs = round(fees.amount + (amount * fees.rate) / 100, 2)

      return [costs, costs, costs]
    }

    const minimalCosts = Math.max(1.56, round(amount * 0.07, 2) + 0.79)
    const moreCosts = this.$normalizeCosts(round(minimalCosts * 1.35, 2))
    const mostCosts = round(moreCosts * 1.35, 2)

    return [minimalCosts, moreCosts, mostCosts]
  }

  $normalizeCosts(costs) {
    if (costs < 5.9) {
      return this.$normalizeCostsWithinRange(costs, 5, 5.89)
    }

    if (parseInt(costs, 10) % 10 === 0) {
      return this.$normalizeCostsWithinRange(costs, Math.floor(costs), Math.floor(costs) + 0.49)
    }

    return costs
  }

  $normalizeCostsWithinRange(costs, min, max) {
    if (costs < min || costs > max) {
      return costs
    }

    const baseCostsOffset = 0.1

    const baseCosts = min - baseCostsOffset
    const squashedCostsInRange = round(((costs - min) / (max - min)) * (baseCostsOffset - 0.01), 2)

    return baseCosts + squashedCostsInRange
  }
}

export default DccService
