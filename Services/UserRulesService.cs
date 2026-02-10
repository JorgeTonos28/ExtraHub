using ExtraHub.Api.Data;
using ExtraHub.Api.Models;
using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Services;

public class UserRulesService(AppDbContext db)
{
    private static readonly HashSet<string> CargosPermitidosHorasExtras =
    [
        "CHOFER", "CONSERJE", "MENSAJERO INTERNO", "MENSAJERO EXTERNO", "OFICIAL DE SEGURIDAD",
        "PINTOR", "PLOMERO", "CAMARERO", "JARDINERO", "LAVADOR DE VEHÍCULO",
        "AUXILIAR DE ALMACÉN", "TÉCNICO DE MANTENIMIENTO", "ANALISTA DE CCTV",
        "AUXILIAR DE SERVICIOS GENERALES", "AUXILIAR DE TRANSPORTACIÓN",
        "AUXILIAR DE EVENTOS", "ASISTENTE DE TRANSPORTACIÓN"
    ];

    public async Task<string?> ValidateBusinessRulesAsync(HubUser user, int[] apps, bool isUpdate)
    {
        if (user.DepartmentId <= 0 || user.RoleId <= 0) return "Debe seleccionar Gerencia, Departamento y Rol válidos.";

        var existingByCode = await db.HubUsers.FirstOrDefaultAsync(u => u.EmployeeCode == user.EmployeeCode);
        if (!isUpdate && existingByCode is not null) return "El código de usuario ya está registrado.";

        var duplicateMail = await db.HubUsers.FirstOrDefaultAsync(u => u.Email == user.Email && u.EmployeeCode != user.EmployeeCode);
        if (duplicateMail is not null) return "El correo electrónico ya está registrado.";

        if (user.RoleId == 5)
        {
            var count = await db.HubUsers.CountAsync(u => u.RoleId == 5 && u.EmployeeCode != user.EmployeeCode);
            if (count >= 2) return "No se pueden crear más de 2 administradores generales a nivel nacional.";
        }

        if (user.RoleId == 1)
        {
            var targetDep = await db.Departments.AsNoTracking().FirstOrDefaultAsync(d => d.Id == user.DepartmentId);
            if (targetDep is null) return "Departamento no encontrado.";
            var adminGerenciaCount = await db.HubUsers
                .Join(db.Departments, u => u.DepartmentId, d => d.Id, (u, d) => new { u, d })
                .CountAsync(x => x.u.RoleId == 1 && x.d.ManagementId == targetDep.ManagementId && x.u.EmployeeCode != user.EmployeeCode);
            if (adminGerenciaCount >= 3) return "Ya existen 3 administradores en la gerencia seleccionada.";
        }

        if (user.RoleId == 2)
        {
            var count = await db.HubUsers.CountAsync(u => u.RoleId == 2 && u.DepartmentId == user.DepartmentId && u.EmployeeCode != user.EmployeeCode);
            if (count >= 1) return "Ya existe un encargado en este departamento.";
        }

        if (user.RoleId == 3)
        {
            var count = await db.HubUsers.CountAsync(u => u.RoleId == 3 && u.DepartmentId == user.DepartmentId && u.EmployeeCode != user.EmployeeCode);
            if (count >= 3) return "Ya existen 3 ayudantes en este departamento.";
        }

        if (user.RoleId == 4 && apps.Contains(1))
        {
            var cargo = user.Position.ToUpperInvariant();
            if (!CargosPermitidosHorasExtras.Contains(cargo))
            {
                return "La creación de horas extras está permitida únicamente para personal de apoyo (5to nivel). Quite la app Horas Extras para continuar.";
            }
        }

        if (apps.Length == 0) return "Debe seleccionar al menos una app.";

        return null;
    }

    public static string? ValidateSchedule(TimeOnly?[] entries)
    {
        var freeDays = entries.Count(e => e is null);
        if (freeDays < 2) return "El horario debe contener al menos 2 días libres.";
        if (freeDays > 2) return "El horario debe contener exactamente 2 días libres.";
        return null;
    }
}
