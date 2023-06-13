import { Fragment, useCallback } from 'react'
import { Listbox, Transition } from '@headlessui/react'
import FieldValidatorIcon from '@/screens/Imports/Mapping/FieldValidatorIcon'
import FieldValidatorMessage from '@/screens/Imports/Mapping/FieldValidatorMessage'
import classNames from 'classnames'
import { CheckIcon } from '@heroicons/react/outline'
import useApiQuery from '@/hooks/useApiQuery'
import { useParams } from 'react-router-dom'
import { until } from '@open-draft/until'

const getMappedColumnForField = (field, sheetColumns) => {
  return sheetColumns.find((sheetColumn) => sheetColumn.column === field.mappedTo)
}

const Field = ({ field, fileInfo, setMappables, handleFieldValidation, handleDetailsClick }) => {
  const apiUrl = useApiQuery()
  const { id } = useParams()

  const mappedColumnForField = getMappedColumnForField(field, fileInfo.sheet.headers)

  const mapField = useCallback(
    async (field, mappedToColumn) => {
      const oldFieldDefinition = field

      const newFieldDefinition = {
        ...field,
        ...{
          hasErrors: false,
          mappedTo: mappedToColumn,
          message: null,
          shouldBeValidated: mappedToColumn ? true : null,
        },
      }
      setMappables((mappables) => {
        return mappables.map((map) => {
          return map.id === field.id ? { ...newFieldDefinition } : { ...map }
        })
      })

      const { error } = await until(() =>
        apiUrl.post(`imports/${id}/field`, {
          fieldId: field.id,
          mappedToColumn: mappedToColumn,
        })
      )
      if (error) {
        const errors = {
          message: error.message,
          hasErrors: true,
          shouldBeValidated: mappedToColumn ? false : null,
        }
        setMappables((mappables) => {
          return mappables.map((map) => {
            return map.id === field.id ? { ...oldFieldDefinition, ...errors } : { ...map }
          })
        })
      } else {
        newFieldDefinition.mappedTo && handleFieldValidation(newFieldDefinition)
      }
    },
    [field.id]
  )

  return (
    <Listbox
      value={mappedColumnForField}
      disabled={fileInfo.import.stage === 'done'}
      onChange={(value) => {
        mapField(field, value.column)
      }}
    >
      {({ open }) => (
        <div className='grid grid-cols-2 grid-gap-6 mb-8'>
          <Listbox.Label className='block text-left'>
            <div className='text-lg font-bold text-black'>{field.name}</div>
            <div className='text-gray-400 text-sm'>{field.hint}</div>
          </Listbox.Label>
          <div className='mt-1 relative'>
            <Listbox.Button className='relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm'>
              <span className='w-full inline-flex truncate'>
                <span className='text-gray-500'>{mappedColumnForField?.column}</span>
                <span className='ml-2 truncate'>{mappedColumnForField?.header}</span>
              </span>
              <span className='absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none'>
                <FieldValidatorIcon
                  loading={field.shouldBeValidated}
                  isValid={!field.hasErrors}
                  hasState={!!field.mappedTo}
                />
              </span>
            </Listbox.Button>

            <Transition
              show={open}
              as={Fragment}
              leave='transition ease-in duration-100'
              leaveFrom='opacity-100'
              leaveTo='opacity-0'
            >
              <Listbox.Options className='list-none	absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md pl-0 py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm'>
                {mappedColumnForField && (
                  <Listbox.Option
                    value={{ column: null, value: null }}
                    className='cursor-default select-none relative py-2 pl-3 pr-9'
                  >
                    <div className='flex'>
                      <span className='text-gray-500'>Don't map</span>
                    </div>
                  </Listbox.Option>
                )}
                {fileInfo.sheet.headers.map((header) => (
                  <Listbox.Option
                    key={header.column}
                    className={({ active }) =>
                      classNames(
                        active ? 'text-white bg-indigo-600' : 'text-gray-900',
                        'cursor-default select-none relative py-2 pl-3 pr-9'
                      )
                    }
                    value={header}
                  >
                    {({ selected, active }) => (
                      <>
                        <div className='flex'>
                          <span className={classNames(active ? 'text-indigo-200' : 'text-gray-500', 'truncate')}>
                            {header.column}
                          </span>
                          <span className={classNames(selected ? 'font-semibold' : 'font-normal', 'ml-2 truncate')}>
                            {header.header}{' '}
                            <span className='text-gray-400 text-sm text-ellipsis overflow-hidden'>
                              &lt;
                              {header.rows
                                .slice(0, 3)
                                .filter((n) => n !== '')
                                .join(', ')}
                              &gt;
                            </span>
                          </span>
                        </div>

                        {selected ? (
                          <span
                            className={classNames(
                              active ? 'text-white' : 'text-indigo-600',
                              'absolute inset-y-0 right-0 flex items-center pr-4'
                            )}
                          >
                            <CheckIcon className='h-5 w-5' />
                          </span>
                        ) : null}
                      </>
                    )}
                  </Listbox.Option>
                ))}
              </Listbox.Options>
            </Transition>
            {field.hasErrors && <FieldValidatorMessage field={field} handleDetailsClick={handleDetailsClick} />}
          </div>
        </div>
      )}
    </Listbox>
  )
}

export default Field
