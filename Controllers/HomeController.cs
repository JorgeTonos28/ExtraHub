using System.Security.Claims;
using ExtraHub.Api.Data;
using ExtraHub.Api.ViewModels;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Controllers;

[Authorize]
public class HomeController(AppDbContext db) : Controller
{
    public async Task<IActionResult> Index()
    {
        var userId = int.Parse(User.FindFirstValue(ClaimTypes.NameIdentifier)!);
        var role = User.FindFirstValue(ClaimTypes.Role) ?? string.Empty;

        var apps = await db.UserAppAccesses.Where(x => x.HubUserId == userId)
            .Include(x => x.AppModule)
            .Select(x => x.AppModule!)
            .ToListAsync();

        return View(new LandingViewModel
        {
            UserName = User.Identity?.Name ?? string.Empty,
            Role = role,
            Apps = apps
        });
    }

    [AllowAnonymous]
    public IActionResult Error(bool showAlert = false)
    {
        if (showAlert) TempData["Error"] = "Ha ocurrido un error inesperado. Revise logs para más detalles.";
        return View();
    }
}
