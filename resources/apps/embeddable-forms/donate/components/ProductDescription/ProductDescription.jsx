import { memo, useContext } from 'react'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import styles from '@/components/ProductDescription/ProductDescription.scss'

const ProductDescription = () => {
  const { theme, title, summary } = useContext(StoreContext)
  const isLightTheme = theme === 'light'

  if (!title && !summary) {
    return null
  }

  return (
    <div className={classnames(styles.root, isLightTheme && styles.light)}>
      {!!title && <p className={styles.title}>{title}</p>}

      {!!summary && <p>{summary}</p>}
    </div>
  )
}

export default memo(ProductDescription)
