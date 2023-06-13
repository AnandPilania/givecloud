import PropTypes from 'prop-types'

const MockComponent = ({ children }) => <>{children}</>

MockComponent.propTypes = {
  children: PropTypes.node,
}

export { MockComponent }
