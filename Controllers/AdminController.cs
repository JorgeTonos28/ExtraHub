using ExtraHub.Api.Data;
using ExtraHub.Api.Models;
using ExtraHub.Api.Services;
using ExtraHub.Api.ViewModels;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Controllers;

[Authorize(Policy = "AdminGeneralOnly")]
[Route("admin")]
public class AdminController(AppDbContext db, UserRulesService rules) : Controller
{
    [HttpGet("")]
    public async Task<IActionResult> Index()
    {
        return View("Dashboard", new AdminDashboardViewModel
        {
            TotalUsers = await db.HubUsers.CountAsync(),
            ActiveDepartments = await db.Departments.CountAsync(d => d.IsActive),
            TotalPayroll = await db.PayrollEntries.CountAsync(),
            TotalPunches = await db.PunchRecords.CountAsync()
        });
    }

    [HttpGet("usuarios")]
    public async Task<IActionResult> Users()
    {
        ViewBag.Roles = await db.Roles.ToListAsync();
        ViewBag.Departments = await db.Departments.Where(d => d.IsActive).ToListAsync();
        ViewBag.Apps = await db.AppModules.ToListAsync();
        return View(await db.HubUsers.Include(u => u.Role).Include(u => u.Department).ToListAsync());
    }

    [HttpPost("usuarios")]
    [ValidateAntiForgeryToken]
    public async Task<IActionResult> UsersCreate(HubUser user, int[] apps)
    {
        var error = await rules.ValidateBusinessRulesAsync(user);
        if (error is not null)
        {
            TempData["Error"] = error;
            return RedirectToAction(nameof(Users));
        }

        user.PasswordHash = new Microsoft.AspNetCore.Identity.PasswordHasher<HubUser>().HashPassword(user, "Temporal123!");
        db.HubUsers.Add(user);
        await db.SaveChangesAsync();

        db.UserAppAccesses.AddRange(apps.Select(id => new UserAppAccess { HubUserId = user.Id, AppModuleId = id }));
        await db.SaveChangesAsync();
        TempData["Success"] = "Usuario creado con contraseña temporal segura.";
        return RedirectToAction(nameof(Users));
    }

    [HttpGet("nomina")]
    public async Task<IActionResult> Payroll() => View(await db.PayrollEntries.OrderByDescending(x => x.UpdatedAtUtc).Take(200).ToListAsync());

    [HttpGet("departamentos")]
    public async Task<IActionResult> Departments()
    {
        ViewBag.Managements = await db.Managements.Where(x => x.IsActive).ToListAsync();
        return View(await db.Departments.Include(d => d.Management).ToListAsync());
    }

    [HttpGet("gerencias")]
    public async Task<IActionResult> Managements() => View(await db.Managements.ToListAsync());

    [HttpGet("ponchado")]
    public async Task<IActionResult> Punching() => View(await db.PunchRecords.OrderByDescending(p => p.PunchTimeLocal).Take(200).ToListAsync());
}
