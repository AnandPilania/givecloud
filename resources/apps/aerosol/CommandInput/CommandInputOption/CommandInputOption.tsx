import type { FC, ReactNode } from 'react'
import classNames from 'classnames'
import { Combobox } from '@headlessui/react'
import { Text } from '@/aerosol/Text'
import styles from './CommandInputOption.styles.scss'

type ValueType = string | object

interface Props {
  value: ValueType
  children: ReactNode
}

const CommandInputOption: FC<Props> = ({ value, children }) => {
  return (
    <Combobox.Option className={({ active }) => classNames(styles.root, active && styles.active)} value={value}>
      {({ selected }) => (
        <Text isTruncated isMarginless isBold={selected}>
          {children}
        </Text>
      )}
    </Combobox.Option>
  )
}

export { CommandInputOption }
