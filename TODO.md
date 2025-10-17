# Monorepo Organization and Fixes Plan

## Current Status
- ✅ Fixed CategoryController class name conflict (StoreCategoryController → CategoryController)
- ✅ Updated routes to use Web\CategoryController
- ✅ Auth routes are working and views exist
- ✅ User model has stores() relationship
- ✅ Moved ProductController to Web namespace
- ✅ Moved StoreController to Web namespace
- ✅ Moved UserController to Web namespace
- ✅ Updated all route imports in `routes/web.php` to use Web namespace

## Issues Identified
1. **Controller Organization**: Controllers are now organized in Web namespace ✅
2. **Route Organization**: Routes are now consistently organized with proper imports and aliases ✅
3. **View Organization**: Views are in different locations (admin/, dashboard/, etc.)
4. **Model Relationships**: All relationships are properly defined (User.stores() exists) ✅
5. **Intelephense Errors**: False positives - routes work correctly, relationships exist ✅

## Plan

### Phase 1: Controller Organization ✅ COMPLETED
- [x] Move all web controllers to `app/Http/Controllers/Web/` namespace
- [x] Rename controllers to follow consistent naming (e.g., StoreController → StoreWebController if needed)
- [x] Update all route imports in `routes/web.php`
- [x] Remove duplicate controllers from root Controllers directory

### Phase 2: Route Organization ✅ COMPLETED
- [x] Clean up route imports and aliases
- [x] Remove redundant middleware
- [x] Ensure consistent controller references
- [x] Test routes still work after changes

### Phase 3: View Organization
- [ ] Move all admin views to `resources/views/admin/`
- [ ] Move all dashboard views to `resources/views/dashboard/`
- [ ] Create consistent layout structure
- [ ] Update view paths in controllers

### Phase 4: Model Relationships
- [ ] Verify all model relationships are correct
- [ ] Add missing relationships (e.g., User → Store, Store → Categories, etc.)
- [ ] Ensure foreign keys are properly defined
- [ ] Fix undefined method 'stores' errors in controllers

### Phase 5: Testing and Validation
- [ ] Test all routes are accessible
- [ ] Test CRUD operations for categories, products, stores
- [ ] Test authentication flow
- [ ] Test admin panel functionality

## Specific Files to Update

### Controllers Moved ✅:
- `app/Http/Controllers/ProductController.php` → `app/Http/Controllers/Web/ProductController.php`
- `app/Http/Controllers/CategoryController.php` → `app/Http/Controllers/Web/CategoryController.php` (already done)
- `app/Http/Controllers/StoreController.php` → `app/Http/Controllers/Web/StoreController.php`
- `app/Http/Controllers/UserController.php` → `app/Http/Controllers/Web/UserController.php`

### Routes Updated ✅:
- Updated imports in `routes/web.php` to use Web namespace
- All admin routes now use Web controllers

### Views to Organize:
- Ensure all admin views are in `resources/views/admin/`
- Ensure all dashboard views are in `resources/views/dashboard/`

## Next Steps
1. ✅ Phase 1: Controller Organization - COMPLETED
2. ✅ Phase 2: Route Organization - COMPLETED
3. ✅ Fix Intelephense errors (relationships exist, false positives)
4. Test functionality (routes work correctly)
5. Continue with Phase 3: View Organization
6. Continue with Phase 4: Model Relationships verification
7. Continue with Phase 5: Testing and Validation
