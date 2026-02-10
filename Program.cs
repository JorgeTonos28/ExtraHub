using System.Security.Claims;
using ExtraHub.Api.Data;
using ExtraHub.Api.Services;
using ExtraHub.Api.Models;
using Microsoft.AspNetCore.Authentication.Cookies;
using Microsoft.AspNetCore.Diagnostics;
using Microsoft.EntityFrameworkCore;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddControllersWithViews();
builder.Services.AddDbContext<AppDbContext>(opt =>
    opt.UseSqlServer(builder.Configuration.GetConnectionString("DefaultConnection")));
builder.Services.AddScoped<AuthService>();
builder.Services.AddScoped<UserRulesService>();
builder.Services.AddHttpContextAccessor();

builder.Services
    .AddAuthentication(CookieAuthenticationDefaults.AuthenticationScheme)
    .AddCookie(options =>
    {
        options.LoginPath = "/auth/login";
        options.AccessDeniedPath = "/auth/forbidden";
        options.SlidingExpiration = true;
        options.Cookie.HttpOnly = true;
        options.Cookie.SameSite = SameSiteMode.Strict;
        options.Cookie.SecurePolicy = CookieSecurePolicy.SameAsRequest;
    });

builder.Services.AddAuthorization(options =>
{
    options.AddPolicy("AdminGeneralOnly", policy =>
        policy.RequireClaim(ClaimTypes.Role, AppRoles.AdministradorGeneral));
});

builder.Logging.ClearProviders();
builder.Logging.AddConsole();

var app = builder.Build();

using (var scope = app.Services.CreateScope())
{
    var db = scope.ServiceProvider.GetRequiredService<AppDbContext>();
    var logger = scope.ServiceProvider.GetRequiredService<ILoggerFactory>().CreateLogger("StartupMigration");
    try
    {
        await db.Database.MigrateAsync();
        await SeedData.InitializeAsync(db, logger);
        logger.LogInformation("Migraciones y seed ejecutados correctamente.");
    }
    catch (Exception ex)
    {
        logger.LogCritical(ex, "Fallo durante AutoMigrate/SeedData al iniciar la aplicación.");
        throw;
    }
}

if (!app.Environment.IsDevelopment())
{
    app.UseExceptionHandler("/Home/Error");
}
else
{
    app.UseDeveloperExceptionPage();
}

app.UseExceptionHandler(errorApp =>
{
    errorApp.Run(async context =>
    {
        var logger = context.RequestServices.GetRequiredService<ILoggerFactory>().CreateLogger("GlobalException");
        var errorFeature = context.Features.Get<IExceptionHandlerFeature>();
        if (errorFeature?.Error is not null)
        {
            logger.LogError(errorFeature.Error, "Excepción no controlada en ruta {Path}", context.Request.Path);
        }

        context.Response.Redirect("/Home/Error?showAlert=true");
        await Task.CompletedTask;
    });
});

app.UseStaticFiles();
app.UseRouting();
app.UseAuthentication();
app.UseAuthorization();

app.MapControllerRoute(
    name: "default",
    pattern: "{controller=Home}/{action=Index}/{id?}");

app.Run();
