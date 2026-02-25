<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# ðŸŽ¯ Implementation Plan: Crear Tienda + Productos Views

## âœ… **Current Status Analysis**
- âœ… `StoreController` and `ProductController` exist with basic functionality
- âœ… Models `Store` and `Product` are properly set up with relationships
- âœ… Basic views exist: `stores/create.blade.php` and `products/index.blade.php`
- âœ… Layout structure with sidebar exists (`layouts.app` + `layouts.navigation`)
- âš ï¸ Routes need updates to match exact requirements
- âš ï¸ Controllers need updates for exact functionality
- âš ï¸ Views need replacement with exact provided content

## ðŸ“‹ **Implementation Steps**

### **1. Routes Update (`routes/web.php`)**
- âœ… Add/update routes for `store.create`, `store.store`, `products.index`, `products.store`
- âœ… Ensure proper `auth` middleware grouping

### **2. Controllers Updates**
- âœ… **StoreController**: Update redirect destination from `products.dashboard` to `products.index`
- âœ… **ProductController**:
  - âœ… Update `store` method to handle image uploads and URL imports
  - âœ… Add `saveRemoteImage` method for URL-based image handling
  - âœ… Add proper validation for `image_url` field

### **3. Model Update**
- âœ… **Product Model**: Add `image_path` to fillable array

### **4. Views Replacement**
- [ ] **stores/create.blade.php**: Replace with exact provided content (dark form with orange buttons)
- [ ] **products/index.blade.php**: Replace with exact provided content (light dashboard with tabs)

### **5. Testing Steps**
- [ ] Test store creation flow
- [ ] Test product creation with both file upload and URL
- [ ] Test product listing with pagination
- [ ] Test tab switching functionality
- [ ] Verify responsive design

## ðŸŽ¨ **Design Requirements**
- **Orange Primary**: `#FF6000`
- **Orange Soft**: `#FF8A3D`
- **Dark Background**: `#000` (95% opacity)
- **Light Text**: `#fff` / `#F3F4F6`

## ðŸ”§ **Technical Requirements**
- Laravel + Blade + Tailwind
- Auth middleware where indicated
- File storage in `public` disk
- Spanish language throughout
- No breaking existing functionality

## ðŸ“ **Files to Edit**
1. `routes/web.php`
2. `app/Http/Controllers/StoreController.php`
3. `app/Http/Controllers/ProductController.php`
4. `app/Models/Product.php`
5. `resources/views/stores/create.blade.php`
6. `resources/views/products/index.blade.php`

## ðŸ§ª **Testing Commands**
```bash
php artisan storage:link
php artisan optimize:clear
```

## ðŸ“± **Responsive Testing**
- â‰¤375px: Stacked buttons
- â‰¥1280px: 4-5 column grid

