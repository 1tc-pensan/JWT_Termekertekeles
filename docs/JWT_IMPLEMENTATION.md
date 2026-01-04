# JWT Autentikáció Implementáció

## 📝 Áttekintés

Ez a dokumentum részletezi a Laravel Sanctum-ról JWT (JSON Web Token) autentikációra való áttérés lépéseit a Termék Értékelések API projektben.

---

## 🔧 Telepítés és Konfiguráció

### 1. JWT Package Telepítése

```bash
composer require php-open-source-saver/jwt-auth
```

**Package verzió:** `php-open-source-saver/jwt-auth` v2.8.3

### 2. JWT Konfiguráció Publikálása

```bash
php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"
```

Ez létrehozza a `config/jwt.php` fájlt.

### 3. JWT Secret Kulcs Generálása

```bash
php artisan jwt:secret
```

Ez hozzáadja a `JWT_SECRET` kulcsot a `.env` fájlhoz.

### 4. Sanctum Eltávolítása

```bash
composer remove laravel/sanctum
rm config/sanctum.php
```

---

## 🛠️ Kód Módosítások

### 1. User Model Frissítése

**Fájl:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    // ... existing code ...

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'is_admin' => (bool) $this->is_admin,
        ];
    }
}
```

**Változások:**
- ✅ `JWTSubject` interface implementálva
- ✅ `getJWTIdentifier()` metódus - user ID visszaadása
- ✅ `getJWTCustomClaims()` metódus - admin státusz hozzáadása a token-hez

### 2. Auth Konfiguráció Frissítése

**Fájl:** `config/auth.php`

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => env('AUTH_API_DRIVER', 'jwt'),  // Changed from 'sanctum'
        'provider' => 'users',
        'hash' => false,
    ],
],
```

**Változások:**
- ✅ `api` guard driver: `jwt` (korábban: `sanctum`)

### 3. AuthController Frissítése

**Fájl:** `app/Http/Controllers/Api/AuthController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // JWT token generálás
        $token = auth('api')->login($user);

        return response()->json([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        // JWT autentikáció
        if (!$token = auth('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'user' => auth('api')->user(),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        // JWT token invalidálása
        auth('api')->logout();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
```

**Változások:**
- ✅ Sanctum `createToken()` helyett JWT `auth('api')->login()`
- ✅ Sanctum `currentAccessToken()->delete()` helyett JWT `auth('api')->logout()`
- ✅ JWT `attempt()` használata credential ellenőrzéshez

---

## 🔐 JWT Token Struktúra

### Token Formátum

```
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9sb2dpbiIsImlhdCI6MTY0NTI0MzIwMCwiZXhwIjoxNjQ1MjQ2ODAwLCJuYmYiOjE2NDUyNDMyMDAsImp0aSI6IlJXZFFZdVZyMnVLdTdaeEciLCJzdWIiOiIxIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsImlzX2FkbWluIjpmYWxzZX0.hashed_signature_here
```

### Token Részek

1. **Header** (Base64 encoded):
```json
{
  "typ": "JWT",
  "alg": "HS256"
}
```

2. **Payload** (Base64 encoded):
```json
{
  "iss": "http://localhost/api/login",
  "iat": 1645243200,
  "exp": 1645246800,
  "nbf": 1645243200,
  "jti": "RWdQYuVr2uKu7ZxG",
  "sub": "1",
  "prv": "23bd5c8949f600adb39e701c400872db7a5976f7",
  "is_admin": false
}
```

3. **Signature** (HMAC SHA-256):
```
HMACSHA256(
  base64UrlEncode(header) + "." +
  base64UrlEncode(payload),
  secret
)
```

### JWT Claims Magyarázat

- `iss` (Issuer): Token kibocsátója (API URL)
- `iat` (Issued At): Kibocsátás időpontja (Unix timestamp)
- `exp` (Expiration): Lejárat időpontja (Unix timestamp)
- `nbf` (Not Before): Token érvényességének kezdete
- `jti` (JWT ID): Token egyedi azonosítója
- `sub` (Subject): User ID
- `prv`: Provider hash (Laravel belső használat)
- `is_admin`: Custom claim - admin jogosultság

---

## 📡 API Használat

### Headers

Minden védett végpontnál:

```
Authorization: Bearer {your_jwt_token}
Content-Type: application/json
```

### Példa Kérések

#### 1. Regisztráció (JWT token megszerzése)

```bash
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Válasz:**
```json
{
  "message": "User created successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer"
}
```

#### 2. Bejelentkezés

```bash
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Válasz:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "is_admin": false
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer"
}
```

#### 3. Védett Végpont Hívása

```bash
GET /api/products
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

## 🚀 Előnyök

### JWT vs Sanctum

