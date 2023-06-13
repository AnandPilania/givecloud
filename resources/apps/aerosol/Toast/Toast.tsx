import type { FC, ReactNode } from 'react'
import type { TypeOptions } from 'react-toastify'
import type { LocationDescriptor } from 'history'
import classnames from 'classnames'
import { Text } from '@/aerosol/Text'
import { ToastButton } from './ToastButton'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './Toast.styles.scss'

export interface ToastButtonProps {
  to?: LocationDescriptor
  'aria-label'?: string
  children?: ReactNode
}

interface Props {
  header: string
  type?: TypeOptions
  description?: string
  buttonProps?: ToastButtonProps
}

const Toast: FC<Props> = ({ type = 'success', header, description, buttonProps }) => {
  const { small } = useTailwindBreakpoints()
  const renderDescription = () => {
    if (description) {
      return (
        <Text type='h5' isMarginless className={styles.text}>
          {description}
        </Text>
      )
    }
    return null
  }

  const renderCTA = () => {
    if (buttonProps?.children) {
      return (
        <ToastButton
          {...buttonProps}
          theme={type}
          isFullWidth={small.lessThan}
          className={classnames(small.lessThan && 'mt-2')}
        />
      )
    }
    return null
  }

  return (
    <div className={styles.root}>
      <div className={styles.wrapper}>
        <div className={styles.textContent} data-testid={`toast-${type}`}>
          <Text type='h5' isBold isTruncated isMarginless className={styles.text}>
            {header}
          </Text>
          {renderDescription()}
        </div>
        {renderCTA()}
      </div>
    </div>
  )
}

Toast.defaultProps = {
  type: 'success',
  buttonProps: {},
}

export { Toast }
