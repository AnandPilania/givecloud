import { Fragment, useState } from 'react'
import classNames from 'classnames'
import formatDate from '@/utilities/formatDate'
import { Listbox, Transition } from '@headlessui/react'
import { CheckIcon, ChevronDownIcon } from '@heroicons/react/solid'

function months() {
  let months = []

  for (const i of Array(13).keys()) {
    let copy = new Date()
    months.push(copy.setMonth(copy.getMonth() - i))
  }

  return months
}

const lastMonths = months()

const MonthsDropdown = ({ onChange }) => {
  const [selectedMonth, setSelectedMonth] = useState(lastMonths[0])

  const handleChange = (e) => {
    setSelectedMonth(e)
    onChange && onChange(formatDate(e, { year: 'numeric', month: 'long' }))
  }

  return (
    <Listbox value={selectedMonth} onChange={handleChange}>
      {({ open }) => (
        <>
          <div className='relative'>
            <Listbox.Button className='relative cursor-default py-1 ml-1 pr-7 text-left'>
              <span className='block truncate'>{formatDate(selectedMonth, { year: 'numeric', month: 'long' })}</span>
              <span className='pointer-events-none absolute inset-y-0 right-0 flex items-center pr-1'>
                <ChevronDownIcon className='h-5 w-5 text-gray-400' aria-hidden='true' />
              </span>
            </Listbox.Button>

            <Transition
              show={open}
              as={Fragment}
              leave='transition ease-in duration-100'
              leaveFrom='opacity-100'
              leaveTo='opacity-0'
            >
              <Listbox.Options className='absolute z-10 mt-1 max-h-60 max-w-xs list-none p-0 overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm'>
                {lastMonths.map((month) => (
                  <Listbox.Option
                    key={month}
                    className={({ active }) =>
                      classNames(
                        active ? 'bg-gray-100' : '',
                        'relative cursor-default text-gray-900 select-none py-2 pl-10 pr-4'
                      )
                    }
                    value={month}
                  >
                    {({ selected }) => (
                      <>
                        <span className={classNames(selected ? 'font-semibold' : 'font-normal', 'block truncate')}>
                          {formatDate(month, { year: 'numeric', month: 'long' })}
                        </span>
                        {selected && (
                          <span className='text-gray-700 absolute inset-y-0 left-0 flex items-center pl-3'>
                            <CheckIcon className='h-5 w-5' aria-hidden='true' />
                          </span>
                        )}
                      </>
                    )}
                  </Listbox.Option>
                ))}
              </Listbox.Options>
            </Transition>
          </div>
        </>
      )}
    </Listbox>
  )
}

export { MonthsDropdown }
