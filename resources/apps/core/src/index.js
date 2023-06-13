const Givecloud = require('./givecloud').default

const instance = new Givecloud()

window['Givecloud'] = window['GiveCloud'] = instance

module.exports = instance
