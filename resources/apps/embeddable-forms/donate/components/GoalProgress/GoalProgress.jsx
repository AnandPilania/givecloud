import { memo, useContext, useEffect } from 'react'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import { supportedPrimaryColors } from '@/constants/styleConstants'
import { moneyFormatter } from '@/utilities/formatMoney'
import styles from './GoalProgress.scss'

const GoalProgress = () => {
  const {
    primaryColor,
    product,
    showGoalProgress,
    goalCurrencyFormat,
    currency: {
      chosen: { code: currencyCode },
    },
  } = useContext(StoreContext)

  const { bgColor, borderColor } = supportedPrimaryColors[primaryColor]

  const goalAmount = moneyFormatter(product.goal_amount, currencyCode, goalCurrencyFormat)
  const goalProgress = moneyFormatter(product.goal_progress, currencyCode, goalCurrencyFormat)
  const daysLeft = product.goal_days_left

  useEffect(() => {
    document.dispatchEvent(
      new CustomEvent('embeddedable-donate:goal-progress', {
        detail: {
          goal_amount: product.goal_amount,
          goal_progress: product.goal_progress,
          currency_code: currencyCode,
        },
        bubbles: true,
        cancelable: true,
        composed: false,
      })
    )
  }, [product, currencyCode])

  return (
    showGoalProgress && (
      <div className={classnames(styles.root, 'goal')}>
        <div className={classnames(styles.progress, borderColor)}>
          <div
            role='progressbar'
            className={classnames(styles.progressBar, bgColor)}
            style={{ width: `${product.goal_progress_percent}%` }}
          ></div>
        </div>
        <div className={styles.content}>
          <div className={classnames(styles.goalAmount, 'goal-amount')}>
            <small>RAISED</small>
            <br />
            {goalProgress}
          </div>
          <div className={classnames(styles.goalDays, 'goal-days')}>
            <small>DAYS LEFT</small>
            <br />
            {daysLeft}
          </div>
          <div className={classnames(styles.goalCount, 'goal-count')}>
            <small>GOAL</small>
            <br />
            {goalAmount}
          </div>
        </div>
      </div>
    )
  )
}

export default memo(GoalProgress)
