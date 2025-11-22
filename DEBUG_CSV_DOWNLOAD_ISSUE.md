# Debug Guide: CSV Download Not Working for Admin

## Issue
Admin users cannot download CSV files from any of these pages:
- Chart of Accounts
- Journal Entries
- Customers
- Invoices
- Vendors
- Bills
- Inventory
- Fixed Assets

---

## Step-by-Step Debugging

### 1. Check Your User Role in Database

Run this command to check your user's actual role:

```powershell
php artisan tinker
```

Then run:
```php
$user = \App\Models\User::where('email', 'your@email.com')->first();
echo "Role: " . $user->role . PHP_EOL;
echo "Is Admin: " . ($user->isAdmin() ? 'YES' : 'NO') . PHP_EOL;
echo "Can Edit: " . ($user->canEdit() ? 'YES' : 'NO') . PHP_EOL;
exit
```

**Expected Output for Admin:**
```
Role: admin
Is Admin: YES
Can Edit: YES
```

**If the role is NOT 'admin'**, update it:
```php
$user = \App\Models\User::where('email', 'your@email.com')->first();
$user->role = 'admin';
$user->save();
echo "Updated to admin" . PHP_EOL;
exit
```

---

### 2. Check Browser Debug URL

**While logged in**, visit:
```
http://localhost:9003/debug/user-info
```

This will show your permissions in JSON format.

**Expected output:**
```json
{
  "authenticated": true,
  "user": {
    "role": "admin"
  },
  "permissions": {
    "canEdit()": true
  }
}
```

**If `canEdit()` is `false`**, your role is not set correctly in the database.

---

### 3. Visual Check on the Page

Go to: `http://localhost:9003/customers`

#### What do you see?

**Case A: Green "Download CSV" Button (Can Click)**
- ✅ Permissions are correct
- Issue is likely in the route or controller
- Try clicking it and check browser console (F12) for errors

**Case B: Grayed Out "Download CSV" Button (Cannot Click)**
- ❌ Permissions are NOT correct
- Your `canEdit()` is returning `false`
- Your role in database is not 'admin' or 'accountant'
- Fix: Update your role in database (see Step 1)

**Case C: No "Download CSV" Button at All**
- ❌ Views are not loading correctly
- Try: Hard refresh (Ctrl+Shift+R)
- Try: Clear browser cache
- Check: Browser console for JavaScript errors

---

### 4. Test Direct URL Access

While logged in, try accessing export URL directly:

```
http://localhost:9003/customers/export
```

**Possible Results:**

**A) CSV File Downloads Successfully**
- ✅ Backend works fine
- ❌ Frontend button issue
- Check browser console for JavaScript errors

**B) 403 Forbidden Error**
- ❌ Middleware is blocking you
- Your role is not 'admin' or 'accountant'
- Fix: Update role in database

**C) 500 Internal Server Error**
- ❌ Controller/database error
- Check Laravel logs: `tail storage/logs/laravel.log`

**D) Redirected to Login Page**
- ❌ Not authenticated
- Your session expired
- Re-login and try again

**E) Blank Page / No Response**
- ❌ Route not registered
- Run: `php artisan route:clear`
- Check route exists: `php artisan route:list | grep export`

---

### 5. Check Laravel Logs

If you get any errors, check the logs:

**Windows:**
```powershell
Get-Content storage/logs/laravel.log -Tail 50
```

**Or just open the file:**
```
storage/logs/laravel.log
```

Look for errors related to:
- `customers/export`
- `CheckRole`
- `403`
- `User`

---

### 6. Verify Routes Are Registered

Check if export routes exist:

```powershell
php artisan route:list | Select-String "export"
```

You should see:
```
GET  /customers/export              customers.export
GET  /invoices/export               invoices.export
GET  /bills/export                  bills.export
...
```

If you DON'T see them, run:
```powershell
php artisan route:clear
php artisan config:clear
```

---

## Common Issues & Solutions

### Issue 1: Role is NULL or Not Set

**Check:**
```powershell
php artisan tinker
```
```php
\App\Models\User::where('email', 'your@email.com')->first()->role;
```

**Fix:**
```php
$user = \App\Models\User::where('email', 'your@email.com')->first();
$user->role = 'admin';
$user->save();
```

---

### Issue 2: Button Shows But Doesn't Work (JavaScript Issue)

**Check browser console (F12) for errors.**

**Try:** Hard refresh the page (Ctrl+Shift+R)

**Check:** Make sure Vite dev server is running:
```powershell
npm run dev
```

---

### Issue 3: 403 Forbidden When Clicking Button

**Cause:** Middleware is blocking the request.

**Check your role:**
```powershell
php artisan tinker
```
```php
$user = auth()->user();
echo "Role: " . $user->role . PHP_EOL;
echo "Can Edit: " . ($user->canEdit() ? 'YES' : 'NO') . PHP_EOL;
```

**Fix:** Make sure role is exactly 'admin' or 'accountant' (lowercase).

---

### Issue 4: Views Not Updated

**Clear all caches:**
```powershell
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

**Hard refresh browser:**
- Chrome/Edge: `Ctrl + Shift + R`
- Firefox: `Ctrl + F5`

---

### Issue 5: Button Shows for Viewer (Wrong)

**This means the view is not using the correct check.**

**Verify the view has:**
```blade
@if(auth()->user()->canEdit())
    <a href="{{ route('customers.export') }}">Download CSV</a>
@else
    <span class="grayed-out">Download CSV</span>
@endif
```

---

## Quick Fix Script

Create a file `fix-user-role.php` in project root:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Replace with your email
$email = 'your@email.com';

$user = \App\Models\User::where('email', $email)->first();

if (!$user) {
    echo "User not found: $email\n";
    exit(1);
}

echo "Current role: " . ($user->role ?? 'NULL') . "\n";

$user->role = 'admin';
$user->is_active = true;
$user->save();

echo "Updated to: admin\n";
echo "Can edit: " . ($user->canEdit() ? 'YES' : 'NO') . "\n";
```

Run:
```powershell
php fix-user-role.php
```

---

## What to Share With Me

Please run the debug URL and share the output:
```
http://localhost:9003/debug/user-info
```

And tell me:
1. What do you see on the customers page? (Green button, grayed button, or no button?)
2. What happens when you visit: `http://localhost:9003/customers/export`
3. Any errors in browser console (F12)?
4. What does `php artisan tinker` show for your user role?

---

## Expected Behavior

### For Admin User:
- `user.role` = `'admin'`
- `canEdit()` = `true`
- Button: **Green, clickable**
- Clicking button: **Downloads CSV file**

### For Accountant User:
- `user.role` = `'accountant'`
- `canEdit()` = `true`
- Button: **Green, clickable**
- Clicking button: **Downloads CSV file**

### For Viewer User:
- `user.role` = `'viewer'`
- `canEdit()` = `false`
- Button: **Grayed out, not clickable**
- Tooltip: "Viewer: No permission"

---

**Date:** November 21, 2025

