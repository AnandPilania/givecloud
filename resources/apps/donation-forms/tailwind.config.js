const baseConfig = require('../../../tailwind.config')

module.exports = {
  ...baseConfig,
  corePlugins: { preflight: true },
}
