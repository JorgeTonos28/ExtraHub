using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace ExtraHub.Api.Controllers;

[Authorize]
[Route("apps")]
public class AppModulesController : Controller
{
    [HttpGet("horas-extras")]
    public IActionResult HorasExtras() => View("ComingSoon", "Horas Extras");

    [HttpGet("refrigerios")]
    public IActionResult Refrigerios() => View("ComingSoon", "Refrigerios");

    [HttpGet("combustible")]
    public IActionResult Combustible() => View("ComingSoon", "Combustible");
}
