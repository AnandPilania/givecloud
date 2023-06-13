import type { FC, ComponentPropsWithRef } from 'react'
import { forwardRef } from 'react'
import classnames from 'classnames'
import styles from './CodeInput.styles.scss'

type Props = ComponentPropsWithRef<'input'>

const CodeInput: FC<Props> = forwardRef(({ className, ...rest }, ref) => {
  return <input {...rest} className={classnames(styles.input, className)} maxLength={1} type='text' ref={ref} />
})

CodeInput.displayName = 'CodeInput'

export { CodeInput }
export { Props as CodeInputProps }
