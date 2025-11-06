si
# TODO: UI Improvement for Admin Products Index

## Tasks
- [ ] Add CSS utilities to resources/css/app.css for ComercioPlus branding and buttons.
- [ ] Update resources/views/admin/products/index.blade.php with new responsive design, cards, and states.

## Details
- Maintain all existing logic: routes, controllers, variables unchanged.
- Use Tailwind for styling, add minimal CSS if needed.
- Ensure responsive design: 1 col mobile, 2 sm, 3 lg, 4 xl.
- Add accessibility: aria-labels, focus states.
- Branding: Primary #FF6000, bg #F5F6F8, rounded-2xl, soft shadows.
- Grid: grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6.
- Cards: Image aspect-[4/3], title, price, category if exists, buttons Edit/Delete.
- Empty state: Dashed border, message, CTA.
- Pagination: Keep as is.
