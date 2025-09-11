import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: Echo
  }
}

window.Pusher = Pusher

const echo = new Echo({
  broadcaster: 'reverb',
  key: 'construction-key',
  wsHost: 'localhost',
  wsPort: 8080,
  wssPort: 8080,
  forceTLS: false,
  enabledTransports: ['ws', 'wss'],
  auth: {
    headers: {
      Authorization: `Bearer ${localStorage.getItem('token') || ''}`,
    },
  },
})

window.Echo = echo

export default echo