import type { FC, HTMLProps } from 'react'
import classNames from 'classnames'
import { PRIMARY, ERROR } from '@/shared/constants/theme'
import styles from './ProgressBar.styles.scss'

type Theme = typeof PRIMARY | typeof ERROR

interface Props extends HTMLProps<HTMLDivElement> {
  completion: number
  theme: Theme
}
const ProgressBar: FC<Props> = ({ completion, theme, className, ...rest }) => {
  return (
    <div role='progressbar' {...rest} className={classNames(styles.root, className)}>
      <div
        className={classNames(styles.bar, styles[theme])}
        style={{
          width: `${completion}%`,
        }}
      >
        <span className='sr-only'>completed: {completion}%</span>
      </div>
    </div>
  )
}

ProgressBar.defaultProps = {
  completion: 0,
  theme: PRIMARY,
}

export { ProgressBar }
