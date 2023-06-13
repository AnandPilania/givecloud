import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import styles from '@/fields/Label/Label.scss'

const Label = ({ children, error = '', title = '' }) => {
  const { theme } = useContext(StoreContext)
  const isLightTheme = theme === 'light'

  return (
    <div className={styles.root}>
      <label className={styles.label}>
        {title && <div className={styles.title}>{title}</div>}

        <div className={classnames(styles.content, !isLightTheme && styles.dark)}>
          {children}

          {!!error && (
            <div className={styles.errorIconContainer}>
              <svg className={styles.errorIcon} fill='currentColor' viewBox='0 0 20 20'>
                <path
                  fillRule='evenodd'
                  d='M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z'
                  clipRule='evenodd'
                />
              </svg>
            </div>
          )}
        </div>
      </label>

      {!!error && <div className={styles.errorText}>{error}</div>}
    </div>
  )
}

Label.propTypes = {
  error: PropTypes.string,
  title: PropTypes.string,
  children: PropTypes.node,
}

export default memo(Label)
