# Profile Management - Planning Summary

## 📋 Overview
Fitur untuk user edit profile mereka sendiri (nama, email, password, alamat, telepon)

---

## 🎯 Key Features

### 1. Profile Page (`profile.php`)
**Sections:**
- **View Mode** - Lihat informasi profile saat ini
- **Edit Mode** - Form untuk update profile

**Edit Forms (Tab-based):**
- **Tab 1: Basic Info** - Edit name, email, phone
- **Tab 2: Change Password** - Ganti password dengan verifikasi
- **Tab 3: Shipping Address** - Update alamat default untuk checkout

---

## 🗄️ Database Changes Needed

Tabel `users` saat ini **TIDAK** punya kolom `phone` dan `address`.

**Migration Required:**
```sql
ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email;
ALTER TABLE users ADD COLUMN address TEXT AFTER phone;
```

---

## 🔐 Security Features

✅ **Authentication:** Harus login untuk akses  
✅ **Authorization:** User hanya bisa edit profile sendiri  
✅ **Password Verification:** Ganti password butuh password lama  
✅ **Email Uniqueness:** Validate email tidak duplikat  
✅ **CSRF Protection:** Semua forms protected

---

## ✨ UI/UX Highlights

🎨 **Design:**
- Card-based layout
- Tab navigation untuk organize forms
- Icons untuk setiap field
- Avatar placeholder (initial dari nama)
- Member badge dengan tanggal join

🔄 **Interactions:**
- Toggle view/edit mode
- Tab switching
- Inline validation
- Success animations

---

## 📱 Navbar Integration

**Before:**
```
Home | Produk | My Orders | Logout | 🛒
```

**After:**
```
Home | Produk | My Orders | Profile | Logout | 🛒
```

---

## ✅ Validation Rules

### Basic Info
- **Name:** Required, min 3 characters
- **Email:** Required, valid format, unique
- **Phone:** Optional, valid format

### Change Password
- **Current Password:** Must match database
- **New Password:** Min 6 characters
- **Confirm Password:** Must match new password

### Address
- **Address:** Optional, no specific format

---

## 🧪 Testing Checklist

- [ ] View profile as logged-in user
- [ ] Update name successfully
- [ ] Update email (test unique constraint)
- [ ] Change password (test current password verification)
- [ ] Update address/phone
- [ ] Test validation errors
- [ ] Test as guest (should redirect to login)
- [ ] Test security (can't edit other user's profile)

---

## 🚀 Implementation Steps

1. **Database Migration** - Add phone & address columns
2. **Create profile.php** - Main profile page
3. **Update navbar** - Add Profile link
4. **Test thoroughly** - All validation & security

---

## 💡 Future Enhancements (Optional)

- Profile picture upload
- Email verification
- Two-factor authentication
- Activity log
- Delete account
- Password strength meter
