import type { FC } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinner } from '@fortawesome/free-solid-svg-icons'
import styles from './VisualEditorLoader.styles.scss'

const VisualEditorLoader: FC = () => (
  <div data-testid='loading' className={styles.root} aria-live='assertive' aria-busy='true'>
    <FontAwesomeIcon icon={faSpinner} className='text-4xl text-white' spin />
  </div>
)

export { VisualEditorLoader }
