/* globals Canny:readonly */

import { useEffect } from 'react'
import { useRecoilValue } from 'recoil'
import axios from 'axios'
import configState from '@/atoms/config'

const Feedback = () => {
  const { cannyBoardToken: boardToken } = useRecoilValue(configState)

  useEffect(() => {
    // prettier-ignore
    (function(w,d,i,s){function l(){if(!d.getElementById(i)){var f=d.getElementsByTagName(s)[0],e=d.createElement(s);e.type='text/javascript',e.async=!0,e.src='https://canny.io/sdk.js',f.parentNode.insertBefore(e,f)}}if('function'!=typeof w.Canny){var c=function(){c.q.push(arguments)};c.q=[],w.Canny=c,'complete'===d.readyState?l():w.attachEvent?w.attachEvent('onload',l):w.addEventListener('load',l,!1)}})(window,document,'canny-jssdk','script')

    const renderCannyWidget = async () => {
      const response = await axios.get('/jpanel/auth/canny')

      Canny('render', {
        boardToken,
        basePath: '/jpanel/feedback',
        ssoToken: response?.data?.sso_token,
      })
    }

    renderCannyWidget()
  }, [boardToken])

  return <div data-canny />
}

export default Feedback
