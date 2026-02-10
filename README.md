# ExtraHub (ASP.NET Core 8)

ExtraHub es un Hub corporativo para centralizar autenticación y acceso a aplicaciones internas (por ejemplo: Horas Extras, Refrigerios y Combustible), junto con un panel administrativo para gestión de usuarios, nómina, departamentos, gerencias y sincronización de ponchado.

## Stack
- ASP.NET Core 8 MVC + Cookie Authentication.
- Entity Framework Core 8 (SQL Server).
- Bootstrap 5 + Bootstrap Icons.

## Seguridad implementada
- Contraseñas con hash seguro (`PasswordHasher`).
- Autenticación por cookie (`HttpOnly`, `SameSite=Strict`, `Secure=Always`).
- Redirección HTTPS forzada (`UseHttpsRedirection`) y HSTS en no-desarrollo.
- Autorización basada en rol (`Administrador General` para panel admin).
- Validaciones de reglas de negocio heredadas para creación de usuarios.
- Manejo centralizado de excepciones con logging en consola.
- Alertas de error y éxito en front end (`TempData` + Bootstrap alerts).

## AutoMigrate + SeedData
Al iniciar la app (`dotnet run`):
1. Se ejecutan automáticamente migraciones pendientes sobre `AppDbContext`.
2. Se ejecuta `SeedData` para cargar:
   - Roles heredados.
   - Apps base.
   - Gerencia y departamento iniciales.
   - Usuario admin de prueba.

No se requiere correr `dotnet ef database update` manualmente para entorno de ejecución normal.

## Credenciales de prueba
- Usuario: `ADMIN001`
- Contraseña: `Admin123!Secure`

> Cambiar inmediatamente en ambientes reales.

## Módulos implementados
- Login.
- Landing/index con apps habilitadas por usuario y acceso a panel admin según rol.
- Panel administrativo con:
  - Dashboard
  - Crear/Modificar Usuario
  - Nómina
  - Departamentos
  - Gerencias (nuevo)
  - Ponchado

## Lógica de nómina (resumen)
El módulo de nómina mantiene entradas con código, cédula, nombre, cargo y marca de actualización. La idea operativa es cargar/actualizar registros de empleados y conservar la última fecha de actualización para trazabilidad. Esta base permite implementar reconciliación de personal activo y validación cruzada contra usuarios del Hub.

## Lógica de ponchado y Google Sheets (seguridad de secrets/tokens)
Para robustez de seguridad:
1. **No** guardar JSON de credenciales en el repo.
2. Guardar secrets en:
   - User Secrets (desarrollo), o
   - Variables de entorno / Azure Key Vault / Secret Manager (producción).
3. Configurar (ejemplo):
   - `GoogleSheets:SpreadsheetId`
   - `GoogleSheets:CredentialsJson` (contenido JSON o ruta segura fuera del repo)
4. Implementar servicio de sincronización que:
   - Consulte Google Sheets con API oficial.
   - Registre errores detallados en log.
   - Devuelva errores controlados al front end con mensajes entendibles.

## Tipos de Departamentos
Tipos fijos en el sistema:
- Dirección
- Departamento
- División
- Sección
- Centro
- Área

## Ejecución
```bash
dotnet restore
dotnet run
```

## Migraciones EF Core
Si hay cambios de esquema adicionales en desarrollo:
```bash
dotnet ef migrations add NombreMigracion
dotnet ef database update
```

En runtime normal, `Program.cs` ya aplica migraciones pendientes automáticamente.


## Configuración HTTPS en despliegues con proxy inverso
- Mantener TLS extremo a extremo o terminar TLS en el proxy (IIS/Nginx/Azure App Service) y reenviar tráfico interno seguro.
- No deshabilitar `UseHttpsRedirection` ni `Secure` en cookies de autenticación.
- Validar certificados y cabeceras de proxy en entornos productivos.
