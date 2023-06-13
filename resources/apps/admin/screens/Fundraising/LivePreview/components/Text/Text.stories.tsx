import { Text } from './Text'

export default {
  title: 'Live Preview / Text',
  component: Text,
}

export const Default = () => <Text>Default Text</Text>

export const Large = () => <Text isLarge>Bigger Text</Text>
