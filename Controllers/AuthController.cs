using ExtraHub.Api.Services;
using ExtraHub.Api.ViewModels;
using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace ExtraHub.Api.Controllers;

public class AuthController(AuthService authService, ILogger<AuthController> logger) : Controller
{
    [HttpGet]
    [AllowAnonymous]
    public IActionResult Login() => View(new LoginViewModel());

    [HttpPost]
    [AllowAnonymous]
    [ValidateAntiForgeryToken]
    public async Task<IActionResult> Login(LoginViewModel model)
    {
        if (!ModelState.IsValid)
        {
            TempData["Error"] = "Debe completar usuario y contraseña.";
            return View(model);
        }

        var (ok, error) = await authService.LoginAsync(HttpContext, model.Login.Trim(), model.Password);
        if (!ok)
        {
            logger.LogWarning("Intento de login fallido para {Login}", model.Login);
            TempData["Error"] = error;
            return View(model);
        }

        return RedirectToAction("Index", "Home");
    }

    [Authorize]
    [HttpPost]
    [ValidateAntiForgeryToken]
    public async Task<IActionResult> Logout()
    {
        await HttpContext.SignOutAsync();
        return RedirectToAction(nameof(Login));
    }

    public IActionResult Forbidden()
    {
        TempData["Error"] = "No cuenta con permisos para acceder a esta sección.";
        return RedirectToAction("Index", "Home");
    }
}
