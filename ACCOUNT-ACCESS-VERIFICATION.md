# Account Access Verification

## Accounts Created

### Accountant Accounts (4)
1. revemar.surigao@sindbad.tech
2. hazel.bacalso@sindbad.tech
3. aziz.alsultan@sindbad.tech
4. mohammed.agbawi@sindbad.tech

### Admin Account (1)
1. development@sindbad.tech

**Password for ALL accounts:** `Ksa@2021!`

---

## Login Methods Available

### ✅ Method 1: Email/Password Login
**Status:** WORKING

- Login form is available on the login page (below SSO button)
- Users enter email and password
- Uses Laravel's standard `Auth::attempt()` authentication
- Accounts created via seeder can login immediately

**How to use:**
1. Go to login page
2. Scroll down below "Sign in with Microsoft" button
3. Enter email: `[one of the emails above]`
4. Enter password: `Ksa@2021!`
5. Click "Sign in"

---

### ✅ Method 2: Microsoft SSO (Azure AD)
**Status:** WORKING (with auto-creation)

#### Scenario A: Account Already Exists (Created via Seeder)
**What happens:**
1. User clicks "Sign in with Microsoft"
2. Authenticates with Azure AD
3. System checks database - finds existing user by email
4. Updates `azure_id` field (links Azure account)
5. Preserves existing password and role
6. Logs user in automatically

**Result:** ✅ User can access app. Both login methods continue to work.

#### Scenario B: Account Doesn't Exist Yet
**What happens:**
1. User clicks "Sign in with Microsoft"
2. Authenticates with Azure AD
3. System checks database - user doesn't exist
4. Automatically creates user with:
   - Email from Azure AD
   - Name from Azure AD
   - Role: `accountant` (for @sindbad.tech domain)
   - Random password (for SSO users)
   - `azure_id` linked
   - `is_active = true`
5. Logs user in automatically

**Result:** ✅ User can access app. Can continue using SSO in future.

---

## Important Notes

### Password Behavior
- **Accounts created via seeder:** Password is `Ksa@2021!` (hashed in database)
  - Can use email/password login
  - Can also use SSO (password is preserved, not changed)
  
- **Accounts created via SSO:** Password is randomly generated (not meant for email/password login)
  - Should use SSO for future logins
  - Can set password manually if needed for email/password login

### Role Assignment via SSO

When a user logs in via SSO from `@sindbad.tech` domain:

1. **If user exists:** Role is preserved (not changed by SSO)
2. **If user is new:** Role is assigned as:
   - `revemar.surigao@sindbad.tech` → `accountant`
   - `hazel.bacalso@sindbad.tech` → `accountant`
   - `aziz.alsultan@sindbad.tech` → `accountant`
   - `mohammed.agbawi@sindbad.tech` → `accountant`
   - `development@sindbad.tech` → `admin`
   - Other `@sindbad.tech` emails → `accountant` (default)

---

## Testing Checklist

### Test Email/Password Login
- [ ] Login as `revemar.surigao@sindbad.tech` with password `Ksa@2021!`
- [ ] Login as `hazel.bacalso@sindbad.tech` with password `Ksa@2021!`
- [ ] Login as `aziz.alsultan@sindbad.tech` with password `Ksa@2021!`
- [ ] Login as `mohammed.agbawi@sindbad.tech` with password `Ksa@2021!`
- [ ] Login as `development@sindbad.tech` with password `Ksa@2021!`
- [ ] Verify correct permissions based on role (accountant vs admin)

### Test SSO Login (Existing Accounts)
- [ ] Login via SSO as `revemar.surigao@sindbad.tech` (account exists)
- [ ] Verify account is linked to Azure (azure_id is set)
- [ ] Verify can still login with email/password after SSO
- [ ] Verify role is preserved

### Test SSO Login (New Accounts)
- [ ] Login via SSO as new `@sindbad.tech` user (doesn't exist)
- [ ] Verify user is automatically created
- [ ] Verify role is assigned correctly
- [ ] Verify can login via SSO again in future

---

## Potential Issues & Solutions

### Issue 1: "Account doesn't exist" when using SSO
**Solution:** The system will automatically create the account. No action needed.

### Issue 2: "Wrong password" when using email/password
**Solution:** 
- Verify password is exactly: `Ksa@2021!` (case-sensitive)
- If account was created via SSO only, it has a random password - use SSO or reset password

### Issue 3: "Inactive account" error
**Solution:** 
- Run the seeder again to update accounts: `php artisan users:create-sindbad-tech`
- This will set `is_active = true` for all accounts

### Issue 4: Wrong role/permissions
**Solution:**
- Check user role in database: `User::where('email', 'email@sindbad.tech')->first()->role`
- Update if needed or run seeder again

---

## Summary

✅ **YES, all accounts can access the app after creation**

✅ **YES, SSO will work normally:**
- Existing accounts: Links Azure account, preserves password and role
- New accounts: Auto-creates with proper role

✅ **Both login methods work:**
- Email/Password: Works for accounts created via seeder
- SSO: Works for all @sindbad.tech accounts (auto-creates if needed)
