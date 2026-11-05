import { reactive } from 'vue';

const STORAGE_KEY = 'umechan-theme';

export const THEME = {
  LIGHT: 'light',
  DARK: 'dark'
};

function getInitialTheme() {
  const stored = localStorage.getItem(STORAGE_KEY);
  if (stored === THEME.DARK || stored === THEME.LIGHT) {
    return stored;
  }

  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    return THEME.DARK;
  }
  return THEME.LIGHT;
}

const state = reactive({
  current: getInitialTheme()
});

export default {
  state,
  init() {
    this.applyTheme();
  },
  toggle() {
    state.current = state.current === THEME.LIGHT ? THEME.DARK : THEME.LIGHT;
    this.save();
    this.applyTheme();
  },
  set(theme) {
    if (theme === THEME.DARK || theme === THEME.LIGHT) {
      state.current = theme;
      this.save();
      this.applyTheme();
    }
  },
  save() {
    localStorage.setItem(STORAGE_KEY, state.current);
  },
  applyTheme() {
    document.documentElement.classList.remove('theme-light', 'theme-dark');
    document.documentElement.classList.add(`theme-${state.current}`);
  }
};
