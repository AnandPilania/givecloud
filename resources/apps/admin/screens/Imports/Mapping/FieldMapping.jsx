import { useState, useCallback, useEffect } from 'react'
import { useParams } from 'react-router-dom'
import useApiQuery from '@/hooks/useApiQuery'
import FieldList from '@/screens/Imports/Mapping/FieldList'
import FieldColumnModal from '@/screens/Imports/Mapping/FieldColumnModal'
import AdvanceButton from '@/screens/Imports/AdvanceButton'
import { until } from '@open-draft/until'

const FieldMapping = ({ fileInfo }) => {
  const [isError, setIsError] = useState(false)
  const [showModal, setShowModal] = useState(false)
  const [modalField, setModalField] = useState(false)
  const [mappables, setMappables] = useState(fileInfo.data)
  const [shouldRevalidateField, setShouldRevalidateFields] = useState(true)
  const [requiredFieldsAreMapped, setRequiredFieldsAreMapped] = useState(true)

  const { id } = useParams()

  const apiQuery = useApiQuery()

  const revalidateMappedFields = useCallback(() => {
    setShouldRevalidateFields(false)
    mappables.map((field) => {
      field.mappedTo && handleFieldValidation(field)
    })
  }, [mappables])

  useEffect(() => {
    shouldRevalidateField && revalidateMappedFields()
  }, [revalidateMappedFields, shouldRevalidateField])

  const handleDetailsClick = useCallback((field) => {
    setModalField(field)
    setShowModal(true)
  }, [])

  const handleFieldValidation = useCallback(
    async (field) => {
      setMappables((mappables) => {
        return mappables.map((map) => {
          return map.id === field.id ? { ...field, ...{ shouldBeValidated: true } } : { ...map }
        })
      })

      const { error, data } = await until(() => {
        return apiQuery.post(`imports/${id}/validate`, {
          fieldId: field.id,
          mappedTo: field.mappedTo,
        })
      })
      let validationData = {
        shouldBeValidated: false,
        ...data?.data,
      }
      if (error) {
        validationData = {
          hasErrors: true,
          message: error.message ?? error.error,
          shouldBeValidated: false,
        }
      }

      setMappables((mappables) => {
        return mappables.map((map) => {
          return map.id === field.id ? { ...map, ...validationData } : { ...map }
        })
      })
    },
    [apiQuery, setMappables]
  )

  return (
    <>
      <div className='text-md text-gray-400'>{fileInfo?.import?.file_name}</div>
      {!!isError && <div>An error occured, please try again.</div>}

      {!isError && (
        <FieldList
          mappables={mappables}
          setMappables={setMappables}
          fileInfo={fileInfo}
          handleFieldValidation={handleFieldValidation}
          handleDetailsClick={handleDetailsClick}
        />
      )}
      {showModal && <FieldColumnModal setShowModal={setShowModal} fileInfo={fileInfo} field={modalField} />}

      <AdvanceButton to='analyze' title='Analyse & Review' isEnabled={requiredFieldsAreMapped} />
    </>
  )
}

export default FieldMapping
