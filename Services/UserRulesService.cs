using ExtraHub.Api.Data;
using ExtraHub.Api.Models;
using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Services;

public class UserRulesService(AppDbContext db)
{
    public async Task<string?> ValidateBusinessRulesAsync(HubUser user)
    {
        if (user.RoleId == 5)
        {
            var count = await db.HubUsers.CountAsync(u => u.RoleId == 5);
            if (count >= 2) return "No se pueden crear más de 2 administradores generales a nivel nacional.";
        }

        if (user.RoleId == 2)
        {
            var count = await db.HubUsers.CountAsync(u => u.RoleId == 2 && u.DepartmentId == user.DepartmentId);
            if (count >= 1) return "Ya existe un encargado en este departamento.";
        }

        if (user.RoleId == 3)
        {
            var count = await db.HubUsers.CountAsync(u => u.RoleId == 3 && u.DepartmentId == user.DepartmentId);
            if (count >= 3) return "Ya existen 3 ayudantes en este departamento.";
        }

        return null;
    }
}
