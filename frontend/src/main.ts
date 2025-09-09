import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

// FontAwesome imports
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { 
  faUsers, faUser, faPlus, faEdit, faTrash, faEye, faSearch, 
  faCog, faShield, faEnvelope, faPhone, faBuilding, faCrown,
  faTools, faBriefcase, faDownload, faFilter,
  faChevronUp, faChevronDown, faChevronLeft, faChevronRight,
  faCheck, faTimes, faEllipsisV, faTable, faList, faGripVertical,
  faCheckCircle, faClock, faSort, faClipboard, faBox,
  faUserPlus, faArrowUp, faArrowDown
} from '@fortawesome/free-solid-svg-icons'

import App from './App.vue'
import router from './router'

// Add icons to FontAwesome library
library.add(
  faUsers, faUser, faPlus, faEdit, faTrash, faEye, faSearch,
  faCog, faShield, faEnvelope, faPhone, faBuilding, faCrown,
  faTools, faBriefcase, faDownload, faFilter,
  faChevronUp, faChevronDown, faChevronLeft, faChevronRight,
  faCheck, faTimes, faEllipsisV, faTable, faList, faGripVertical,
  faCheckCircle, faClock, faSort, faClipboard, faBox,
  faUserPlus, faArrowUp, faArrowDown
)

const app = createApp(App)

// Register FontAwesome component globally
app.component('font-awesome-icon', FontAwesomeIcon)

app.use(createPinia())
app.use(router)

app.mount('#app')
