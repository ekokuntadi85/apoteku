import { defineConfig } from 'tailwindcss'

export default defineConfig({
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/livewire/flux/stubs/**/*.blade.php',
    "./app/Livewire/**/*.php" // Untuk Laravel 10 / Livewire 3
  ],
  darkMode: 'class', // Enable class-based dark mode for Flux
  theme: {
    extend: {
      fontFamily: {
        sans: ['Outfit', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
      },
    },
  },
  plugins: [],
})
