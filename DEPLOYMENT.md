# 🚀 Deployment Guide - Railway.com

Panduan lengkap untuk deploy aplikasi **Shop Online** ke Railway.com menggunakan Docker.

## 📋 Prerequisites

- Akun Railway.com (gratis: https://railway.app)
- Git repository (GitHub, GitLab, atau Bitbucket)
- Database schema (`database.sql`)

---

## 🔧 Step 1: Persiapan Repository

### 1.1 Push Code ke Git Repository

```bash
# Initialize git (jika belum)
git init

# Add all files
git add .

# Commit
git commit -m "Add deployment configuration"

# Add remote (ganti dengan URL repository Anda)
git remote add origin https://github.com/username/shop-online.git

# Push
git push -u origin main
```

### 1.2 Verifikasi File Deployment

Pastikan file-file berikut ada di repository:
- ✅ `Dockerfile`
- ✅ `nginx.conf`
- ✅ `init.sh`
- ✅ `.dockerignore`
- ✅ `railway.json`
- ✅ `.env.example`

---

## 🗄️ Step 2: Setup Database di Railway

### 2.1 Login ke Railway
1. Buka https://railway.app
2. Login dengan GitHub/GitLab/Email

### 2.2 Create New Project
1. Click **"New Project"**
2. Pilih **"Provision MySQL"**
3. Database akan otomatis dibuat

### 2.3 Get Database Credentials
1. Click pada MySQL service
2. Tab **"Variables"**
3. Copy credentials:
   - `MYSQL_HOST`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`
   - `MYSQL_DATABASE`
   - `MYSQL_PORT`
   - `DATABASE_URL` (format lengkap)

### 2.4 Import Database Schema

**Option A: Via Railway CLI**
```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link to project
railway link

# Connect to MySQL
railway run mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < database.sql
```

**Option B: Via MySQL Client**
```bash
mysql -h <MYSQL_HOST> -P <MYSQL_PORT> -u <MYSQL_USER> -p<MYSQL_PASSWORD> <MYSQL_DATABASE> < database.sql
```

**Option C: Via phpMyAdmin/Adminer**
1. Deploy Adminer di Railway (optional)
2. Import `database.sql` via web interface

---

## 🚢 Step 3: Deploy Application

### 3.1 Add Application Service
1. Di Railway project, click **"New"**
2. Pilih **"GitHub Repo"** atau **"GitLab Repo"**
3. Authorize Railway dan pilih repository `shop-online`
4. Railway akan otomatis detect `Dockerfile`

### 3.2 Configure Environment Variables
1. Click pada application service
2. Tab **"Variables"**
3. Add variables berikut:

```env
DATABASE_URL=${{MySQL.DATABASE_URL}}
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_USER=${{MySQL.MYSQL_USER}}
DB_PASS=${{MySQL.MYSQL_PASSWORD}}
DB_NAME=${{MySQL.MYSQL_DATABASE}}
DB_PORT=${{MySQL.MYSQL_PORT}}
APP_ENV=production
```

> **💡 Tip:** Railway supports variable references dengan `${{ServiceName.VARIABLE}}`

### 3.3 Deploy
1. Railway akan otomatis build dan deploy
2. Monitor logs di tab **"Deployments"**
3. Tunggu hingga status **"Success"**

### 3.4 Get Public URL
1. Tab **"Settings"**
2. Section **"Networking"**
3. Click **"Generate Domain"**
4. Copy public URL (contoh: `shop-online-production.up.railway.app`)

---

## ✅ Step 4: Verification

### 4.1 Test Application
1. Buka public URL
2. Test halaman:
   - ✅ Homepage (`/`)
   - ✅ Products (`/products.php`)
   - ✅ Login (`/login.php`)
   - ✅ Register (`/register.php`)

### 4.2 Test Admin Login
1. Buka `/admin`
2. Login dengan:
   - Email: `admin@shop.com`
   - Password: `admin123`
3. Verifikasi dashboard admin

### 4.3 Test Upload
1. Login sebagai admin
2. Tambah produk baru dengan gambar
3. Verifikasi gambar ter-upload

---

## 🔍 Troubleshooting

### ❌ Build Failed

**Error: `Cannot find Dockerfile`**
```bash
# Pastikan Dockerfile ada di root repository
ls -la Dockerfile
```

**Error: `PHP extension not found`**
- Check `Dockerfile` sudah install semua extensions
- Rebuild dengan `railway up`

### ❌ Database Connection Failed

**Error: `SQLSTATE[HY000] [2002] Connection refused`**

1. Verifikasi environment variables:
```bash
railway variables
```

2. Check database service running:
   - Railway dashboard → MySQL service → Status

3. Test connection manual:
```bash
railway run mysql -h $MYSQL_HOST -u $MYSQL_USER -p
```

### ❌ Upload Not Working

**Error: Permission denied**

1. Check `init.sh` executable:
```bash
chmod +x init.sh
git add init.sh
git commit -m "Make init.sh executable"
git push
```

2. Verify uploads directory created:
   - Check logs: `railway logs`
   - Look for: `mkdir -p /var/www/html/uploads`

### ❌ 502 Bad Gateway

**Nginx can't connect to PHP-FPM**

1. Check logs:
```bash
railway logs
```

2. Verify `init.sh` starts both services:
   - PHP-FPM: `php-fpm -D`
   - Nginx: `nginx -g 'daemon off;'`

---

## 🔄 Update Deployment

### Push Updates
```bash
# Make changes
git add .
git commit -m "Update feature"
git push

# Railway will auto-deploy
```

### Rollback
1. Railway dashboard → Deployments
2. Click previous successful deployment
3. Click **"Redeploy"**

---

## 💰 Railway Pricing

### Free Tier (Hobby Plan)
- $5 credit per month
- ~500 hours runtime
- Perfect untuk testing/development

### Pro Plan
- $20/month
- Unlimited projects
- Better resources

> **💡 Tip:** Monitor usage di Railway dashboard

---

## 🔐 Security Checklist

- ✅ Change default admin password
- ✅ Use strong database password
- ✅ Enable HTTPS (Railway provides free SSL)
- ✅ Set `APP_ENV=production`
- ✅ Disable error display in production
- ✅ Regular database backups

---

## 📚 Additional Resources

- [Railway Docs](https://docs.railway.app)
- [Railway CLI](https://docs.railway.app/develop/cli)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)

---

## 🆘 Need Help?

1. Check Railway logs: `railway logs`
2. Railway Discord: https://discord.gg/railway
3. Railway GitHub: https://github.com/railwayapp/railway

---

**Happy Deploying! 🚀**
