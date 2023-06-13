import { StrictMode } from 'react'
import { RecoilRoot } from 'recoil'
import { QueryClient, QueryClientProvider } from 'react-query'
import { ToastContainer } from 'react-toastify'
import Layout from './components/Layout/Layout'
import { cssVariables } from '@/utilities/theme'

import 'react-toastify/dist/ReactToastify.css'

const queryClient = new QueryClient()

const styleElement = document.createElement('style')
document.head.appendChild(styleElement)
styleElement.sheet.insertRule(':root {}', 0)

Object.keys(cssVariables).forEach((property) => {
  styleElement.sheet.cssRules[0].style.setProperty(property, cssVariables[property])
})

const App = () => (
  <QueryClientProvider client={queryClient}>
    <RecoilRoot>
      <StrictMode>
        <Layout />
      </StrictMode>
      <ToastContainer />
    </RecoilRoot>
  </QueryClientProvider>
)

export default App
