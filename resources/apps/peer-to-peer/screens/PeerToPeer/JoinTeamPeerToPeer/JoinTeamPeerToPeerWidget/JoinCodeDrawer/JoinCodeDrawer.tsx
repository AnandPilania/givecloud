import type { EventWithIndex } from '@/components/CodeInputs'
import type { FC } from 'react'
import type { DrawerProps } from '@/aerosol'
import { useState, useCallback, useEffect } from 'react'
import Givecloud from 'givecloud'
import { SCREENS } from '@/constants/screens'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowLeft, faArrowRight, faSpinner } from '@fortawesome/pro-regular-svg-icons'
import FacebookLoginButton from '@/shared/components/SocialLoginDrawer/components/FacebookLoginButton/FacebookLoginButton'
import GoogleLoginButton from '@/shared/components/SocialLoginDrawer/components/GoogleLoginButton/GoogleLoginButton'
import MicrosoftLoginButton from '@/shared/components/SocialLoginDrawer/components/MicrosoftLoginButton/MicrosoftLoginButton'
import { Button, Carousel, CarouselItem, CarouselItems, Drawer, triggerToast } from '@/aerosol'
import { CodeInputs, Text } from '@/components'
import { JOIN_PATH } from '@/constants/paths'
import { openNewWindow } from '@/shared/utilities'
import { useFocus, useParams } from '@/shared/hooks'
import { useSupporterState } from '@/screens/PeerToPeer/useSupporterState'
import { useValidateTeamJoinCodeMutation } from './useValidateTeamJoinCodeMutation'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import styles from './JoinCodeDrawer.styles.scss'

type Props = Pick<DrawerProps, 'isOpen' | 'onClose'>

const createCodeValues = () => [...new Array(4)].map((_, i: number) => ({ name: i.toString(), value: '' }))

const JoinCodeDrawer: FC<Props> = ({ isOpen, onClose }) => {
  const [values, setValues] = useState(createCodeValues())
  const isCodeValid = values.every(({ value }) => !!value.length)
  const { params, setAndReplaceParams, id, deleteAndReplaceParams } = useParams()
  const [buttonRef, setButtonFocus] = useFocus<HTMLButtonElement>()
  const [newWindow, setNewWindow] = useState<Window | undefined>(undefined)
  const { setSupporter } = useSupporterState()

  const { setPeerToPeerState, peerToPeerValue } = usePeerToPeerState()

  const getActiveIndex = () => Number(params.get(SCREENS.DRAWER))

  const { mutate, isLoading } = useValidateTeamJoinCodeMutation({
    onSuccess: (isCodeValid) => {
      if (!isCodeValid) {
        triggerToast({
          type: 'error',
          header: 'Invalid Code',
          description: `The code you entered is invalid. Check your code and try again.`,
        })
      } else {
        setAndReplaceParams(SCREENS.DRAWER, '1')
      }
    },
    onError: () => {
      triggerToast({
        type: 'error',
        header: 'Something went wrong.',
        description: `There was an error validating your code. Please try again later.`,
      })
    },
  })

  const handleChange = ({ target }: EventWithIndex) => {
    const { value, index } = target

    setValues((prevState) => {
      const copy = [...prevState]
      copy[index] = { ...copy[index], value }
      return copy
    })
  }

  const handleValidateJoinCode = () => {
    if (id) {
      const code = values
        .map(({ value }) => value)
        .join('')
        .toUpperCase()

      setPeerToPeerState({ ...peerToPeerValue, team: { ...peerToPeerValue.team, joinCode: code } })
      mutate({ id, code })
    }
  }

  const onAuthenticated = async () => {
    const { account } = await Givecloud.Account.get()
    setSupporter(account)
    params.set(SCREENS.SCREEN, SCREENS.GOAL)
    deleteAndReplaceParams(['joinCode', 'drawer'], `${JOIN_PATH}/${id}`)
  }

  const handleOnClose = useCallback(() => {
    if (newWindow) {
      newWindow.close()
    }
  }, [newWindow])

  const handleLogin = (provider: string) => {
    const newWindow = openNewWindow({
      url: `https://${location.host}/account/social/transparent/${provider}`,
      name: 'socialChallengeLogin',
      onClose,
    })

    setNewWindow(newWindow)
  }

  useEffect(() => {
    const joinCode = params.get('joinCode')?.split('')

    if (joinCode) {
      const updatedValues = values.map((value, index) => ({ ...value, value: joinCode[index] }))
      setValues(updatedValues)
    }

    const handleOnMessage = async ({ data }) => {
      if (data.type !== 'social_login') return null
      handleOnClose()

      if (data.payload?.successful) {
        setNewWindow(undefined)
        onClose

        await onAuthenticated()
      }
    }

    window.addEventListener('message', handleOnMessage)

    return () => window.removeEventListener('message', handleOnMessage)
  }, [])

  const renderSocialLogin = () => {
    if (newWindow) {
      return <FontAwesomeIcon icon={faSpinner} spin size='2x' />
    }
    return (
      <>
        <div className={styles.wrapper}>
          <GoogleLoginButton onClick={handleLogin} />
          <FacebookLoginButton onClick={handleLogin} />
          <MicrosoftLoginButton onClick={handleLogin} />
        </div>
        <Button isClean className={styles.cta} onClick={() => setAndReplaceParams(SCREENS.DRAWER, '0')} theme='custom'>
          <FontAwesomeIcon className='mr-2' icon={faArrowLeft} />
          Back
        </Button>
      </>
    )
  }

  return (
    <Drawer name='join team' isOpen={isOpen} onClose={onClose}>
      <Carousel name='join-screen' initialIndex={getActiveIndex()}>
        <CarouselItems>
          <CarouselItem isPaddingless itemIndex={0} className={styles.padding}>
            <Text type='h4'>Enter a Join Code</Text>
            <Text isMarginless>
              Don't have a join code?
              <br /> Request the code from <strong>{peerToPeerValue.team.name}.</strong>
            </Text>
            <div className={styles.inputs}>
              <CodeInputs nextFocusedElement={setButtonFocus} inputValues={values} onChange={handleChange} />
            </div>
            <Button
              isDisabled={!isCodeValid}
              isLoading={isLoading}
              isFullWidth
              ref={buttonRef}
              onClick={handleValidateJoinCode}
              theme='custom'
            >
              Join Team <FontAwesomeIcon className='ml-2' icon={faArrowRight} />
            </Button>
          </CarouselItem>
          <CarouselItem isPaddingless className={styles.container} itemIndex={1}>
            {renderSocialLogin()}
          </CarouselItem>
        </CarouselItems>
      </Carousel>
    </Drawer>
  )
}

export { JoinCodeDrawer }
