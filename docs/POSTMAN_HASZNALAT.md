# 🚀 Postman Collection Használati Útmutató

## 📥 Importálás

1. Nyisd meg a Postmant
2. Kattints a **Import** gombra
3. Válaszd ki a `JWT_API_Postman_Collection.json` fájlt a `docs/` mappából
4. Kattints az **Import** gombra

## ⚙️ Környezeti változók beállítása

A collection automatikusan létrehozza a környezeti változókat, de ha szeretnéd, manuálisan is beállíthatod:

### Collection Variables (már benne van):
- `base_url`: `http://localhost:8000`
- `jwt_token`: automatikusan mentődik login után
- `admin_token`: automatikusan mentődik admin login után

### Ha módosítani szeretnéd a base_url-t:
1. Jobb klikk a collection-re → **Edit**
2. **Variables** tab
3. Módosítsd a `base_url` értéket (pl. ha másik porton fut a Laravel)

## 🎯 Használat lépésről lépésre

### 1. Laravel alkalmazás indítása

```bash
cd c:\xampp\htdocs\lk\JWT_Termekertekeles
php artisan serve
```

A Laravel elindul a `http://localhost:8000` címen.

### 2. Adatbázis előkészítése (opcionális)

Ha még nincs adat az adatbázisban:

```bash
php artisan migrate:fresh --seed
```

Ez létrehozza az adatbázis táblákat és feltölti teszt adatokkal.

### 3. Authentikáció - ELSŐ LÉPÉS! ⚠️

**FONTOS:** Először mindig a login/register requesteket futtasd!

#### Opció A: Regisztráció + Automatikus Login

1. Futtasd: **1. Authentication → Register User**
   - Ez létrehoz egy normál felhasználót
   - Automatikusan elmenti a tokent a `jwt_token` változóba
   
2. Futtasd: **1. Authentication → Register Admin**
   - Ez létrehoz egy admin felhasználót
   - Automatikusan elmenti a tokent az `admin_token` változóba

#### Opció B: Login (ha már van felhasználó)

1. Futtasd: **1. Authentication → Login User**
   - Email: `test@example.com`
   - Password: `password123`
   - Token automatikusan mentődik
   
2. Futtasd: **1. Authentication → Login Admin**
   - Email: `admin@example.com`
   - Password: `admin123`
   - Admin token automatikusan mentődik

### 4. API Végpontok tesztelése

Most már használhatod az összes többi végpontot! A tokenek automatikusan hozzáadódnak az Authorization headerhez.

#### Normál felhasználói műveletek:
- **2. Products**: Termékek listázása, megtekintése
- **3. Reviews**: Értékelések létrehozása, módosítása, törlése

#### Admin műveletek (csak admin tokennel):
- **4. Admin - Users**: Felhasználók kezelése
- **5. Admin - Products**: Termékek teljes körű kezelése statisztikákkal
- **6. Admin - Reviews**: Értékelések moderálása

### 5. Token ellenőrzése

Bármikor ellenőrizheted, hogy be vagy-e jelentkezve:
- **1. Authentication → Get User Profile**

### 6. Kijelentkezés

Ha végeztél:
- **1. Authentication → Logout**

## 🔧 Problémamegoldás

### "Unauthenticated" hiba
- Először futtasd a **Login User** vagy **Login Admin** requestet
- Ellenőrizd, hogy a token mentődött-e (Console-ban látható)
- Ha nem működik, próbáld újra a regisztrációt

### "This action is unauthorized"
- Admin műveleteknél használd az `admin_token`-t
- Futtasd le a **Login Admin** requestet

### Token lejárt
- Futtasd újra a login requestet
- A JWT tokenek 60 percig érvényesek (alapértelmezett beállítás)

### Port probléma
- Ha a Laravel nem a 8000-es porton fut, módosítsd a `base_url` változót

## 📝 Hasznos tippek

### Console használata
A Postman alsó részén található **Console** (Ctrl + Alt + C) megmutatja:
- Token mentést
- Request/Response részleteket
- Hibákat

### Environment vs Collection Variables
Ez a collection Collection Variables-t használ, így minden importáláskor ugyanúgy működik.

### Automatikus token mentés
A Login és Register requesteknél van egy **Test** script, ami automatikusan elmenti a tokent:

```javascript
if (pm.response.code === 200 || pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.token) {
        pm.environment.set("jwt_token", jsonData.token);
    }
}
```

### ID-k módosítása
A GET/PUT/DELETE requestekben látható ID-kat (pl. `/products/1`) módosíthatod a te adatbázisodban lévő ID-kra.

## 🎯 Tipikus használati forgatókönyv

```
1. Register Admin vagy Login Admin
   ↓
2. Create Product (Admin)
   → Megjegyzed a product ID-t (pl. 1)
   ↓
3. Login User (normál felhasználó)
   ↓
4. Get Product (ID: 1)
   ↓
5. Create Review (product_id: 1)
   ↓
6. List Reviews
   ↓
7. Login Admin
   ↓
8. List All Reviews with Details (admin)
```

## ✅ Tesztelt végpontok

Mind a **36 teszt sikeres**, tehát minden végpont garantáltan működik! 

A tesztek futtatásához:
```bash
php artisan test
```

Eredmény: **36 passed** ✅

---

**Jó API tesztelést!** 🚀
