import { ref, computed, watch, onMounted } from 'vue';

const theme = ref<'light' | 'dark' | 'system'>('system');
const appliedTheme = ref<'light' | 'dark'>('light');

const isDark = computed(() => appliedTheme.value === 'dark');

function setTheme(value: 'light' | 'dark' | 'system') {
  theme.value = value;
  localStorage.setItem('hris-theme', value);
  updateAppliedTheme();
}

function updateAppliedTheme() {
  if (theme.value === 'system') {
    const systemIsDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    appliedTheme.value = systemIsDark ? 'dark' : 'light';
  } else {
    appliedTheme.value = theme.value;
  }

  // Update document class
  if (appliedTheme.value === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}

function initTheme() {
  const stored = localStorage.getItem('hris-theme');
  if (stored && ['light', 'dark', 'system'].includes(stored)) {
    theme.value = stored as 'light' | 'dark' | 'system';
  }

  updateAppliedTheme();

  // Watch for changes in system preference
  watch(() => theme.value, () => {
    if (theme.value !== 'system') return;
    updateAppliedTheme();
  });

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (theme.value !== 'system') return;
    updateAppliedTheme();
  });
}

export function useTheme() {
  onMounted(() => {
    initTheme();
  });

  return {
    theme,
    appliedTheme,
    isDark,
    setTheme,
  };
}