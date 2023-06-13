import type { FC, ReactNode } from 'react'
import type { ThemeType } from '@/shared/constants/theme'
import type { ColumnProps } from '@/aerosol/Column'
import type { ColumnsProps } from '@/aerosol/Columns'
import classNames from 'classnames'
import { faTimes } from '@fortawesome/pro-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { Button } from '@/aerosol/Button'
import { Column } from '@/aerosol/Column'
import { Columns } from '@/aerosol/Columns'
import styles from './DialogHeader.styles.scss'

interface Props extends Pick<ColumnsProps, 'isMarginless'>, Pick<ColumnProps, 'isPaddingless'> {
  children?: ReactNode
  onClose: () => void
  theme?: ThemeType
}

const DialogHeader: FC<Props> = ({ children, onClose, isPaddingless, isMarginless, theme = 'primary', ...rest }) => {
  const renderChildren = () =>
    children ? (
      <Column isPaddingless={isPaddingless} columnWidth='one'>
        {children}
      </Column>
    ) : null

  return (
    <Columns
      isResponsive={false}
      isStackingOnMobile={false}
      isMarginless={isMarginless}
      className={styles.root}
      {...rest}
    >
      {renderChildren()}
      <Column isPaddingless={isPaddingless} columnWidth={children ? 'small' : 'six'}>
        <Button
          theme={theme}
          aria-label='close dialog'
          isClean
          onClick={onClose}
          className={classNames(!children && 'self-end')}
        >
          <FontAwesomeIcon icon={faTimes} />
        </Button>
      </Column>
    </Columns>
  )
}

export { DialogHeader }
