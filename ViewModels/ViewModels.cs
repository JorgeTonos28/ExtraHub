using System.ComponentModel.DataAnnotations;
using ExtraHub.Api.Models;

namespace ExtraHub.Api.ViewModels;

public class LoginViewModel
{
    [Required] public string Login { get; set; } = string.Empty;
    [Required, DataType(DataType.Password)] public string Password { get; set; } = string.Empty;
}

public class LandingViewModel
{
    public string UserName { get; set; } = string.Empty;
    public string Role { get; set; } = string.Empty;
    public List<AppModule> Apps { get; set; } = [];
    public bool IsAdminGeneral => Role == AppRoles.AdministradorGeneral;
}

public class AdminDashboardViewModel
{
    public int TotalUsers { get; set; }
    public int ActiveDepartments { get; set; }
    public int TotalPayroll { get; set; }
    public int TotalPunches { get; set; }
}
