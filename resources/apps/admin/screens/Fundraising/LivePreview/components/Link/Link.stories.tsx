import type { Meta } from '@storybook/react'
import { Link } from './Link'

export default {
  title: 'Live Preview / Link',
  component: Link,
} as Meta<typeof Link>

export const Default = () => <Link>No Thank You</Link>
