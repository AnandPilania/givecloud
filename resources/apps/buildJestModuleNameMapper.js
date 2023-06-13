const moduleAliases = require('./moduleAliases')

/*
moduleAliases is an abstration in order to have a single source
of truth for our module aliases for both jest and webpack.
*/
const buildModuleNameMapper = () => {
  return Object.keys(moduleAliases).reduce(
    (result, key) => {
      result[`^${key}(.*)$`] = `<rootDir>/${moduleAliases[key]}$1`

      return result
    },
    {
      '\\.scss$': 'identity-obj-proxy',
      '\\.svg\\?react$': '<rootDir>/mocks/svg.js',
      givecloud: '<rootDir>/mocks/givecloud.js',
    }
  )
}

module.exports = buildModuleNameMapper
