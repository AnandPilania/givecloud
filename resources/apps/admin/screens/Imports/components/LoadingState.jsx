import Container from '@/screens/Imports/components/Container'
import Stamp from '@/screens/Imports/components/Stamp'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinner } from '@fortawesome/pro-regular-svg-icons'

export default function LoadingState(props) {
  if (!props.isLoading && props.children) return props.children

  return (
    <>
      <Container>
        <Stamp>
          <FontAwesomeIcon icon={faSpinner} spin={true} size='2x' />
        </Stamp>
      </Container>
    </>
  )
}
