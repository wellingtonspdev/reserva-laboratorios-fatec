/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './crbs-core/application/controllers/**/*.php',
    './crbs-core/application/views/**/*.php',
    './crbs-core/application/components/**/*.php',
  ],
  safelist: [
    'cps-room-card-status-free',
    'cps-room-card-status-active',
    'cps-room-card-status-upcoming',
    'cps-room-schedule-row-active',
    'cps-room-schedule-row-upcoming',
    'cps-room-schedule-summary-next-only',
    {
      pattern: /cps-floor-accent-(0|1|2|3|4|5|6|7|8)/,
    },
  ],
  theme: {
    extend: {
      colors: {
        cps: {
          red: '#B20000',
          black: '#000000',
          white: '#FFFFFF',
          'gray-light': '#F5F5F5',
          'gray-border': '#D9D9D9',
          'gray-text': '#666666',
        },
      },
      fontFamily: {
        sans: ['Verdana', 'Geneva', 'sans-serif'],
      },
      borderRadius: {
        card: '8px',
        btn: '4px',
      },
    },
  },
  plugins: [],
};
