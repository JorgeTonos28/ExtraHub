using System.Security.Claims;
using ExtraHub.Api.Data;
using ExtraHub.Api.Models;
using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Authentication.Cookies;
using Microsoft.AspNetCore.Identity;
using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Services;

public class AuthService(AppDbContext db)
{
    public async Task<(bool ok, string error)> LoginAsync(HttpContext context, string login, string password)
    {
        var user = await db.HubUsers.Include(u => u.Role)
            .FirstOrDefaultAsync(u => u.EmployeeCode == login || u.Email == login);

        if (user is null || !user.IsActive)
        {
            return (false, "Usuario no encontrado o inactivo.");
        }

        var hasher = new PasswordHasher<HubUser>();
        var result = hasher.VerifyHashedPassword(user, user.PasswordHash, password);
        if (result == PasswordVerificationResult.Failed)
        {
            return (false, "Credenciales inválidas.");
        }

        var claims = new List<Claim>
        {
            new(ClaimTypes.NameIdentifier, user.Id.ToString()),
            new(ClaimTypes.Name, user.FullName),
            new(ClaimTypes.Role, user.Role?.Name ?? string.Empty),
            new("EmployeeCode", user.EmployeeCode)
        };

        var identity = new ClaimsIdentity(claims, CookieAuthenticationDefaults.AuthenticationScheme);
        var principal = new ClaimsPrincipal(identity);
        await context.SignInAsync(CookieAuthenticationDefaults.AuthenticationScheme, principal);

        return (true, string.Empty);
    }
}
