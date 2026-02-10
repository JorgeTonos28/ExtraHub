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
public class AdminController(AppDbContext db, UserRulesService rules, PayrollImportService payrollImportService) : Controller
{
    [HttpGet("")]
    public IActionResult Index()
    {
        var demoInsights = new AdminDashboardViewModel
        {
            TotalUsers = 126,
            ActiveDepartments = 24,
            TotalPayroll = 842,
            TotalPunches = 17623
        };
        return View("Dashboard", demoInsights);
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
    public async Task<IActionResult> Payroll(string? search = null, string field = "nombre")
    {
        var query = db.PayrollEntries.AsQueryable();
        if (!string.IsNullOrWhiteSpace(search))
        {
            search = search.Trim();
            query = field.ToLowerInvariant() switch
            {
                "codigo" => query.Where(x => x.EmployeeCode.Contains(search)),
                "cedula" => query.Where(x => x.Cedula.Contains(search)),
                _ => query.Where(x => x.FullName.Contains(search))
            };
        }

        ViewBag.Search = search;
        ViewBag.Field = field;
        ViewBag.TotalSalary = await query.SumAsync(x => x.MonthlySalary);
        return View(await query.OrderBy(x => x.FullName).Take(500).ToListAsync());
    }

    [HttpPost("nomina/actualizar")]
    [ValidateAntiForgeryToken]
    public async Task<IActionResult> PayrollUpdate(IFormFile payrollFile, CancellationToken ct)
    {
        if (payrollFile is null)
        {
            TempData["Error"] = "Debe seleccionar un archivo Excel para actualizar la nómina.";
            return RedirectToAction(nameof(Payroll));
        }

        var ext = Path.GetExtension(payrollFile.FileName);
        if (!string.Equals(ext, ".xlsx", StringComparison.OrdinalIgnoreCase))
        {
            TempData["Error"] = "Formato no permitido. Debe subir un archivo .xlsx.";
            return RedirectToAction(nameof(Payroll));
        }

        var result = await payrollImportService.ImportAsync(payrollFile, ct);
        if (!result.Success)
        {
            TempData["Error"] = result.Message;
            return RedirectToAction(nameof(Payroll));
        }

        TempData["Success"] = $"{result.Message} Registros: {result.RowsImported}. Sumatoria salario mensual: {result.TotalMonthlySalary:N2}";
        return RedirectToAction(nameof(Payroll));
    }

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
