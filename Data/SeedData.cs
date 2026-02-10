using ExtraHub.Api.Models;
using Microsoft.AspNetCore.Identity;
using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Data;

public static class SeedData
{
    public static async Task InitializeAsync(AppDbContext db, ILogger logger)
    {
        if (!await db.Roles.AnyAsync())
        {
            db.Roles.AddRange([
                new Role { Id = 1, Name = AppRoles.AdministradorGerencia },
                new Role { Id = 2, Name = AppRoles.EncargadoDepartamento },
                new Role { Id = 3, Name = AppRoles.AyudanteDepartamento },
                new Role { Id = 4, Name = AppRoles.Solicitante },
                new Role { Id = 5, Name = AppRoles.AdministradorGeneral }
            ]);
        }

        if (!await db.AppModules.AnyAsync())
        {
            db.AppModules.AddRange([
                new AppModule { Id = 1, Name = "Horas Extras", Route = "#" },
                new AppModule { Id = 2, Name = "Refrigerios", Route = "#" },
                new AppModule { Id = 3, Name = "Combustible", Route = "#" }
            ]);
        }

        if (!await db.Managements.AnyAsync())
        {
            db.Managements.Add(new Management { Id = 1, Name = "Gerencia General" });
        }

        if (!await db.Departments.AnyAsync())
        {
            db.Departments.Add(new Department { Id = 1, Name = "Tecnología", ManagementId = 1, Type = "Departamento" });
        }

        await db.SaveChangesAsync();

        if (!await db.HubUsers.AnyAsync(u => u.EmployeeCode == "ADMIN001"))
        {
            var hasher = new PasswordHasher<HubUser>();
            var admin = new HubUser
            {
                EmployeeCode = "ADMIN001",
                FullName = "Administrador General de Prueba",
                Position = "Administrador General",
                Email = "admin@extrahub.local",
                CorporateLevel = 1,
                DepartmentId = 1,
                RoleId = 5,
                SignaturePreference = "digital"
            };
            admin.PasswordHash = hasher.HashPassword(admin, "Admin123!Secure");
            db.HubUsers.Add(admin);
            await db.SaveChangesAsync();

            var appIds = await db.AppModules.Select(a => a.Id).ToListAsync();
            db.UserAppAccesses.AddRange(appIds.Select(id => new UserAppAccess { HubUserId = admin.Id, AppModuleId = id }));
            db.UserSchedules.Add(new UserSchedule { HubUserId = admin.Id, ScheduleType = "fijo", MondayIn = new TimeOnly(8, 0), TuesdayIn = new TimeOnly(8, 0), WednesdayIn = new TimeOnly(8, 0), ThursdayIn = new TimeOnly(8, 0), FridayIn = new TimeOnly(8, 0) });
            await db.SaveChangesAsync();
            logger.LogInformation("Usuario de prueba ADMIN001 seed creado.");
        }
    }
}
