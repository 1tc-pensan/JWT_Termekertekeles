# 🚀 POSTMAN COLLECTION - GYORS INDÍTÁS

## ❗ FONTOS - OLVASD EL ELŐSZÖR!

### 1. XAMPP ellenőrzés
Győződj meg róla, hogy az **XAMPP Apache és MySQL** fut!

### 2. Admin felhasználó létezik
Az adatbázisban már van egy admin felhasználó:
- **Email:** `admin@example.com`
- **Password:** `admin123`

Normál user:
- **Email:** `testuser@example.com`  
- **Password:** `password123`

---

## 📥 IMPORTÁLÁS POSTMANBE

### 1. lépés: Nyisd meg a Postmant

### 2. lépés: Import
1. Kattints a bal felső sarokban az **Import** gombra
2. Válaszd ki a fájlt: `c:\xampp\htdocs\lk\JWT_Termekertekeles\docs\Postman_Collection.json`
3. Kattints az **Import** gombra

### 3. lépés: Ellenőrzés
A bal oldali menüben meg kell jelennie a **"JWT Product Reviews API - WORKING COLLECTION"** nevű collection-nek.

---

## ✅ HASZNÁLAT - LÉPÉSRŐL LÉPÉSRE

### STEP 1: ADMIN LOGIN (KÖTELEZŐ ELSŐ LÉPÉS!)

1. Nyisd ki a collection-t
2. **Auth** mappa → **Admin Login**
3. Kattints a **Send** gombra

**Mit látsz:**
```json
{
    "token": "eyJ0eXAiOi...",
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "role": "admin"
    }
}
```

**FONTOS:** A token automatikusan mentődik az `{{admin_token}}` változóba!  
(Nézd meg a Console-ban: Ctrl+Alt+C)

---

### STEP 2: TESZTELJ EGY ADMIN VÉGPONTOT

Próbáld ki: **Admin → Users → Get All Users**

Kattints **Send**

**Mit látsz:**
```json
{
    "data": [
        {
            "user": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "stats": {
                "totalReviews": 0,
                "averageRating": 0
            }
        }
    ]
}
```

---

### STEP 3: NORMÁL USER LOGIN

Próbáld ki: **Auth → Login**

```json
{
    "email": "testuser@example.com",
    "password": "password123"
}
```

A token automatikusan mentődik a `{{jwt_token}}` változóba.

---

### STEP 4: TERMÉK LÉTREHOZÁSA (ADMIN)

**Admin → Products → Create Product (Admin)**

Kattints **Send** - Használja automatikusan az `{{admin_token}}`-t!

---

### STEP 5: ÉRTÉKELÉS ÍRÁSA (USER)

Először: **Auth → Login** (normál user)

Majd: **Reviews → Create Review**

---

## 🔧 HIBAELHÁRÍTÁS

### "The requested URL was not found"
- **OK:** Az XAMPP Apache nem fut
- **Megoldás:** Indítsd el az XAMPP Control Panel-ben

### "Unauthenticated"
- **OK:** Nincs token vagy lejárt
- **Megoldás:** Futtasd újra az **Admin Login** vagy **Login** requestet

### "This action is unauthorized"
- **OK:** User token-t használsz admin végpontra
- **Megoldás:** Futtasd az **Admin Login** requestet

### Collection üres importálás után
1. Töröld ki a collection-t a Postmanból
2. File → Settings → Data → Clear Postman Data
3. Importáld újra

### Token nem mentődik
1. Nyisd ki a Console-t (Ctrl+Alt+C)
2. Futtasd a Login requestet
3. Látnod kell: `Admin token saved: eyJ0eXA...`
4. Ha nem látod:
   - Jobb klikk a collection-re → **Edit**
   - **Variables** tab
   - Ellenőrizd, hogy léteznek-e: `jwt_token`, `admin_token`, `base_url`

---

## 📝 GYORS REFERENCIA

### Collection változók:
- `{{base_url}}` = `http://localhost/lk/JWT_Termekertekeles/public`
- `{{jwt_token}}` = Normál user token (auto)
- `{{admin_token}}` = Admin token (auto)

### Admin felhasználó:
- Email: `admin@example.com`
- Password: `admin123`

### Normál felhasználó:
- Email: `testuser@example.com`
- Password: `password123`

### Tokenek élettartama:
- 60 perc (1 óra)
- Lejárat után újra kell loginolni

---

## 🎯 TIPPEK

### 1. Console figyelése
Mindig nyisd ki a Console-t (Ctrl+Alt+C) - itt látod a token mentést és a hibákat.

### 2. Token ellenőrzés
**Auth → User Profile** requesttel ellenőrizheted, hogy be vagy-e jelentkezve.

### 3. ID-k cseréje
Az URL-ekben szereplő `/1` ID-kat cserélheted a te adatbázisodban lévő ID-kra.

### 4. Automatikus token
Ne másold be kézzel a tokent - az automatikusan bekerül minden requestbe!

---

## ✅ MŰKÖDIK?

Ha minden jól megy, akkor:

1. ✅ Admin Login → 200 OK + token
2. ✅ Get All Users → 200 OK + user lista
3. ✅ Create Product → 201 Created
4. ✅ Login (user) → 200 OK + token  
5. ✅ Create Review → 201 Created

**Ha mindegyik működik: GRATULÁLOK! 🎉**

---

## 🆘 SUPPORT

Ha még mindig nem működik:
1. Ellenőrizd az XAMPP Apache és MySQL állapotát
2. Ellenőrizd: `http://localhost/lk/JWT_Termekertekeles/public` betöltődik-e a böngészőben
3. Futtasd: `php artisan test` - mind a 36 tesztnek sikeresnek kell lennie
4. Nézd meg a Laravel log-okat: `storage/logs/laravel.log`

**Teszteléshez használd:**
```bash
cd c:\xampp\htdocs\lk\JWT_Termekertekeles
php artisan test
```

Eredmény: **Tests: 36 passed** ✅
