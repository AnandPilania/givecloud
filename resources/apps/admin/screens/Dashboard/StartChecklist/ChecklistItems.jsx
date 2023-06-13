import { Fragment } from 'react'
import { RiCheckFill, RiArrowDropRightLine, RiArrowDropDownLine } from 'react-icons/ri'
import PropTypes from 'prop-types'

import ActiveChecklistItem from './ActiveChecklistItem'

const ChecklistItems = ({ tasks, activeTask, setActiveTask, activeTaskData }) => {
  return (
    <>
      {tasks &&
        tasks.map((task) => {
          const active = activeTask?.key === task.key
          return (
            <Fragment key={task.key}>
              <div
                onClick={() => {
                  setActiveTask(task)
                }}
                className={`${active ? 'bg-gray-100' : ''} cursor-pointer mb-2 rounded p-2`}
              >
                <div className='flex w-full items-center mr-4'>
                  <div
                    className={`${
                      task.isSkipped ? 'bg-yellow-500' : task.isCompleted ? 'bg-green-500' : 'bg-gray-200'
                    } w-6 h-6 flex flex-shrink-0 items-center justify-center rounded-full mr-2`}
                  >
                    <RiCheckFill className={`text-white`} />
                  </div>
                  <div className='flex-grow'>
                    <h4 className='flex justify-between items-center text-sm font-extrabold'>
                      <span className={` ${task.isSkipped ? 'line-through text-gray-400' : ''}`}>
                        {task.title} {task.isSkipped ? '(Skipped)' : ''}
                      </span>
                      {active && (
                        <span className='ml-2 text-xl text-gray-500'>
                          <span className='hidden lg:inline'>
                            <RiArrowDropRightLine />
                          </span>
                          <span className='inline lg:hidden'>
                            <RiArrowDropDownLine />
                          </span>
                        </span>
                      )}
                    </h4>
                  </div>
                </div>
              </div>
              {activeTaskData && activeTaskData.key === task.key && (
                <div className='block lg:hidden my-3'>
                  <ActiveChecklistItem {...activeTaskData} />
                </div>
              )}
            </Fragment>
          )
        })}
    </>
  )
}

ChecklistItems.propTypes = {
  tasks: PropTypes.array,
  activeTask: PropTypes.object,
  activeTaskData: PropTypes.object,
  setActiveTask: PropTypes.func,
}

export default ChecklistItems
