import './assets/main.css'
import './assets/worker-mobile.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createAppI18n } from '@/locales/config/i18n.config'

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
import './plugins/echo'
import { optimizeForWorkers } from './utils/device'

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

// Initialize the app with async i18n setup
async function initializeApp() {
  // Optimize for construction workers before app creation
  const deviceInfo = optimizeForWorkers()
  console.log('Device optimization applied:', deviceInfo)
  
  const app = createApp(App)
  
  // Initialize i18n
  const i18n = await createAppI18n()
  
  // Register FontAwesome component globally
  app.component('font-awesome-icon', FontAwesomeIcon)
  
  app.use(createPinia())
  app.use(router)
  app.use(i18n)
  
  app.mount('#app')
}

// Start the application
initializeApp().catch(error => {
  console.error('Failed to initialize app:', error)
})
