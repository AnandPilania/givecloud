import Endpoint from '@core/endpoint'

class PledgeCampaignsEndpoint extends Endpoint {
  createPledge(id, data) {
    return this.$http('POST', `pledge-campaigns/${id}/pledge`, data).then((data) => {
      return Promise.resolve(data.pledge)
    })
  }
}

export default PledgeCampaignsEndpoint
