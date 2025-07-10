# TODO for Tailwind v4 + Vue + Vite integration

- [x] Verify composer dependencies installed (vendor folder)
- [x] Verify npm dependencies installed for Vite + Vue + Tailwind v4
- [x] Confirm postcss.config.js contains '@tailwindcss/postcss' plugin
- [x] Confirm tailwind.config.js includes required content paths and color/borderRadius extensions
- [x] Confirm resources/css/app.css imports Tailwind correctly
- [x] Confirm resources/js/app.js imports app.css and has Vue test entry
- [x] Create resources/js/vue-test.js as Vue + Tailwind test entry
- [x] Confirm vite.config.js includes laravel and vue plugins and vue-test.js entry
- [x] Create resources/views/tailwind-test.blade.php for Tailwind test view
- [x] Create resources/views/vue-test.blade.php for Vue + Tailwind test view
- [x] Add routes for /tailwind-test and /vue-test in routes/web.php before fallback
- [ ] Run `php artisan route:clear` and `php artisan optimize:clear`
- [ ] Run `npm run dev` and keep running
- [ ] Run `php artisan serve --host=127.0.0.1 --port=8000`
- [ ] Verify http://127.0.0.1:8000/tailwind-test shows orange Tailwind OK box
- [ ] Verify http://127.0.0.1:8000/vue-test shows orange Vue + Tailwind OK box
- [ ] Troubleshoot if assets fail to load or routes not found
