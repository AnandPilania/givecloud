import { useRecoilValue } from 'recoil'
import usePageTitle from '@/hooks/usePageTitle'
import Charts from './Charts'
import StartChecklist from './StartChecklist/StartCheckList'
import Greeting from './Greeting'
import configState from '@/atoms/config'
import styles from './Dashboard.scss'

const Dashboard = () => {
  usePageTitle('Home')

  const { isGivecloudPro = false } = useRecoilValue(configState)

  return (
    <div className={styles.root}>
      <Greeting />
      {isGivecloudPro && <StartChecklist />}
      <Charts />
    </div>
  )
}

export default Dashboard
