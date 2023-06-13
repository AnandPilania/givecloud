import NMIGateway from './nmi'

class CaymanGatewayGateway extends NMIGateway {
  constructor(app) {
    super(app)

    this.$name = 'caymangateway'
    this.$displayName = 'Cayman Gateway'
  }
}

export default CaymanGatewayGateway
