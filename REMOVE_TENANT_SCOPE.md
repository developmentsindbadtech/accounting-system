# Simplified Single-Company Setup

The application has been simplified for single-company use (like QuickBooks Desktop).

## What Changed

1. **Removed multi-tenancy complexity** - No separate databases or tenant switching
2. **Auto-set tenant_id to 1** - All records automatically use tenant_id = 1
3. **Simplified middleware** - Tenant middleware now just ensures tenant_id=1 exists
4. **Global scope** - Models automatically filter by tenant_id=1

## How It Works

- All models use `HasTenantScope` trait that:
  - Automatically sets `tenant_id = 1` when creating records
  - Automatically filters queries to only show tenant_id = 1 records
- User model defaults to `tenant_id = 1`
- Controllers don't need to filter by tenant_id anymore (trait handles it)

## Benefits

✅ **Much simpler setup** - No need for separate databases
✅ **Easier to use** - Works like QuickBooks Desktop (one company)
✅ **Same database structure** - Can add multi-tenancy later if needed
✅ **No tenant switching** - Just login and use

## Future: Adding Multi-Tenancy Back

If you need multi-tenancy later:
1. Remove `HasTenantScope` trait from models
2. Re-enable tenant middleware in routes
3. Update controllers to filter by `auth()->user()->tenant_id`
4. Set up separate databases per tenant

For now, enjoy the simplified single-company setup! 🚀

