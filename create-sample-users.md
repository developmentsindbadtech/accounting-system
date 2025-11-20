# Create Sample Users (Accountant & Viewer)

## Quick Method: Using Tinker

Run this command:

```bash
php artisan tinker
```

Then paste this code:

```php
// Get the first tenant
$tenant = \App\Models\Tenant::first();

// Create Accountant User
\App\Models\User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Accountant User',
    'email' => 'accountant@demo.com',
    'password' => bcrypt('password'),
    'role' => 'accountant',
    'is_active' => true,
]);

// Create Viewer User
\App\Models\User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Viewer User',
    'email' => 'viewer@demo.com',
    'password' => bcrypt('password'),
    'role' => 'viewer',
    'is_active' => true,
]);

// Verify users were created
\App\Models\User::all(['name', 'email', 'role']);
```

Type `exit` to leave tinker.

## Login Credentials

### Accountant Account
- **Email:** `accountant@demo.com`
- **Password:** `password`
- **Role:** `accountant`
- **Permissions:** Can create and edit records, but cannot manage users/system settings

### Viewer Account
- **Email:** `viewer@demo.com`
- **Password:** `password`
- **Role:** `viewer`
- **Permissions:** Read-only access, cannot create or edit records

### Admin Account (Already exists)
- **Email:** `admin@demo.com`
- **Password:** `password`
- **Role:** `admin`
- **Permissions:** Full access to everything

## Test Different Roles

1. **Login as Accountant:**
   - Try creating a journal entry ✅
   - Try creating an invoice ✅
   - Try viewing reports ✅
   - Try accessing admin settings ❌ (should be restricted)

2. **Login as Viewer:**
   - Try viewing dashboard ✅
   - Try viewing reports ✅
   - Try creating a journal entry ❌ (should be restricted)
   - Try editing records ❌ (should be restricted)

## Alternative: Direct Database Insert

If you prefer to use SQL directly:

```sql
-- For PostgreSQL
INSERT INTO users (tenant_id, name, email, password, role, is_active, created_at, updated_at)
VALUES 
(1, 'Accountant User', 'accountant@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant', true, NOW(), NOW()),
(1, 'Viewer User', 'viewer@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer', true, NOW(), NOW());
```

**Note:** The password hash above is for `password`. Use Laravel's `bcrypt('password')` for proper hashing.

