import PropTypes from 'prop-types'

function classNames(...classes) {
  return classes.filter(Boolean).join(' ')
}

const ChecklistCategoryTabs = ({ activeCategory, categories, changeCategory }) => {
  if (!activeCategory) return null

  return (
    <div className='w-full'>
      <div className='lg:hidden'>
        <label htmlFor='tabs' className='sr-only'>
          Select a tab
        </label>
        {/* Use an "onChange" listener to redirect the user to the selected tab URL. */}
        <div className='bg-white rounded-full px-3 py-2 mb-3 border-gray-300 shadow'>
          <select
            id='tabs'
            name='tabs'
            className={`block w-full  focus:ring-indigo-500 focus:border-indigo-500 `}
            onChange={(e) => {
              changeCategory(categories.filter(({ key }) => key === e.target.value)[0])
            }}
            value={activeCategory.key}
          >
            {categories.map((category) => (
              <option key={category.key} value={category.key}>
                {category.label}
              </option>
            ))}
          </select>
        </div>
      </div>
      <div className='hidden lg:block'>
        <nav className='flex w-full space-x-4 mb-3 pb-3' aria-label='Tabs'>
          {categories.map((category, index) => {
            const isActive = category.key === activeCategory.key
            return (
              <a
                key={category.key}
                onClick={() => changeCategory(category)}
                className={classNames(
                  isActive
                    ? ' bg-brand-purple text-white hover:text-gray-100'
                    : 'bg-white hover:bg-gray-100 text-gray-600 hover:text-gray-800',
                  'pl-2 pr-4 py-2 font-medium text-sm rounded-full cursor-pointer flex justify-center items-center'
                )}
                aria-current={isActive ? 'page' : undefined}
              >
                <div
                  className={`${
                    isActive ? 'bg-white text-brand-purple' : 'bg-brand-purple text-white'
                  } text-sm text-white w-6 h-6 flex items-center justify-center rounded-full mr-2`}
                >
                  <span>{index + 1}</span>
                </div>
                {category.label}
              </a>
            )
          })}
        </nav>
      </div>
    </div>
  )
}

ChecklistCategoryTabs.propTypes = {
  changeCategory: PropTypes.func,
  activeCategory: PropTypes.object,
  categories: PropTypes.array,
}

export default ChecklistCategoryTabs
