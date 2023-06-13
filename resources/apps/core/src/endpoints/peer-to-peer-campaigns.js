import Endpoint from '@core/endpoint'

class PeerToPeerCampaignsEndpoint extends Endpoint {
  get(hashid) {
    return this.$http('GET', `peer-to-peer-campaigns/${hashid}`).then((data) => {
      return Promise.resolve(data.fundraising_page)
    })
  }

  validateTeamJoinCode(hashid, code) {
    return this.$http('POST', `peer-to-peer-campaigns/${hashid}/validate-team-join-code`, { code }).then((data) => {
      return Promise.resolve(data.valid)
    })
  }
}

export default PeerToPeerCampaignsEndpoint
