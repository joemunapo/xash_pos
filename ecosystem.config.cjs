module.exports = {
  apps: [
    {
      name: 'laravel-app',
      script: 'artisan',
      interpreter: 'php',
      args: 'serve --host=139.84.227.2 --port=899',
      cwd: '/var/www/pos-dev.xash.co.zw',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env: {
        APP_ENV: 'production',
      },
    },
    {
      name: 'laravel-queue',
      script: 'artisan',
      interpreter: 'php',
      args: 'queue:work --sleep=3 --tries=3 --max-time=3600',
      cwd: '/var/www/pos-dev.xash.co.zw',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env: {
        APP_ENV: 'production',
      },
    },
    {
      name: 'laravel-reverb',
      script: 'artisan',
      interpreter: 'php',
      args: 'reverb:start',
      cwd: '/var/www/pos-dev.xash.co.zw',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env: {
        APP_ENV: 'production',
      },
    },
  ],
};
