import { useState } from 'react'

export default function Summary({ fileInfo }) {
  const [showError, setShowError] = useState(false)
  return (
    <>
      <p>{fileInfo.import.added_records} records added.</p>
      <p>{fileInfo.import.updated_records} records updated.</p>
      <p>{fileInfo.import.error_records} records errored.</p>
      <p>
        <button
          onClick={() => {
            setShowError(() => {
              return !showError
            })
          }}
        >
          View full summary
        </button>
      </p>

      {showError && (
        <p className='text-left'>
          {fileInfo.import?.import_messages?.split('\n').map((line, key) => {
            return (
              <span key={key}>
                {line}
                <br />
              </span>
            )
          })}
        </p>
      )}
    </>
  )
}
