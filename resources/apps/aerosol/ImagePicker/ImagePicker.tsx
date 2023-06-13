import type { FC, HTMLProps } from 'react'
import axios from 'axios'
import { useState } from 'react'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faTrash, faSpinner, faUpload } from '@fortawesome/pro-regular-svg-icons'
import { Button } from '@/aerosol/Button'
import { Label } from '@/aerosol/Label'
import { triggerToast } from '@/aerosol/Toast'
import styles from './ImagePicker.styles.scss'

type FileType = File | undefined
type ImageType = Blob | MediaSource | string
type ObjectFitType = 'contain' | 'cover'
export interface ImageData {
  id: string
  url: string
  name?: string
}

export interface RemoveImageData {
  name?: string
}

interface Props extends Omit<HTMLProps<HTMLInputElement>, 'onChange'> {
  onChange: (imageData: ImageData) => void
  image?: ImageType
  removeImage: (imageData: RemoveImageData) => void
  label?: string
  isMarginless?: boolean
  objectFit?: ObjectFitType
}

const getImage = (image: ImageType) => (typeof image === 'string' ? image : URL.createObjectURL(image))

const processUpload = async (imageFile: FileType) => {
  const signedRes = await axios.post('/jpanel/media/cdn/sign', {
    filename: imageFile?.name,
    content_type: imageFile?.type,
  })

  await axios.put(signedRes.data.signed_upload_url, imageFile)

  const completeUploadRes = await axios.post('/jpanel/media/cdn/done', {
    filename: imageFile?.name,
    content_type: imageFile?.type,
    size: imageFile?.size,
  })

  return { url: completeUploadRes.data.public_url, id: completeUploadRes.data.id }
}

const ImagePicker: FC<Props> = ({
  label,
  onChange,
  image,
  removeImage,
  isMarginless,
  id,
  name,
  objectFit = 'cover',
  ...rest
}) => {
  const [isUploading, setIsUploading] = useState(false)

  const handleNewImageUpload = async (imageFile: FileType) => {
    setIsUploading(true)
    try {
      const { url, id } = await processUpload(imageFile)
      if (id && url) onChange({ id, url, name })
    } catch (error) {
      triggerToast({
        type: 'error',
        header: 'Sorry, there was an error uploading your image. Pleas try again later.',
        options: { autoClose: false },
      })
    } finally {
      setIsUploading(false)
    }
  }

  const renderFigCaption = () => (label ? <figcaption className={styles.label}>{label}</figcaption> : null)

  const renderContent = () => {
    if (image) {
      return (
        <figure>
          {renderFigCaption()}
          <div className={styles.imageContainer}>
            <img
              className={classNames(styles.image, styles[objectFit])}
              alt={`${label ?? id} image`}
              src={getImage(image)}
            />
            <Button
              aria-label={`remove ${label ?? id} image`}
              theme='error'
              onClick={() => removeImage({ name })}
              className={styles.removeButton}
              size='small'
            >
              <FontAwesomeIcon icon={faTrash} />
            </Button>
          </div>
        </figure>
      )
    }

    const renderIcon = () => {
      if (isUploading) {
        return (
          <span role='status'>
            <FontAwesomeIcon icon={faSpinner} spin />
            <span className='sr-only'>`Uploading {label ?? id}`</span>
          </span>
        )
      }

      return <FontAwesomeIcon icon={faUpload} className={styles.icon} />
    }

    const renderLabel = () => (label ? <Label>{label}</Label> : null)

    return (
      <label htmlFor={id}>
        {renderLabel()}
        <div className={styles.imagePicker}>
          {renderIcon()}
          <input
            {...rest}
            data-testid='image-upload'
            type='file'
            id={id}
            accept='image/*'
            onChange={({ target }) => handleNewImageUpload(target.files?.[0])}
            className='sr-only'
          />
        </div>
      </label>
    )
  }

  return <div className={classNames(styles.root, !isMarginless && styles.marginBottom)}>{renderContent()}</div>
}

export { ImagePicker }
