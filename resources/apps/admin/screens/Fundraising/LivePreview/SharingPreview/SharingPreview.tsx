import type { FC, HTMLProps, MutableRefObject } from 'react'
import classNames from 'classnames'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './SharingPreview.styles.scss'

interface Props extends HTMLProps<HTMLDivElement> {
  isHovered?: boolean
  isTitleFocused?: boolean
  isDescriptionFocused?: boolean
  titleOnClick?: () => void
  descriptionOnClick?: () => void
}

const SharingPreview: FC<Props> = ({
  className,
  isHovered,
  titleOnClick,
  isTitleFocused,
  isDescriptionFocused,
  descriptionOnClick,
}) => {
  const {
    sharingValue: { socialPreviewImage, socialLinkTitle, socialLinkDescription },
  } = useFundraisingFormState()

  const renderImg = () =>
    socialPreviewImage.full ? (
      <div className={styles.previewImageWrapper}>
        <div className={styles.previewImageContainer}>
          <img src={socialPreviewImage.full} alt='' className={styles.previewImage} />
        </div>
      </div>
    ) : (
      <div className={styles.imgPlaceholder} />
    )

  const renderTitleOverlay = () =>
    isHovered || isTitleFocused ? <div className={styles.overlay} onClick={titleOnClick} /> : null
  const renderDescriptionOverlay = () =>
    isHovered || isDescriptionFocused ? <div className={styles.overlay} onClick={descriptionOnClick} /> : null

  return (
    <div className={classNames(styles.root, className)} aria-hidden='true'>
      {renderImg()}
      <div className='p-4'>
        <div className={styles.title}>
          {socialLinkTitle}
          {renderTitleOverlay()}
        </div>
        <div className={styles.description}>
          {socialLinkDescription}
          {renderDescriptionOverlay()}
        </div>
      </div>
    </div>
  )
}

export { SharingPreview }
