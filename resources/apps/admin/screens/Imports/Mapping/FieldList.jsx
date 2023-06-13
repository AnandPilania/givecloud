import Field from '@/screens/Imports/Mapping/Field'

const FieldList = ({ mappables, fileInfo, setMappables, handleFieldValidation, handleDetailsClick }) => {
  return (
    <div className='mt-12'>
      {mappables.map((field) => {
        return (
          <Field
            key={field.id}
            field={field}
            setMappables={setMappables}
            handleFieldValidation={handleFieldValidation}
            handleDetailsClick={handleDetailsClick}
            fileInfo={fileInfo}
          />
        )
      })}
    </div>
  )
}

export default FieldList
