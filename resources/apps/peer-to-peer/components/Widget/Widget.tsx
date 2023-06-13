import type { FC, PropsWithChildren } from 'react'
import { Box } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './Widget.styles.scss'

const Widget: FC<PropsWithChildren> = ({ children }) => {
  const { medium } = useTailwindBreakpoints()
  return (
    <div className={styles.root}>
      <Box isPaddingless className='h-full' isFullscreen={medium.lessThan} isMarginless>
        {children}
      </Box>
    </div>
  )
}

export { Widget }
