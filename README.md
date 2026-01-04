# Termék Értékelések API

Laravel 12 alapú REST API termékek és értékelések kezelésére JWT autentikációval.

## 🔐 Autentikáció

**JWT (JSON Web Token)** alapú hitelesítés
- Package: `php-open-source-saver/jwt-auth`
- Token élettartam: 60 perc
- Admin jogosultság támogatás a token payload-ban

## 🚀 Funkciók

- ✅ JWT alapú autentikáció
- ✅ Felhasználói regisztráció és bejelentkezés
- ✅ Termékek CRUD műveletek (Admin)
- ✅ Értékelések CRUD műveletek
- ✅ Admin panel teljes jogosultságokkal
- ✅ Role-based access control (User/Admin)
- ✅ RESTful API végpontok
- ✅ Teljes API dokumentáció

## 📋 Követelmények

- PHP >= 8.2
- Composer
- MySQL/MariaDB
- Laravel 12

## 🛠️ Telepítés

```bash
# Projekt klónozása
git clone <repository-url>
cd Termekertekelesek

# Függőségek telepítése
composer install

# .env fájl létrehozása
cp .env.example .env

# Alkalmazás kulcs generálása
php artisan key:generate

# JWT secret kulcs generálása
php artisan jwt:secret

# Adatbázis migráció és seed
php artisan migrate --seed

# Szerver indítása
php artisan serve
```

## 🔑 JWT Konfigurácio

A JWT konfiguráció a `config/jwt.php` fájlban található. A JWT secret automatikusan generálódik a `php artisan jwt:secret` paranccsal és a `.env` fájlban tárolódik:

```env
JWT_SECRET=your_generated_secret_key
```

## 📚 Dokumentáció

Részletes API dokumentáció: [API_DOKUMENTACIO.md](API_DOKUMENTACIO.md)

## 🧪 Tesztelés

```bash
php artisan test
```

## 👤 Alapértelmezett Admin Fiók

- **Email:** admin@example.com
- **Jelszó:** admin123

## 📝 API Végpontok Összefoglaló

### Publikus
- `POST /api/register` - Regisztráció
- `POST /api/login` - Bejelentkezés (JWT token megszerzése)

### Autentikált (JWT Bearer token)
- `POST /api/logout` - Kijelentkezés
- `GET /api/user` - Felhasználói profil
- `GET /api/products` - Termékek listázása
- `GET /api/reviews` - Értékelések listázása
- `POST /api/reviews` - Értékelés létrehozása

### Admin (JWT Bearer token + admin)
- `GET /api/admin/users` - Felhasználók kezelése
- `POST /api/admin/products` - Termékek kezelése
- `PUT /api/admin/reviews/{id}` - Értékelések moderálása

## 🔧 Technológiák

- Laravel 12
- JWT Auth (php-open-source-saver/jwt-auth)
- MySQL
- RESTful API
- PHP 8.2+

## 📄 Licenc

MIT License
