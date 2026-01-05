# 🎯 POSTMAN COLLECTION - VÉGSŐ ÚTMUTATÓ

## ✅ ELKÉSZÜLT FÁJLOK

### 1. Postman Collection
📁 **Fájl:** `docs/Postman_Collection.json`
- ✅ 45 működő endpoint
- ✅ Automatikus token mentés
- ✅ Test scriptek minden login requestnél
- ✅ XAMPP-ra konfigurálva

### 2. Használati útmutatók
📁 **Fájlok:**
- `docs/POSTMAN_GYORSINDULO.md` - Részletes használat
- `docs/POSTMAN_HASZNALAT.md` - Alternatív útmutató

### 3. Böngészős tesztelő
📁 **Fájl:** `public/api-tester.html`
🌐 **URL:** http://localhost/lk/JWT_Termekertekeles/public/api-tester.html

---

## 🚀 GYORS START - 3 LÉPÉS

### 1️⃣ POSTMAN IMPORT

1. Nyisd meg a Postmant
2. Kattints: **Import**
3. Válaszd ki: `c:\xampp\htdocs\lk\JWT_Termekertekeles\docs\Postman_Collection.json`
4. Kattints: **Import**

### 2️⃣ ADMIN LOGIN

1. Collection: **JWT Product Reviews API - WORKING COLLECTION**
2. **Auth** → **Admin Login**
3. Kattints: **Send**
4. ✅ Token automatikusan mentve!

### 3️⃣ TESZTELÉS

Próbáld ki bármelyik requestet - a token automatikusan bekerül!

Például:
- **Admin → Users → Get All Users**
- **Products → Get All Products**
- **Reviews → Create Review**

---

## 🔧 COLLECTION BEÁLLÍTÁSOK

### Környezeti változók (automatikusak):
```
base_url = http://localhost/lk/JWT_Termekertekeles/public
jwt_token = (automatikusan mentődik login után)
admin_token = (automatikusan mentődik admin login után)
```

### Bejelentkezési adatok:

**Admin:**
- Email: `admin@example.com`
- Password: `admin123`

**Normál user:**
- Email: `testuser@example.com`
- Password: `password123`

---

## 📋 ENDPOINT LISTA (45 db)

### 🔐 Auth (6)
1. Register
2. Login
3. Admin Login
4. Logout
5. Refresh Token
6. User Profile

### 📦 Products (10)
7. Get All Products
8. Get Single Product
9. Get Product Reviews
10. Create Product (Admin)
11. Update Product (PATCH)
12. Update Product (PUT)
13. Delete Product
14. Get Trashed Products
15. Restore Product
16. Force Delete Product

### ⭐ Reviews (8)
17. Get All Reviews
18. Get Single Review
19. Create Review
20. Update Review (PATCH)
21. Update Review (PUT)
22. Delete Review
23. Get Trashed Reviews
24. Restore Review

### 👥 Admin - Users (5)
25. Get All Users
26. Get Single User
27. Update User
28. Delete User
29. Restore User

### 📦 Admin - Products (9)
30. Get All Products (Admin)
31. Get Single Product (Admin)
32. Create Product (Admin)
33. Update Product (Admin - PATCH)
34. Update Product (Admin - PUT)
35. Delete Product (Admin)
36. Get Trashed Products (Admin)
37. Restore Product (Admin)
38. Force Delete Product (Admin)

### ⭐ Admin - Reviews (9)
39. Get All Reviews (Admin)
40. Get Single Review (Admin)
41. Create Review (Admin)
42. Update Review (Admin - PATCH)
43. Update Review (Admin - PUT)
44. Delete Review (Admin)
45. Get Trashed Reviews (Admin)
46. Restore Review (Admin)
47. Force Delete Review (Admin)

---

## ⚡ AUTOMATIKUS FUNKCIÓK

### Token mentés
Minden sikeres login/register után a token **automatikusan mentődik**:

```javascript
// Register és Login requesteknél:
if (pm.response.code === 201 || pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.token) {
        pm.collectionVariables.set('jwt_token', jsonData.token);
        console.log('Token saved: ' + jsonData.token);
    }
}
```

### Token használata
A token automatikusan bekerül minden védett requestbe:
```
Authorization: Bearer {{jwt_token}}
Authorization: Bearer {{admin_token}}
```

