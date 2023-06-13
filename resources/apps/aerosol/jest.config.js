const buildJestModuleNameMapper = require('../buildJestModuleNameMapper')
module.exports = {
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['./jest.setup.js', '../jest.setup.js'],
  moduleNameMapper: buildJestModuleNameMapper(),
  transform: {
    '^.+\\.jsx?$': ['babel-jest', { rootMode: 'upward' }],
    '^.+\\.tsx?$': ['ts-jest'],
  },
  testEnvironmentOptions: {
    url: 'http://localhost/jpanel',
  },
}
