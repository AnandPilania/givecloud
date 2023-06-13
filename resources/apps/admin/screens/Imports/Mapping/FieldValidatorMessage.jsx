const FieldValidatorMessage = ({ field, handleDetailsClick }) => {
  return (
    <p className={`mt-2 text-sm  text-right text-red-600`}>
      {field.message ?? 'Some errors were issued, please review.'}
      {!field.message && (
        <button
          onClick={() => {
            handleDetailsClick(field)
          }}
          className='ml-2 underline'
        >
          Details
        </button>
      )}
    </p>
  )
}

export default FieldValidatorMessage