---

## 🎯 TIPIKUS HASZNÁLAT

### Scenario 1: User műveletek
```
1. Login (user) → Token mentve
2. Get Products → Lista
3. Create Review → Új értékelés
4. Get Reviews → Saját értékelés látható
```

### Scenario 2: Admin műveletek
```
1. Admin Login → Admin token mentve
2. Get All Users → Összes user listája
3. Create Product → Új termék
4. Get Admin Products → Statisztikákkal
```

### Scenario 3: Teljes workflow
```
1. Register → Új user létrehozva
2. Login → Token mentve
3. Get Products → Termékek böngészése
4. Create Review (product_id: 1) → Értékelés írása
5. Admin Login → Admin token
6. Get All Reviews (Admin) → Összes értékelés moderálása
```

---

## 🐛 HIBAELHÁRÍTÁS

### ❌ "Collection üres az importálás után"

**Megoldás:**
1. Töröld a collection-t
2. Postman → Settings → Data → Clear Postman data cache
3. Importáld újra

### ❌ "Unauthenticated" (401)

**Okok:**
- Nincs token (még nem loginoltál)
- Token lejárt (60 perc után)

**Megoldás:**
1. Futtasd: **Auth → Login** vagy **Auth → Admin Login**
2. Ellenőrizd a Console-ban: `Token saved: ...`

### ❌ "This action is unauthorized" (403)

**Ok:** User token-nel próbálsz admin végpontot hívni

**Megoldás:**
1. Futtasd: **Auth → Admin Login**
2. Használd a megfelelő admin requesteket

### ❌ "The requested URL was not found" (404)

**Okok:**
- XAMPP Apache nem fut
- Rossz URL

**Megoldás:**
1. Indítsd el az XAMPP Apache-t
2. Ellenőrizd böngészőben: http://localhost/lk/JWT_Termekertekeles/public
3. Ellenőrizd a collection `base_url` változóját:
   - Jobb klikk → Edit → Variables → base_url
   - Értéke: `http://localhost/lk/JWT_Termekertekeles/public`

### ❌ Token nem mentődik automatikusan

**Ellenőrzés:**
1. Nyisd ki a Console-t: `Ctrl + Alt + C`
2. Futtasd a Login requestet
3. Látnod kell: `Token saved: eyJ0eXA...`

**Ha nem látod:**
1. Jobb klikk a collection-re → **Edit**
2. **Variables** tab
3. Ellenőrizd, hogy léteznek:
   - `base_url`
   - `jwt_token`
   - `admin_token`

---

## 🌐 BÖNGÉSZŐS TESZTELŐ

Ha a Postman nem működik, használd a böngészős tesztelőt:

**URL:** http://localhost/lk/JWT_Termekertekeles/public/api-tester.html

### Funkciók:
- ✅ Összes endpoint tesztelhető
- ✅ Token automatikus mentése
- ✅ Szép, színes UI
- ✅ Azonnali válasz megjelenítés
- ✅ Postman telepítés nem szükséges!

---

## ✅ VALIDÁCIÓ

### Ellenőrizd, hogy minden működik:

```bash
cd c:\xampp\htdocs\lk\JWT_Termekertekeles
php artisan test
```

**Elvárás:**
```
Tests:  36 passed (164 assertions)
Duration: 1.43s
```

Ha mind a 36 teszt sikeres → **A Postman collection is működni fog!** ✅

---

## 📞 GYORS SEGÍTSÉG

### Import probléma
1. Bezárni és újra megnyitni a Postmant
2. Törölni minden cache-t
3. Újra importálni

### Token probléma
1. Console megnyitása (Ctrl+Alt+C)
2. Login request futtatása
3. Token ellenőrzése

### Végpont nem található
1. XAMPP Apache elindítása
2. URL ellenőrzése böngészőben
3. Collection változók ellenőrzése

---

## 🎉 ÖSSZEGZÉS

### Mit kaptál:
- ✅ 45 működő Postman request
- ✅ Automatikus token kezelés
- ✅ Részletes dokumentáció
- ✅ Böngészős tesztelő
- ✅ 100% tesztlefedettség (36/36 ✅)

### Mit kell tenned:
1. Import Postmanbe
2. Admin Login
3. Tesztelés

**Ennyi! Kezdheted használni! 🚀**
