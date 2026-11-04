const STORAGE_KEY = 'umechan-theme'

export const THEME = {
  LIGHT: 'light',
  DARK: 'dark'
}

function getInitialTheme() {
  const stored = localStorage.getItem(STORAGE_KEY)
  if (stored === THEME.DARK || stored === THEME.LIGHT) {
    return stored
  }

  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    return THEME.DARK
  }
  return THEME.LIGHT
}

export default {
  state: {
    current: getInitialTheme()
  },
  init() {
    this.applyTheme()
  },
  toggle() {
    this.state.current = this.state.current === THEME.LIGHT ? THEME.DARK : THEME.LIGHT
    this.save()
    this.applyTheme()
  },
  set(theme) {
    if (theme === THEME.DARK || theme === THEME.LIGHT) {
      this.state.current = theme
      this.save()
      this.applyTheme()
    }
  },
  save() {
    localStorage.setItem(STORAGE_KEY, this.state.current)
  },
  applyTheme() {
    document.documentElement.classList.remove('theme-light', 'theme-dark')
    document.documentElement.classList.add(`theme-${this.state.current}`)
  }
}
