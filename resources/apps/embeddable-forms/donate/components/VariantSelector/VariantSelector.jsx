import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Variant from '@/components/Variant/Variant'
import styles from '@/components/VariantSelector/VariantSelector.scss'

const VariantSelector = () => {
  const { variants } = useContext(StoreContext)
  const isSingleVariant = variants.all.length <= 1

  if (isSingleVariant) {
    return null
  }

  return (
    <div className={styles.root}>
      {variants.all.map((variant) => (
        <Variant key={variant.id} variant={variant} />
      ))}
    </div>
  )
}

export default memo(VariantSelector)
