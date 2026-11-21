# Modo demo — Wolftek Synchro

Esta rama (`demo`) agrega un **modo demo** para publicar el sistema con datos de ejemplo
que se reinician solos. En producción no cambia nada: todo depende de `DEMO_MODE`.

## Qué hace

- Muestra un aviso flotante con el usuario y la contraseña de prueba y un botón **Rellenar login**.
- Protege al usuario demo: no se le puede cambiar el correo/usuario ni la contraseña, ni borrarlo.
- Los correos se mandan al log en lugar de enviarse.
- `php artisan demo:reset` borra la base de datos y carga los datos de ejemplo
  (`database/seeders/DemoModeSeeder.php`). Solo funciona con `DEMO_MODE=true`.

## Variables de entorno

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://demo-synchro.wolftek.com.mx

DEMO_MODE=true
DEMO_LOGIN=demo@wolftek.com.mx
DEMO_PASSWORD=demo1234
DEMO_LOGIN_URL=/admin/login
DEMO_RESET_MINUTES=60

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_la_base
DB_USERNAME=usuario
DB_PASSWORD=contraseña
MAIL_MAILER=log
```

## Tarea programada (cada hora)

```
0 * * * * php /ruta/al/proyecto/artisan demo:reset
```

## Archivos de esta rama

- `config/demo.php`
- `app/Providers/DemoServiceProvider.php` (registrado en `bootstrap/providers.php`)
- `app/Http/Middleware/DemoBanner.php`
- `app/Console/Commands/DemoReset.php`
- `database/seeders/DemoModeSeeder.php`
