import { ref, computed, onMounted } from 'vue';

const theme = ref<'light' | 'dark' | 'system'>('system');
const appliedTheme = ref<'light' | 'dark'>('light');

const isDark = computed(() => appliedTheme.value === 'dark');

let initialized = false;

function applyToDocument(): void {
  if (appliedTheme.value === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}

function updateAppliedTheme(): void {
  if (theme.value === 'system') {
    const systemIsDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    appliedTheme.value = systemIsDark ? 'dark' : 'light';
  } else {
    appliedTheme.value = theme.value;
  }
  applyToDocument();
}

function setTheme(value: 'light' | 'dark' | 'system'): void {
  theme.value = value;
  localStorage.setItem('hris-theme', value);
  updateAppliedTheme();
}

function initTheme(): void {
  if (initialized || typeof window === 'undefined') return;
  initialized = true;

  const stored = localStorage.getItem('hris-theme');
  if (stored === 'light' || stored === 'dark' || stored === 'system') {
    theme.value = stored;
  }

  updateAppliedTheme();

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (theme.value === 'system') {
      updateAppliedTheme();
    }
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

// Eager init for environments where onMounted may not run (safety)
if (typeof window !== 'undefined') {
  initTheme();
}