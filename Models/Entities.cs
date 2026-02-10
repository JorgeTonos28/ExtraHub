using System.ComponentModel.DataAnnotations;

namespace ExtraHub.Api.Models;

public static class AppRoles
{
    public const string AdministradorGerencia = "Administrador de Gerencia";
    public const string EncargadoDepartamento = "Encargado de Departamento";
    public const string AyudanteDepartamento = "Ayudante";
    public const string Solicitante = "Solicitante";
    public const string AdministradorGeneral = "Administrador General";
}

public class Role
{
    public int Id { get; set; }
    [MaxLength(100)] public string Name { get; set; } = string.Empty;
}

public class AppModule
{
    public int Id { get; set; }
    [MaxLength(100)] public string Name { get; set; } = string.Empty;
    [MaxLength(200)] public string Route { get; set; } = string.Empty;
}

public class Management
{
    public int Id { get; set; }
    [MaxLength(120)] public string Name { get; set; } = string.Empty;
    public bool IsActive { get; set; } = true;
}

public class Department
{
    public int Id { get; set; }
    [MaxLength(120)] public string Name { get; set; } = string.Empty;
    [MaxLength(40)] public string Type { get; set; } = "Departamento";
    public int ManagementId { get; set; }
    public Management? Management { get; set; }
    public bool IsActive { get; set; } = true;
}

public class HubUser
{
    public int Id { get; set; }
    [MaxLength(20)] public string EmployeeCode { get; set; } = string.Empty;
    [MaxLength(200)] public string FullName { get; set; } = string.Empty;
    [MaxLength(200)] public string Position { get; set; } = string.Empty;
    [EmailAddress, MaxLength(200)] public string Email { get; set; } = string.Empty;
    [MaxLength(300)] public string PasswordHash { get; set; } = string.Empty;
    public int CorporateLevel { get; set; }
    [MaxLength(80)] public string SignaturePreference { get; set; } = "digital";
    public bool IsActive { get; set; } = true;
    public int DepartmentId { get; set; }
    public Department? Department { get; set; }
    public int RoleId { get; set; }
    public Role? Role { get; set; }
    public ICollection<UserAppAccess> AppAccesses { get; set; } = new List<UserAppAccess>();
}

public class UserAppAccess
{
    public int Id { get; set; }
    public int HubUserId { get; set; }
    public HubUser? HubUser { get; set; }
    public int AppModuleId { get; set; }
    public AppModule? AppModule { get; set; }
}

public class UserSchedule
{
    public int Id { get; set; }
    public int HubUserId { get; set; }
    public HubUser? HubUser { get; set; }
    [MaxLength(20)] public string ScheduleType { get; set; } = "fijo";
    public TimeOnly? MondayIn { get; set; }
    public TimeOnly? TuesdayIn { get; set; }
    public TimeOnly? WednesdayIn { get; set; }
    public TimeOnly? ThursdayIn { get; set; }
    public TimeOnly? FridayIn { get; set; }
    public TimeOnly? SaturdayIn { get; set; }
    public TimeOnly? SundayIn { get; set; }
}

public class PayrollEntry
{
    public int Id { get; set; }
    [MaxLength(20)] public string EmployeeCode { get; set; } = string.Empty;
    [MaxLength(20)] public string Cedula { get; set; } = string.Empty;
    [MaxLength(200)] public string FullName { get; set; } = string.Empty;
    [MaxLength(200)] public string Position { get; set; } = string.Empty;
    public DateTime UpdatedAtUtc { get; set; } = DateTime.UtcNow;
}

public class PunchRecord
{
    public int Id { get; set; }
    [MaxLength(20)] public string EmployeeCode { get; set; } = string.Empty;
    public DateTime PunchTimeLocal { get; set; }
    [MaxLength(30)] public string Source { get; set; } = "GoogleSheets";
    [MaxLength(100)] public string? Device { get; set; }
}
