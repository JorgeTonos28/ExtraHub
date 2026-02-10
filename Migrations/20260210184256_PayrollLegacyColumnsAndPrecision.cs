using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace ExtraHub.Api.Migrations
{
    /// <inheritdoc />
    public partial class PayrollLegacyColumnsAndPrecision : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<string>(
                name: "Department",
                table: "PayrollEntries",
                type: "nvarchar(200)",
                maxLength: 200,
                nullable: false,
                defaultValue: "");

            migrationBuilder.AddColumn<decimal>(
                name: "MonthlySalary",
                table: "PayrollEntries",
                type: "decimal(18,2)",
                precision: 18,
                scale: 2,
                nullable: false,
                defaultValue: 0m);

            migrationBuilder.AddColumn<decimal>(
                name: "VehicleCompensation",
                table: "PayrollEntries",
                type: "decimal(18,2)",
                precision: 18,
                scale: 2,
                nullable: false,
                defaultValue: 0m);
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropColumn(
                name: "Department",
                table: "PayrollEntries");

            migrationBuilder.DropColumn(
                name: "MonthlySalary",
                table: "PayrollEntries");

            migrationBuilder.DropColumn(
                name: "VehicleCompensation",
                table: "PayrollEntries");
        }
    }
}
