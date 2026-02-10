using ExtraHub.Api.Models;
using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Data;

public class AppDbContext(DbContextOptions<AppDbContext> options) : DbContext(options)
{
    public DbSet<Role> Roles => Set<Role>();
    public DbSet<AppModule> AppModules => Set<AppModule>();
    public DbSet<Management> Managements => Set<Management>();
    public DbSet<Department> Departments => Set<Department>();
    public DbSet<HubUser> HubUsers => Set<HubUser>();
    public DbSet<UserAppAccess> UserAppAccesses => Set<UserAppAccess>();
    public DbSet<UserSchedule> UserSchedules => Set<UserSchedule>();
    public DbSet<PayrollEntry> PayrollEntries => Set<PayrollEntry>();
    public DbSet<PunchRecord> PunchRecords => Set<PunchRecord>();

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        modelBuilder.Entity<HubUser>().HasIndex(u => u.EmployeeCode).IsUnique();
        modelBuilder.Entity<HubUser>().HasIndex(u => u.Email).IsUnique();
        modelBuilder.Entity<UserAppAccess>().HasIndex(x => new { x.HubUserId, x.AppModuleId }).IsUnique();
        modelBuilder.Entity<PayrollEntry>().Property(x => x.MonthlySalary).HasPrecision(18, 2);
        modelBuilder.Entity<PayrollEntry>().Property(x => x.VehicleCompensation).HasPrecision(18, 2);
    }
}
