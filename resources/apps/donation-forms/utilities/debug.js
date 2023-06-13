import { noop } from 'lodash'

const isProduction = process.env.NODE_ENV === 'production'

export const devLog = isProduction ? noop : console.log
