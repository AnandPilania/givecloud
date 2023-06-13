import usePageTitle from '@/hooks/usePageTitle'
import styles from './PageNotFound.scss'

const PageNotFound = () => {
  usePageTitle('Page not found')

  return (
    <div className={styles.root}>
      <div className={styles.content}>
        <h2 className={styles.heading} aria-label='Not found heading'>
          Error 404
        </h2>

        <h1 className={styles.message} aria-label='Not found message'>
          Resource not found
        </h1>

        <h2 className={styles.description} aria-label='Not found description'>
          The requested resource could not be found but may be available again in the future.
        </h2>
      </div>
    </div>
  )
}

export { PageNotFound }
