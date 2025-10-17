module.exports = {
  apps: [
    {
      name: 'comercio-plus-backend',
      script: 'artisan',
      args: 'serve --host=0.0.0.0 --port=8000',
      cwd: __dirname,
      instances: 1,
      exec_mode: 'fork',
      env: {
        NODE_ENV: 'development',
      },
      env_production: {
        NODE_ENV: 'production',
      },
    },
  ],
};
