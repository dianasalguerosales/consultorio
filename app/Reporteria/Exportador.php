<?php

namespace App\Reporteria;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Un informe ya armado, bajado como archivo.
 *
 * Vive aparte del controlador porque son tres formatos con poco en común: el
 * CSV se va escribiendo, el Excel y el PDF se arman completos en memoria.
 */
class Exportador
{
    public const FORMATOS = ['csv', 'xlsx', 'pdf'];

    /** Azul caine, para el encabezado de la tabla. */
    private const AZUL = '2D2B5B';

    public function __construct(
        private readonly string $nombre,
        private readonly array $encabezados,
        private readonly array $filas,
    ) {}

    public function responder(string $formato): Response
    {
        return match ($formato) {
            'xlsx' => $this->excel(),
            'pdf' => $this->pdf(),
            default => $this->csv(),
        };
    }

    /** El nombre del archivo, sin extensión. */
    private function archivo(string $extension): string
    {
        $base = str_replace(' ', '-', strtolower($this->nombre));

        return "{$base}-" . now()->format('Ymd-His') . ".{$extension}";
    }

    /* ---------- CSV ---------- */

    private function csv(): StreamedResponse
    {
        $archivo = $this->archivo('csv');

        return new StreamedResponse(function () {
            $salida = fopen('php://output', 'w');

            // BOM para que Excel reconozca UTF-8 y no rompa las tildes.
            fwrite($salida, "\xEF\xBB\xBF");

            fputcsv($salida, $this->encabezados, '|');
            foreach ($this->filas as $fila) {
                fputcsv($salida, $fila, '|');
            }

            fclose($salida);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$archivo}\"",
        ]);
    }

    /* ---------- Excel ---------- */

    private function excel(): StreamedResponse
    {
        $archivo = $this->archivo('xlsx');

        $libro = new Spreadsheet();
        $hoja = $libro->getActiveSheet();
        // Excel no acepta más de 31 caracteres ni : \ / ? * [ ] en el nombre.
        $hoja->setTitle(substr(preg_replace('/[:\\\\\/?*\[\]]/', ' ', $this->nombre), 0, 31));

        $hoja->fromArray($this->encabezados, null, 'A1');

        // Se escriben como texto: un código como "0045873" o un expediente
        // "KID-2026001" no deben convertirse en número ni en fecha.
        foreach ($this->filas as $i => $fila) {
            foreach (array_values($fila) as $j => $celda) {
                $hoja->setCellValueExplicit(
                    [$j + 1, $i + 2],
                    (string) $celda,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            }
        }

        $ultima = $hoja->getHighestColumn();

        $encabezado = $hoja->getStyle("A1:{$ultima}1");
        $encabezado->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $encabezado->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . self::AZUL);
        $encabezado->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $hoja->getRowDimension(1)->setRowHeight(22);

        foreach (range('A', $ultima) as $columna) {
            $hoja->getColumnDimension($columna)->setAutoSize(true);
        }

        // Con el encabezado congelado y el autofiltro, la hoja se usa sin tener
        // que prepararla a mano cada vez.
        $hoja->freezePane('A2');
        $hoja->setAutoFilter("A1:{$ultima}1");

        return new StreamedResponse(function () use ($libro) {
            (new Xlsx($libro))->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $archivo . '"',
        ]);
    }

    /* ---------- PDF ---------- */

    private function pdf(): Response
    {
        $archivo = $this->archivo('pdf');

        $opciones = new Options();
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($opciones);
        $dompdf->loadHtml($this->html(), 'UTF-8');
        // Horizontal: un informe de catorce columnas no entra en vertical.
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $archivo . '"',
        ]);
    }

    private function html(): string
    {
        $e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

        $encabezados = implode('', array_map(fn($h) => '<th>' . $e($h) . '</th>', $this->encabezados));

        $filas = implode('', array_map(
            fn(array $fila) => '<tr>' . implode('', array_map(fn($c) => '<td>' . $e($c) . '</td>', $fila)) . '</tr>',
            $this->filas
        ));

        $titulo = $e($this->nombre);
        $azul = '#' . self::AZUL;
        $generado = now()->format('d/m/Y H:i');
        $total = count($this->filas);
        $registros = $total === 1 ? '1 registro' : "{$total} registros";

        // La hoja de estilos va embebida: dompdf no sale a la red a buscarla.
        return <<<HTML
        <html><head><meta charset="utf-8"><style>
            @page { margin: 12mm; }
            body { font-family: 'DejaVu Sans', sans-serif; font-size: 7.5pt; color: #374151; }
            h1 { font-size: 13pt; color: {$azul}; margin: 0 0 2px; }
            .pie { font-size: 7pt; color: #9ca3af; margin: 0 0 10px; }
            table { width: 100%; border-collapse: collapse; }
            th { background: {$azul}; color: #fff; text-align: left; padding: 5px 4px; font-size: 7pt; }
            td { border-bottom: 1px solid #e5e7eb; padding: 4px; vertical-align: top; }
            tr:nth-child(even) td { background: #faf9f7; }
        </style></head><body>
            <h1>{$titulo}</h1>
            <p class="pie">{$registros} · generado el {$generado}</p>
            <table>
                <thead><tr>{$encabezados}</tr></thead>
                <tbody>{$filas}</tbody>
            </table>
        </body></html>
        HTML;
    }
}
