import { useEffect } from 'react'
import PropTypes from 'prop-types'
import { useHistory } from 'react-router-dom'
import {
  Button,
  Column,
  Columns,
  Dialog,
  DialogHeader,
  Text,
  triggerToast,
  DialogContent,
  DialogFooter,
} from '@/aerosol'
import { Link } from '@/components/Link'
import { useDeleteFundraisingFormMutation } from './useDeleteFundraisingFormMutation'
import styles from './DeleteFormDialog.scss'

const DeleteFormDialog = ({ isOpen, onClose, formId, formName, isDefaultForm }) => {
  const history = useHistory()
  const { mutate, isLoading } = useDeleteFundraisingFormMutation(formId)

  useEffect(() => {
    return () => onClose()
  }, [])

  const handleDeleteForm = () => {
    if (isLoading) return null

    mutate(formId, {
      onSuccess: () => {
        history.push('/fundraising/forms')
        triggerToast({ type: 'success', header: `${formName} Deleted.` })
      },
      onError: () => {
        triggerToast({
          type: 'error',
          header: 'Sorry, there was a problem deleting your experience.',
          options: { autoClose: false },
        })
      },
    })
  }

  const renderSecondaryText = () =>
    isDefaultForm ? (
      <Text type='h5' isSecondaryColour>
        Looks like you're trying to delete your default experience. Set another experience as your default before
        deleting.
        <Link to={'/fundraising/forms'} className={styles.link}>
          Go to fundraising experiences
        </Link>
      </Text>
    ) : (
      <Text type='h5' isSecondaryColour>
        Are you sure you want to delete this experience?
      </Text>
    )

  return (
    <Dialog isOpen={isOpen} onClose={onClose}>
      <DialogHeader theme='error' onClose={onClose}>
        <Text type='h3' isTruncated>
          Delete {formName}
        </Text>
      </DialogHeader>
      <DialogContent>
        <Columns>
          <Column>{renderSecondaryText()}</Column>
        </Columns>
      </DialogContent>
      <DialogFooter>
        <Columns isResponsive={false} className={styles.buttonContainer}>
          <Column columnWidth='small'>
            <Button theme='error' isOutlined onClick={onClose}>
              Cancel
            </Button>
          </Column>
          <Column columnWidth='small'>
            <Button isDisabled={isDefaultForm} theme='error' isLoading={isLoading} onClick={handleDeleteForm}>
              Delete
            </Button>
          </Column>
        </Columns>
      </DialogFooter>
    </Dialog>
  )
}

DeleteFormDialog.propTypes = {
  formId: PropTypes.string,
  formName: PropTypes.string,
  isDefaultForm: PropTypes.bool,
  isOpen: PropTypes.bool,
  onClose: PropTypes.func,
}

export { DeleteFormDialog }
