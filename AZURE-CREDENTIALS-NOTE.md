# Azure AD Credentials - Important Note

## 🔒 Security Notice

**The actual Azure AD credentials are NOT stored in this repository.**

GitHub's secret scanning protection has been triggered to prevent accidental exposure of sensitive credentials.

---

## 📝 For DevOps Team

The production `.env` file on the server should contain the actual Azure AD credentials:

```env
# Azure AD Single Sign-On Configuration
AZURE_AD_TENANT_ID=<actual-tenant-id>
AZURE_AD_CLIENT_ID=<actual-client-id>
AZURE_AD_CLIENT_SECRET=<actual-client-secret>
AZURE_AD_REDIRECT_URI=https://stas.sindbad.tech/login/azure/callback
```

**Where to get these values:**
1. Login to [Azure Portal](https://portal.azure.com)
2. Navigate to: Azure Active Directory → App Registrations
3. Select your application
4. Copy the values:
   - **Tenant ID**: Overview → Directory (tenant) ID
   - **Client ID**: Overview → Application (client) ID
   - **Client Secret**: Certificates & secrets → Client secrets
   - **Redirect URI**: Must be exactly `https://stas.sindbad.tech/login/azure/callback`

---

## ✅ What Changed in This Deploy

**Code Changes (No secrets involved):**
- ✅ Added CSRF exception for Azure OAuth callback in `bootstrap/app.php`
- ✅ Auto-configured session security for HTTPS in `config/session.php`
- ✅ Enhanced error handling in `app/Http/Controllers/Auth/AzureController.php`

**Documentation (Placeholders only):**
- All documentation files use placeholder values like `your-tenant-id-here`
- No actual secrets are committed to the repository
- DevOps manages actual credentials via server `.env` file

---

## 🚀 Deployment Process

1. **Code is pulled** from repository (no secrets in code)
2. **DevOps ensures** `.env` file has correct Azure credentials
3. **Application reads** credentials from `.env` at runtime
4. **Never commit** the actual `.env` file to git

---

## 🔐 Security Best Practices

✅ **DO:**
- Store credentials in `.env` file on server only
- Restrict `.env` file permissions: `chmod 600 .env`
- Keep `.env` in `.gitignore`
- Rotate secrets regularly
- Use environment-specific credentials (dev, staging, prod)

❌ **DON'T:**
- Commit `.env` file to git
- Share credentials in Slack/email
- Hardcode credentials in code
- Use production credentials in development
- Store credentials in documentation

---

## 📖 Related Documentation

- `SSO-CONFIGURATION-GUIDE.md` - Complete SSO setup guide
- `PRODUCTION-ENV-TEMPLATE.md` - Environment template with placeholders
- `SSO-FIX-DEPLOYMENT-INSTRUCTIONS.md` - Deployment instructions

All documentation uses placeholder values. DevOps provides actual credentials.

---

**Last Updated:** November 22, 2025  
**Security Status:** ✅ No secrets in repository