| Tulajdonság | JWT | Sanctum |
|------------|-----|---------|
| **Token tárolás** | Nincs szerver oldali tárolás | Personal access tokens táblában |
| **Skálázhatóság** | Horizontálisan skálázható | Adatbázis függő |
| **Teljesítmény** | Gyorsabb (nincs DB lekérdezés) | Lassabb (DB lekérdezés minden kérésnél) |
| **Token méret** | Nagyobb (tartalmazza az adatokat) | Kisebb (csak ID) |
| **Státusz** | Stateless | Stateful |
| **Token invalidálás** | Nehezebb (blacklist szükséges) | Egyszerű (DB delete) |
| **Offline ellenőrzés** | Lehetséges | Nem lehetséges |

### JWT Előnyei

✅ **Stateless**: Nincs szükség szerver oldali session tárolásra
✅ **Horizontálisan skálázható**: Load balancer-rel könnyű használni
✅ **Teljesítmény**: Nincs DB lekérdezés minden API kérésnél
✅ **Cross-domain**: Különböző domain-ek között is működik
✅ **Mobile-friendly**: Natív alkalmazásokban egyszerűen használható
✅ **Self-contained**: Token tartalmazza a szükséges információkat
✅ **Standard**: RFC 7519 szabvány

---

## ⚙️ Konfiguráció

### JWT Beállítások

**Fájl:** `config/jwt.php`

```php
'secret' => env('JWT_SECRET'),
'ttl' => env('JWT_TTL', 60), // Token élettartam percben
'algo' => env('JWT_ALGO', 'HS256'), // Algoritmus
'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // Refresh token TTL (2 hét)
```

### .env Konfiguráció

```env
JWT_SECRET=your_generated_secret_key
JWT_TTL=60
JWT_ALGO=HS256
JWT_REFRESH_TTL=20160
```

---

## 🧪 Tesztelés

### Postman/Insomnia

1. **Login kérés:**
   - Method: POST
   - URL: `http://localhost/api/login`
   - Body: JSON
   ```json
   {
     "email": "admin@example.com",
     "password": "admin123"
   }
   ```

2. **Token mentése:**
   - Copy token from response

3. **Védett végpont hívás:**
   - Method: GET
   - URL: `http://localhost/api/products`
   - Headers:
     - `Authorization: Bearer {token}`

### CURL Példák

```bash
# Login
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}'

# Védett végpont
curl -X GET http://localhost/api/products \
  -H "Authorization: Bearer {your_token}"
```

---

## 🔒 Biztonság

### Best Practices

1. **HTTPS használata**: Mindig használj HTTPS-t production környezetben
2. **Token tárolás**: Kliens oldalon localStorage vagy sessionStorage
3. **Token lejárat**: Ne használj túl hosszú TTL-t (ajánlott: 60 perc)
4. **Refresh token**: Implementálj refresh token mechanizmust
5. **CORS beállítás**: Korlátozd az engedélyezett origin-okat
6. **Rate limiting**: Védd az API-t túl sok kérés ellen
7. **Token blacklist**: Invalidált tokenek listázása (opcionális)

### Potenciális Kockázatok

⚠️ **XSS**: Token ellopása XSS támadással
⚠️ **Token méret**: Nagyobb mint Sanctum token
⚠️ **Token invalidálás**: Nehezebb végrehajtani
⚠️ **Titkos kulcs**: Kompromittálódás esetén minden token érvénytelen

---

## 📚 További Források

- [JWT.io](https://jwt.io/) - JWT debugger és dokumentáció
- [php-open-source-saver/jwt-auth dokumentáció](https://github.com/PHP-Open-Source-Saver/jwt-auth)
- [RFC 7519 - JWT szabvány](https://tools.ietf.org/html/rfc7519)
- [Laravel Authentication dokumentáció](https://laravel.com/docs/authentication)

---

## 📝 Migráció Checklist

- [x] JWT package telepítése
- [x] JWT konfiguráció publikálása
- [x] JWT secret generálása
- [x] Sanctum eltávolítása
- [x] User model JWTSubject implementálása
- [x] Auth konfiguráció frissítése (jwt driver)
- [x] AuthController átírása JWT használatra
- [x] API dokumentáció frissítése
- [x] README frissítése
- [x] .env.example frissítése
- [x] CHANGELOG frissítése
- [x] Tesztelés Postman-nel

---

## ✅ Összefoglalás

A JWT autentikáció sikeres implementálása után:

1. ✅ A rendszer stateless autentikációt használ
2. ✅ Token-ek 60 percig érvényesek
3. ✅ Admin jogosultság a token payload-ban tárolódik
4. ✅ Horizontálisan skálázható architektúra
5. ✅ Teljesítmény javulás (nincs DB lekérdezés tokenekért)
6. ✅ Cross-platform támogatás (web, mobile)
7. ✅ Standard, széles körben használt technológia

**A projekt most production-ready JWT autentikációval rendelkezik! 🎉**
