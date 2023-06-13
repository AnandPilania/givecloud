import ReactDOM from 'react-dom'

// styles.css needs to be loaded before the App component in order for Tailwind
// preflight styles to not take precedence of styles define in the CSS modules
// of the React app. This is primarily an issue for us with [type='button'] styles.
import './styles.css'

import { App } from './App'

ReactDOM.render(<App />, document.getElementById('app'))
