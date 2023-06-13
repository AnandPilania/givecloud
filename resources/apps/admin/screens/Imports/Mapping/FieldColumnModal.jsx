import { Fragment, useMemo } from 'react'
import { Dialog, Transition } from '@headlessui/react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faTimes } from '@fortawesome/pro-regular-svg-icons'

export default function FieldColumnModal({ fileInfo, setShowModal, field }) {
  const headerForField = useMemo(() => {
    if (!fileInfo.import.file_has_headers) {
      return false
    }
    return fileInfo.sheet.headers.find((header) => {
      return header.column === field.mappedTo
    })
  }, [fileInfo.import.file_has_headers, field.mappedTo])

  console.log(headerForField, field)

  return (
    <Transition.Root show={true} as={Fragment}>
      <Dialog
        as='div'
        className='fixed z-10 inset-0 overflow-y-auto'
        onClose={() => {
          setShowModal(false)
        }}
      >
        <div className='flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0'>
          <Transition.Child
            as={Fragment}
            enter='ease-out duration-300'
            enterFrom='opacity-0'
            enterTo='opacity-100'
            leave='ease-in duration-200'
            leaveFrom='opacity-100'
            leaveTo='opacity-0'
          >
            <Dialog.Overlay className='fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity' />
          </Transition.Child>

          {/* This element is to trick the browser into centering the modal contents. */}
          <span className='hidden sm:inline-block sm:align-middle sm:h-screen' aria-hidden='true'>
            &#8203;
          </span>
          <Transition.Child
            as={Fragment}
            enter='ease-out duration-300'
            enterFrom='opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95'
            enterTo='opacity-100 translate-y-0 sm:scale-100'
            leave='ease-in duration-200'
            leaveFrom='opacity-100 translate-y-0 sm:scale-100'
            leaveTo='opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95'
          >
            <div className='inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6'>
              <div className='hidden sm:block absolute top-0 right-0 pt-4 pr-4'>
                <button
                  type='button'
                  className='bg-white rounded-md text-gray-400 hover:text-gray-500'
                  onClick={() => setShowModal(false)}
                >
                  <span className='sr-only'>Close</span>
                  <FontAwesomeIcon icon={faTimes} className='h-6 w-6 text-gray-500' />
                </button>
              </div>
              <div className='sm:flex sm:items-start'>
                <div className='mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left'>
                  <Dialog.Title as='h2' className='text-2xl leading-6 font-bold text-gray-900'>
                    {field.name}
                  </Dialog.Title>
                  <div className='mt-2'>
                    <p className='text-xl text-gray-500'>{field.hint}</p>
                  </div>
                </div>
              </div>
              <div className='my-8'>
                <div className='bg-white overflow-hidden m-auto max-w-md shadow-lg rounded-lg divide-y divide-gray-200'>
                  <div className='bg-gray-50 px-4 py-5 sm:px-6 text-lg'>
                    <p className='text-sm text-gray-500'>Column {field.mappedTo}</p>
                    {!!headerForField && (
                      <p className='text-lg mt-1 text-gray-900 leading-6'>{headerForField.header}</p>
                    )}
                  </div>
                  <div className='px-0 py-0'>
                    <ul role='list' className='list-none divide-y divide-gray-200'>
                      {field.filtered.map((row, index) => {
                        return (
                          <li
                            key={index}
                            className='relative bg-white px-4  py-5 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600'
                          >
                            <div className='flex justify-between space-x-3'>
                              <div className='min-w-0 flex-1'>
                                <span className='absolute inset-0' aria-hidden='true' />
                                <p className='text-sm font-medium text-gray-900 truncate'>{row.data}</p>
                              </div>
                              <div className='flex-shrink-0 whitespace-nowrap text-sm text-gray-500'>
                                Row {index + 1}
                              </div>
                            </div>
                            {!!row.hasErrors && (
                              <div className='mt-1'>
                                <p className='line-clamp-2 text-sm text-red-700'>{row.message}</p>
                              </div>
                            )}
                          </li>
                        )
                      })}
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </Transition.Child>
        </div>
      </Dialog>
    </Transition.Root>
  )
}
