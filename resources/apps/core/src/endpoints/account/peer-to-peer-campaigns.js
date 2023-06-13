import Endpoint from '@core/endpoint'

class PeerToPeerCampaignsEndpoint extends Endpoint {
  create(data) {
    return this.$http('POST', `account/peer-to-peer-campaigns`, data).then((data) => {
      return Promise.resolve(data.fundraising_page)
    })
  }

  get(hashid) {
    return this.$http('GET', `account/peer-to-peer-campaigns/${hashid}`).then((data) => {
      return Promise.resolve(data.fundraising_page)
    })
  }

  update(hashid, data) {
    return this.$http('PATCH', `account/peer-to-peer-campaigns/${hashid}`, data).then((data) => {
      return Promise.resolve(data.fundraising_page)
    })
  }

  join(hashid, data) {
    return this.$http('POST', `account/peer-to-peer-campaigns/${hashid}/join`, data).then((data) => {
      return Promise.resolve(data.fundraising_page)
    })
  }
}

export default PeerToPeerCampaignsEndpoint
