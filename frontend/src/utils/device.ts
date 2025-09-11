// Device detection utilities for construction worker interface optimization

export interface DeviceInfo {
  isMobile: boolean
  isTablet: boolean
  isDesktop: boolean
  isTouchDevice: boolean
  isLandscape: boolean
  screenSize: 'small' | 'medium' | 'large'
  hasNotch: boolean
  supportsHaptics: boolean
}

export function detectDevice(): DeviceInfo {
  const userAgent = navigator.userAgent
  const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0
  const screenWidth = window.innerWidth
  const screenHeight = window.innerHeight
  const isLandscape = screenWidth > screenHeight
  
  // Mobile detection
  const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgent) || 
                   (isTouchDevice && screenWidth < 768)
  
  // Tablet detection
  const isTablet = isTouchDevice && screenWidth >= 768 && screenWidth < 1024
  
  // Desktop detection
  const isDesktop = !isMobile && !isTablet
  
  // Screen size classification
  let screenSize: 'small' | 'medium' | 'large' = 'medium'
  if (screenWidth < 640) screenSize = 'small'
  else if (screenWidth > 1024) screenSize = 'large'
  
  // Notch detection (approximate)
  const hasNotch = window.screen.height > 800 && 
                   (window.screen.height / window.screen.width > 2 || 
                    CSS.supports('padding-top: env(safe-area-inset-top)'))
  
  // Haptics support detection
  const supportsHaptics = 'vibrate' in navigator
  
  return {
    isMobile,
    isTablet,
    isDesktop,
    isTouchDevice,
    isLandscape,
    screenSize,
    hasNotch,
    supportsHaptics
  }
}

export function addHapticFeedback(type: 'light' | 'medium' | 'heavy' = 'light') {
  if ('vibrate' in navigator) {
    const patterns = {
      light: [10],
      medium: [20],
      heavy: [30]
    }
    navigator.vibrate(patterns[type])
  }
}

export function optimizeForWorkers() {
  const device = detectDevice()
  const body = document.body
  
  // Add device-specific classes
  if (device.isMobile) body.classList.add('is-mobile')
  if (device.isTablet) body.classList.add('is-tablet')
  if (device.isTouchDevice) body.classList.add('is-touch')
  if (device.hasNotch) body.classList.add('has-notch', 'safe-top', 'safe-bottom')
  if (device.supportsHaptics) body.classList.add('supports-haptics')
  
  // Prevent zoom on inputs for iOS
  if (device.isMobile) {
    const viewport = document.querySelector('meta[name="viewport"]')
    if (viewport) {
      viewport.setAttribute('content', 
        'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no'
      )
    }
  }
  
  // Add touch delay removal for better responsiveness
  if (device.isTouchDevice) {
    document.addEventListener('touchstart', function() {}, { passive: true })
  }
  
  // Orientation change handling
  window.addEventListener('orientationchange', () => {
    setTimeout(() => {
      // Re-detect device info after orientation change
      const newDevice = detectDevice()
      body.classList.toggle('is-landscape', newDevice.isLandscape)
      body.classList.toggle('is-portrait', !newDevice.isLandscape)
    }, 100)
  })
  
  return device
}