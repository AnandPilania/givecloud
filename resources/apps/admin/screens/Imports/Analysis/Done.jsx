import Container from '@/screens/Imports/components/Container'
import Stamp from '@/screens/Imports/components/Stamp'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck } from '@fortawesome/pro-regular-svg-icons'

export default function Done(props) {
  return (
    <>
      <Container>
        <Stamp bgColor='bg-green-400'>
          <FontAwesomeIcon icon={faCheck} className='text-white' size='2x' />
        </Stamp>
        {props.children}
      </Container>
    </>
  )
}
