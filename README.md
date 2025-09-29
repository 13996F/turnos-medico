<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo" />
</p>

# Sistema de Turnos Médicos — Centro Médico del Milagro

![CI](https://github.com/13996F/turnos-medico/actions/workflows/laravel-ci.yml/badge.svg)

Aplicación Laravel para gestionar turnos médicos: registro de usuarios, agenda de profesionales, turnos, y panel de administración.

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- Extensiones PHP típicas de Laravel (pdo, openssl, mbstring, etc.)

## Puesta en marcha (local)

1. Clonar el repositorio
   ```bash
   git clone https://github.com/13996F/turnos-medico.git
   cd turnos-medico
   ```

2. Instalar dependencias
   ```bash
   composer install
   npm install
   ```

3. Variables de entorno
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   - Por defecto `.env.example` usa SQLite. Para usar MySQL, descomenta y configura `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
   - Mail (opcional): configura `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`.
   - Almacenamiento: ejecuta `php artisan storage:link` si sirves archivos públicos.

4. Migraciones y seeders (si aplica)
   ```bash
   php artisan migrate
   # php artisan db:seed   # si definiste seeders
   ```

5. Compilar assets y servir
   ```bash
   npm run dev
   php artisan serve
   ```

## Scripts útiles

- Iniciar servidor: `php artisan serve`
- Ejecutar pruebas: `php artisan test`
- Correcciones de estilo (si se agrega Pint/PHPCS): `composer lint`

## Estructura destacada

- Rutas: `routes/web.php`
- Controladores: `app/Http/Controllers/`
- Middlewares: `app/Http/Middleware/`
- Vistas Blade: `resources/views/`
- Migraciones: `database/migrations/`

## CI/CD

Se incluye un workflow de GitHub Actions para ejecutar pruebas en cada push/PR. Ver `.github/workflows/laravel-ci.yml`.

## Autenticación y roles

- Roles soportados: `Admin`, `Doctor`, `Patient`.
- Controladores clave:
  - `app/Http/Controllers/AdminAuthController.php`
  - `app/Http/Controllers/DoctorAuthController.php`
  - `app/Http/Controllers/PatientAuthController.php`
  - `app/Http/Controllers/PatientPasswordController.php`
- Ajusta rutas en `routes/web.php` para páginas de login/registro según tus necesidades.

## Datos de prueba (opcional)

Si agregas seeders, podrás crear usuarios demo. Ejemplo de comandos:
```bash
php artisan migrate:fresh --seed
```
Luego inicia sesión con las credenciales documentadas en tus seeders.

## Despliegue (resumen)

- Configura `.env` para producción (APP_ENV=production, APP_DEBUG=false, base de datos, mail, etc.).
- Ejecuta `php artisan migrate --force` en el servidor.
- Compila assets: `npm run build`.
- Opcional: `php artisan config:cache && php artisan route:cache && php artisan view:cache`.

## Solución de problemas

- Errores de permisos en `storage/` o `bootstrap/cache/`: asegúrate de que el usuario del servidor tenga permisos de escritura.
- Si usas SQLite y falla la migración en CI/local, verifica que el archivo `database/database.sqlite` exista y que la ruta en `DB_DATABASE` apunte correctamente.
- En Windows, si aparece un error de rutas simbólicas, ejecuta la terminal como administrador para `php artisan storage:link`.

## Seguridad

- Nunca subas el archivo `.env` (está ignorado por `.gitignore`).
- Revisa y personaliza `.env.example` para documentar variables necesarias sin exponer secretos.

## Contribución

Los cambios se aceptan mediante Pull Requests. Antes de abrir un PR:
- Ejecuta `composer install && npm install`.
- Asegúrate de que `php artisan test` pase en local.
- Describe claramente el cambio y su motivación.

## Licencia

Este proyecto se distribuye bajo la licencia MIT. Ver `LICENSE`.
