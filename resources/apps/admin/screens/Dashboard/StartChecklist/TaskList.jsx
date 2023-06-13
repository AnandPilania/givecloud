import { useState, useCallback, useMemo } from 'react'
import PropTypes from 'prop-types'

import ChecklistItems from './ChecklistItems'
import ChecklistCategoryTabs from './ChecklistCategoryTabs'
import ActiveChecklistItem from './ActiveChecklistItem'

const TaskList = ({ tasks }) => {
  const [activeTask, setActiveTask] = useState(getActiveTask(tasks))

  const activeCategory = useMemo(() => {
    return categories.filter((category) => {
      return tasks[category.key].filter((task) => task.key === activeTask.key).length > 0
    })[0]
  }, [activeTask, tasks])

  const activeTasks = useMemo(() => {
    return getActiveTasks(activeCategory, tasks)
  }, [activeCategory, tasks])

  const changeCategory = useCallback(
    (category) => {
      setActiveTask(getActiveTask(tasks, category))
    },
    [tasks, setActiveTask]
  )

  const activeTaskData = useMemo(() => {
    if (activeCategory.key && activeTask.key) {
      const activeTaskData = tasks[activeCategory.key].filter((task) => task.key === activeTask.key)[0]
      return {
        taskKey: activeTaskData.key,
        ...activeTaskData,
      }
    }
    return null
  }, [tasks, activeCategory, activeTask])

  return (
    <>
      <div>
        <div className='flex px-6'>
          <ChecklistCategoryTabs
            categories={categories}
            activeCategory={activeCategory}
            changeCategory={changeCategory}
          />
        </div>
      </div>
      <div className='flex w-full pt-0 p-6'>
        <div className='flex flex-col lg:w-1/2 lg:pr-6'>
          <div>
            <ChecklistItems
              key={activeCategory.key}
              tasks={activeTasks}
              activeTask={activeTask}
              setActiveTask={setActiveTask}
              activeTaskData={activeTaskData}
            />
          </div>
        </div>
        <div className={`hidden lg:flex w-1/2 items-center`}>
          {activeTaskData && <ActiveChecklistItem {...activeTaskData} />}
        </div>
      </div>
    </>
  )
}

TaskList.propTypes = {
  tasks: PropTypes.object.isRequired,
}

const categories = [
  {
    key: 'setup',
    label: 'Setup',
  },
  {
    key: 'goingLive',
    label: 'Go Live',
  },
  {
    key: 'next',
    label: 'Next Steps',
  },
]

function getInitialActiveCategory(tasks) {
  const categoriesWithIncompleteTasks = categories.filter((category) => {
    return tasks[category.key].filter((task) => !task.isCompleted && !task.isSkipped && task.isActive).length > 0
  })
  return categoriesWithIncompleteTasks.length > 0 ? categoriesWithIncompleteTasks[0] : categories[0]
}

function getActiveTasks(activeCategory, tasks) {
  return tasks[activeCategory.key]?.filter((task) => task.isActive)
}

function getActiveTask(tasks, category = null) {
  const initialCategory = category ? category : getInitialActiveCategory(tasks)
  const activeTasks = getActiveTasks(initialCategory, tasks)
  const incompleteTasks = activeTasks.filter((task) => !task.isCompleted && !task.isSkipped)
  if (incompleteTasks.length) {
    return incompleteTasks[0]
  }
  return activeTasks[activeTasks.length - 1]
}

export default TaskList
