import { setupServer } from 'msw/node'
import { createGetters } from '@/mocks/handlers'

const server = setupServer(...createGetters())

export { server }
