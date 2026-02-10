using ExtraHub.Api.Models;
using Microsoft.AspNetCore.Identity;
using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Data;

public static class SeedData
{
    public static async Task InitializeAsync(AppDbContext db, ILogger logger)
    {
        var roleNames = new[]
        {
            AppRoles.AdministradorGerencia,
            AppRoles.EncargadoDepartamento,
            AppRoles.AyudanteDepartamento,
            AppRoles.Solicitante,
            AppRoles.AdministradorGeneral
        };

        foreach (var roleName in roleNames)
        {
            if (!await db.Roles.AnyAsync(r => r.Name == roleName))
            {
                db.Roles.Add(new Role { Name = roleName });
            }
        }

        var modules = new[]
        {
            new AppModule { Name = "Horas Extras", Route = "/apps/horas-extras" },
            new AppModule { Name = "Refrigerios", Route = "/apps/refrigerios" },
            new AppModule { Name = "Combustible", Route = "/apps/combustible" }
        };

        foreach (var module in modules)
        {
            var existing = await db.AppModules.FirstOrDefaultAsync(a => a.Name == module.Name);
            if (existing is null)
            {
                db.AppModules.Add(module);
            }
            else if (!string.Equals(existing.Route, module.Route, StringComparison.OrdinalIgnoreCase))
            {
                existing.Route = module.Route;
            }
        }

        if (!await db.Managements.AnyAsync(m => m.Name == "Gerencia General"))
        {
            db.Managements.Add(new Management { Name = "Gerencia General", IsActive = true });
        }

        await db.SaveChangesAsync();

        var gerenciaGeneral = await db.Managements.FirstAsync(m => m.Name == "Gerencia General");

        if (!await db.Departments.AnyAsync(d => d.Name == "Tecnología"))
        {
            db.Departments.Add(new Department
            {
                Name = "Tecnología",
                ManagementId = gerenciaGeneral.Id,
                Type = "Departamento",
                IsActive = true
            });
            await db.SaveChangesAsync();
        }

        if (!await db.HubUsers.AnyAsync(u => u.EmployeeCode == "ADMIN001"))
        {
            var adminRole = await db.Roles.FirstAsync(r => r.Name == AppRoles.AdministradorGeneral);
            var defaultDepartment = await db.Departments.FirstAsync(d => d.Name == "Tecnología");

            var hasher = new PasswordHasher<HubUser>();
            var admin = new HubUser
            {
                EmployeeCode = "ADMIN001",
                FullName = "Administrador General de Prueba",
                Position = "Administrador General",
                Email = "admin@extrahub.local",
                CorporateLevel = 1,
                DepartmentId = defaultDepartment.Id,
                RoleId = adminRole.Id,
                Cedula = "000-0000000-0",
                Salary = 0,
                SignaturePreference = "Administrador General",
                IsActive = true
            };

            admin.PasswordHash = hasher.HashPassword(admin, "Admin123!Secure");
            db.HubUsers.Add(admin);
            await db.SaveChangesAsync();

            var appIds = await db.AppModules.Select(a => a.Id).ToListAsync();
            db.UserAppAccesses.AddRange(appIds.Select(id => new UserAppAccess { HubUserId = admin.Id, AppModuleId = id }));
            db.UserSchedules.Add(new UserSchedule
            {
                HubUserId = admin.Id,
                ScheduleType = "fijo",
                MondayIn = new TimeOnly(8, 0),
                TuesdayIn = new TimeOnly(8, 0),
                WednesdayIn = new TimeOnly(8, 0),
                ThursdayIn = new TimeOnly(8, 0),
                FridayIn = new TimeOnly(8, 0)
            });

            await db.SaveChangesAsync();
            logger.LogInformation("Usuario de prueba ADMIN001 seed creado.");
        }
    }
}
