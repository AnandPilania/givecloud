import NMIGateway from './nmi'

class SafeSaveGateway extends NMIGateway {
  constructor(app) {
    super(app)

    this.$name = 'safesave'
    this.$displayName = 'SafeSave Payment Services'
  }
}

export default SafeSaveGateway
