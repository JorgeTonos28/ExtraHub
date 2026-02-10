using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Data;

public class AppDbContext : DbContext
{
    public AppDbContext(DbContextOptions<AppDbContext> options) : base(options) { }
    public DbSet<Ping> Pings => Set<Ping>();
}

public class Ping
{
    public int Id { get; set; }
    public string Message { get; set; } = "";
}