import { memo, useState } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import TakeActionGiveNowTab from '@/components/TakeAction/TakeActionGiveNowTab'
import styles from '@/components/TakeAction/TakeAction.scss'

const TakeAction = ({ domain, tabs, themeStyle, themePrimaryColor }) => {
  const [activeTab, setActiveTab] = useState(tabs[0].name)

  const selectTab = (ev, tabNumber) => {
    ev.preventDefault()
    setActiveTab(tabNumber)
  }

  return (
    <div className={`${styles.root} ${themeStyle === 'dark' ? styles.dark : styles.light}`}>
      <nav className={styles.nav}>
        {tabs.map((tab) => {
          const isActive = tab.name === activeTab

          return (
            <a
              key={tab.name}
              className={classnames(
                styles.tab,
                isActive && styles.active,
                styles[themePrimaryColor]
              )}
              href='#'
              onClick={(e) => {
                selectTab(e, tab.name)
              }}
            >
              {tab.name}
            </a>
          )
        })}
      </nav>

      <div className={styles.giveNowTabContainer}>
        {tabs.map((tab) => (
          <TakeActionGiveNowTab
            key={tab.name}
            hide={activeTab !== tab.name}
            themeStyle={themeStyle}
            themePrimaryColor={themePrimaryColor}
            domain={domain}
            productCode={tab.productId}
            productSummary={tab.productSummary}
          />
        ))}
      </div>
    </div>
  )
}

TakeAction.propTypes = {
  domain: PropTypes.string,
  themeStyle: PropTypes.string,
  themePrimaryColor: PropTypes.string,
  tabs: PropTypes.arrayOf(
    PropTypes.shape({
      name: PropTypes.string,
      productId: PropTypes.string,
    })
  ),
}

export default memo(TakeAction)
