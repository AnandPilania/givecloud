const FieldValidationError = ({ errors }) => {
  return errors.map((error) => {
    {
      ;`<span>An error occurred on line ${error.line}: ${error.message} (Value was : ${error.value})`
    }
  })
}
export default FieldValidationError
