using ExtraHub.Api.Data;
using ExtraHub.Api.Models;
using ExtraHub.Api.Services;
using ExtraHub.Api.ViewModels;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Identity;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Controllers;

public record UserUpsertRequest(
    string EmployeeCode,
    string FullName,
    string Cedula,
    string Position,
    int ManagementId,
    int DepartmentId,
    decimal Salary,
    int CorporateLevel,
    string Email,
    string? Password,
    int RoleId,
    string? SignaturePreference,
    bool IsRotative,
    TimeOnly? MondayIn,
    TimeOnly? TuesdayIn,
    TimeOnly? WednesdayIn,
    TimeOnly? ThursdayIn,
    TimeOnly? FridayIn,
    TimeOnly? SaturdayIn,
    TimeOnly? SundayIn,
    int[] Apps);

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
        ViewBag.Managements = await db.Managements.Where(m => m.IsActive).ToListAsync();
        ViewBag.Departments = await db.Departments.Where(d => d.IsActive).ToListAsync();
        ViewBag.Apps = await db.AppModules.ToListAsync();
        var levels = await db.HubUsers.Select(x => x.CorporateLevel).Distinct().OrderBy(x => x).ToListAsync();
        ViewBag.Levels = levels.Count > 0 ? levels : Enumerable.Range(1, 10).ToList();

        return View(await db.HubUsers.Include(u => u.Role).Include(u => u.Department).ThenInclude(d => d!.Management).ToListAsync());
    }

    [HttpGet("usuarios/payroll-suggest")]
    public async Task<IActionResult> PayrollSuggest(string q)
    {
        q = (q ?? string.Empty).Trim();
        if (q.Length < 2) return Json(Array.Empty<object>());

        var items = await db.PayrollEntries
            .Where(x => x.EmployeeCode.Contains(q) || x.FullName.Contains(q) || x.Cedula.Contains(q))
            .OrderBy(x => x.FullName)
            .Take(20)
            .Select(x => new { x.EmployeeCode, x.FullName, x.Cedula })
            .ToListAsync();

        return Json(items);
    }

    [HttpGet("usuarios/payroll/{code}")]
    public async Task<IActionResult> PayrollByCode(string code)
    {
        var payroll = await db.PayrollEntries.FirstOrDefaultAsync(x => x.EmployeeCode == code);
        if (payroll is null) return NotFound(new { error = "Registro de nómina no encontrado." });

        var existing = await db.HubUsers.Include(u => u.AppAccesses).Include(u => u.Role)
            .Include(u => u.Department).ThenInclude(d => d!.Management)
            .FirstOrDefaultAsync(u => u.EmployeeCode == code);
        var schedule = existing is null ? null : await db.UserSchedules.FirstOrDefaultAsync(s => s.HubUserId == existing.Id);

        return Json(new
        {
            payroll.EmployeeCode,
            payroll.FullName,
            payroll.Cedula,
            payroll.Position,
            payroll.Department,
            payroll.MonthlySalary,
            existingUser = existing is not null,
            existingData = existing is null ? null : new
            {
                existing.Email,
                existing.CorporateLevel,
                existing.RoleId,
                existing.SignaturePreference,
                existing.DepartmentId,
                managementId = existing.Department!.ManagementId,
                apps = existing.AppAccesses.Select(a => a.AppModuleId).ToArray(),
                isRotative = schedule?.ScheduleType == "rotativo",
                mondayIn = schedule?.MondayIn,
                tuesdayIn = schedule?.TuesdayIn,
                wednesdayIn = schedule?.WednesdayIn,
                thursdayIn = schedule?.ThursdayIn,
                fridayIn = schedule?.FridayIn,
                saturdayIn = schedule?.SaturdayIn,
                sundayIn = schedule?.SundayIn
            }
        });
    }

    [HttpPost("usuarios/upsert")]
    [ValidateAntiForgeryToken]
    public async Task<IActionResult> UsersUpsert([FromForm] UserUpsertRequest req)
    {
        if (string.IsNullOrWhiteSpace(req.EmployeeCode) || string.IsNullOrWhiteSpace(req.Email))
        {
            TempData["Error"] = "Código y correo son requeridos.";
            return RedirectToAction(nameof(Users));
        }

        var isUpdate = await db.HubUsers.AnyAsync(u => u.EmployeeCode == req.EmployeeCode);

        var department = await db.Departments.AsNoTracking().FirstOrDefaultAsync(d => d.Id == req.DepartmentId);
        if (department is null || department.ManagementId != req.ManagementId)
        {
            TempData["Error"] = "La gerencia y departamento seleccionados no son válidos.";
            return RedirectToAction(nameof(Users));
        }

        var user = await db.HubUsers.FirstOrDefaultAsync(u => u.EmployeeCode == req.EmployeeCode) ?? new HubUser();
        user.EmployeeCode = req.EmployeeCode.Trim();
        user.FullName = req.FullName.Trim();
        user.Cedula = req.Cedula.Trim();
        user.Position = req.Position.Trim();
        user.DepartmentId = req.DepartmentId;
        user.RoleId = req.RoleId;
        user.CorporateLevel = req.CorporateLevel;
        user.Email = req.Email.Trim();
        user.Salary = req.Salary;
        user.SignaturePreference = req.SignaturePreference?.Trim() ?? string.Empty;
        user.IsActive = true;

        var businessError = await rules.ValidateBusinessRulesAsync(user, req.Apps, isUpdate);
        if (businessError is not null)
        {
            TempData["Error"] = businessError;
            return RedirectToAction(nameof(Users));
        }

        var scheduleEntries = new[] { req.MondayIn, req.TuesdayIn, req.WednesdayIn, req.ThursdayIn, req.FridayIn, req.SaturdayIn, req.SundayIn };
        var scheduleError = UserRulesService.ValidateSchedule(scheduleEntries);
        if (scheduleError is not null)
        {
            TempData["Error"] = scheduleError;
            return RedirectToAction(nameof(Users));
        }

        if (!isUpdate && string.IsNullOrWhiteSpace(req.Password))
        {
            TempData["Error"] = "La contraseña es obligatoria al crear usuario.";
            return RedirectToAction(nameof(Users));
        }

        var hasher = new PasswordHasher<HubUser>();

        await using var tx = await db.Database.BeginTransactionAsync();

        if (!isUpdate)
        {
            user.PasswordHash = hasher.HashPassword(user, req.Password!);
            db.HubUsers.Add(user);
            await db.SaveChangesAsync();
        }
        else
        {
            if (!string.IsNullOrWhiteSpace(req.Password))
            {
                user.PasswordHash = hasher.HashPassword(user, req.Password);
            }
            db.HubUsers.Update(user);
            await db.SaveChangesAsync();

            var previousApps = db.UserAppAccesses.Where(a => a.HubUserId == user.Id);
            db.UserAppAccesses.RemoveRange(previousApps);
        }

        db.UserAppAccesses.AddRange(req.Apps.Distinct().Select(id => new UserAppAccess { HubUserId = user.Id, AppModuleId = id }));

        var schedule = await db.UserSchedules.FirstOrDefaultAsync(s => s.HubUserId == user.Id) ?? new UserSchedule { HubUserId = user.Id };
        schedule.ScheduleType = req.IsRotative ? "rotativo" : "fijo";
        schedule.MondayIn = req.MondayIn;
        schedule.TuesdayIn = req.TuesdayIn;
        schedule.WednesdayIn = req.WednesdayIn;
        schedule.ThursdayIn = req.ThursdayIn;
        schedule.FridayIn = req.FridayIn;
        schedule.SaturdayIn = req.SaturdayIn;
        schedule.SundayIn = req.SundayIn;

        if (schedule.Id == 0) db.UserSchedules.Add(schedule); else db.UserSchedules.Update(schedule);

        await db.SaveChangesAsync();
        await tx.CommitAsync();

        TempData["Success"] = isUpdate ? "Usuario modificado correctamente." : "Usuario creado correctamente.";
        return RedirectToAction(nameof(Users));
    }

    [HttpGet("nomina")]
    public async Task<IActionResult> Payroll(string? q = null)
    {
        var query = db.PayrollEntries.AsQueryable();
        if (!string.IsNullOrWhiteSpace(q))
        {
            q = q.Trim();
            query = query.Where(x =>
                x.EmployeeCode.Contains(q) ||
                x.FullName.Contains(q) ||
                x.Cedula.Contains(q) ||
                x.Department.Contains(q) ||
                x.Position.Contains(q) ||
                x.MonthlySalary.ToString().Contains(q) ||
                x.VehicleCompensation.ToString().Contains(q));
        }

        ViewBag.Query = q;
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
