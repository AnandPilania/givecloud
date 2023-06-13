const path = require('path')
const moduleAliases = require('./moduleAliases')

/*
moduleAliases is an abstration in order to have a single source
of truth for our module aliases for all apps in both jest and webpack.
*/
const buildModuleAliases = (dir) => {
  return Object.keys(moduleAliases).reduce((result, key) => {
    result[key] = path.resolve(dir, moduleAliases[key])

    return result
  }, {})
}

module.exports = buildModuleAliases
