using ClosedXML.Excel;
using ExtraHub.Api.Data;
using ExtraHub.Api.Models;
using Microsoft.EntityFrameworkCore;

namespace ExtraHub.Api.Services;

public record PayrollImportResult(bool Success, string Message, decimal TotalMonthlySalary, int RowsImported);

public class PayrollImportService(AppDbContext db, ILogger<PayrollImportService> logger)
{
    private static readonly Dictionary<string, string> ExpectedHeaders = new()
    {
        ["A3"] = "Código ",
        ["B3"] = "Nombre",
        ["C3"] = "Cédula",
        ["D3"] = "Departamento",
        ["E3"] = "Cargo o Posición",
        ["F3"] = "Salario Quincenal",
        ["G3"] = "Salario Mensual",
        ["H3"] = "COMPENSACION POR USO DE VEHICULOS"
    };

    public async Task<PayrollImportResult> ImportAsync(IFormFile file, CancellationToken ct = default)
    {
        if (file.Length == 0)
        {
            return new PayrollImportResult(false, "El archivo está vacío.", 0, 0);
        }

        try
        {
            using var stream = file.OpenReadStream();
            using var workbook = new XLWorkbook(stream);

            var parsedRows = new List<PayrollEntry>();
            decimal sum = 0;

            foreach (var ws in workbook.Worksheets)
            {
                var indicator = ws.Cell("A1").GetString().Trim();
                if (!string.Equals(indicator, "INFOTEP", StringComparison.OrdinalIgnoreCase))
                {
                    return new PayrollImportResult(false, $"La hoja '{ws.Name}' no contiene 'INFOTEP' en A1.", 0, 0);
                }

                foreach (var kvp in ExpectedHeaders)
                {
                    var actual = ws.Cell(kvp.Key).GetString();
                    if (!string.Equals(actual, kvp.Value, StringComparison.Ordinal))
                    {
                        return new PayrollImportResult(false, $"La hoja '{ws.Name}' tiene encabezado inválido en {kvp.Key}. Esperado: '{kvp.Value}'.", 0, 0);
                    }
                }

                var row = 4;
                while (true)
                {
                    var employeeCode = ws.Cell(row, 1).GetString().Trim();
                    if (string.IsNullOrWhiteSpace(employeeCode))
                    {
                        break;
                    }

                    var fullName = ws.Cell(row, 2).GetString().Trim();
                    var cedula = ws.Cell(row, 3).GetString().Trim();
                    var department = ws.Cell(row, 4).GetString().Trim();
                    var position = ws.Cell(row, 5).GetString().Trim();
                    var monthlySalary = ParseDecimal(ws.Cell(row, 7).Value.ToString());
                    var compensation = ParseDecimal(ws.Cell(row, 8).Value.ToString());

                    parsedRows.Add(new PayrollEntry
                    {
                        EmployeeCode = employeeCode,
                        FullName = fullName,
                        Cedula = cedula,
                        Department = department,
                        Position = position,
                        MonthlySalary = monthlySalary,
                        VehicleCompensation = compensation,
                        UpdatedAtUtc = DateTime.UtcNow
                    });

                    sum += monthlySalary;
                    row++;
                }
            }

            if (parsedRows.Count == 0)
            {
                return new PayrollImportResult(false, "No se encontraron filas válidas de nómina en el archivo.", 0, 0);
            }

            await using var tx = await db.Database.BeginTransactionAsync(ct);
            db.PayrollEntries.RemoveRange(db.PayrollEntries);
            await db.SaveChangesAsync(ct);
            await db.PayrollEntries.AddRangeAsync(parsedRows, ct);
            await db.SaveChangesAsync(ct);
            await tx.CommitAsync(ct);

            logger.LogInformation("Nómina importada correctamente. Filas: {Rows}, Sumatoria Salario Mensual: {Total}", parsedRows.Count, sum);
            return new PayrollImportResult(true, "Nómina actualizada correctamente.", sum, parsedRows.Count);
        }
        catch (Exception ex)
        {
            logger.LogError(ex, "Error al importar archivo de nómina.");
            return new PayrollImportResult(false, $"Error al procesar nómina: {ex.Message}", 0, 0);
        }
    }

    private static decimal ParseDecimal(string? value)
    {
        if (string.IsNullOrWhiteSpace(value)) return 0;
        value = value.Replace(",", "").Trim();
        return decimal.TryParse(value, out var result) ? result : 0;
    }
}
