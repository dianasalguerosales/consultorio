<?php

namespace App\Http\Controllers;

use App\Reporteria\Informe;
use App\Reporteria\Registro;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InformesController extends Controller
{
    // Tope de la vista previa. La descarga no lo aplica: ahí van todas.
    private const TOPE_VISTA = 200;

    public function index(Request $request)
    {
        $clave = $request->query('informe');

        return Inertia::render('Informes', [
            'catalogo' => Registro::catalogo(),
            'seleccion' => $clave,
            'resultado' => $clave ? $this->resultado($request, $clave, self::TOPE_VISTA) : null,
        ]);
    }

    public function exportar(Request $request)
    {
        $clave = $request->query('informe');
        $resultado = $this->resultado($request, $clave);

        abort_unless($resultado, 404, 'Informe no encontrado.');

        $nombre = str_replace(' ', '-', strtolower($resultado['nombre']));
        $archivo = "{$nombre}-" . now()->format('Ymd-His') . '.csv';

        return new StreamedResponse(function () use ($resultado) {
            $salida = fopen('php://output', 'w');

            // BOM para que Excel reconozca UTF-8 y no rompa las tildes.
            fwrite($salida, "\xEF\xBB\xBF");

            fputcsv($salida, $resultado['encabezados'], '|');
            foreach ($resultado['filas'] as $fila) {
                fputcsv($salida, $fila, '|');
            }

            fclose($salida);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$archivo}\"",
        ]);
    }

    /**
     * Arma el informe. Todo lo que llega del navegador se valida contra la
     * definición: si una columna o un filtro no está declarado, se ignora.
     */
    private function resultado(Request $request, ?string $clave, ?int $tope = null): ?array
    {
        $informe = $clave ? Registro::buscar($clave) : null;

        if (! $informe) {
            return null;
        }

        $definidas = $informe->columnas();
        $columnas = $this->columnasPedidas($request, $definidas);
        $filtros = $this->filtrosAplicados($request, $informe);

        $consulta = $informe->consulta();

        foreach ($filtros as $llave => $valor) {
            ($informe->filtros()[$llave]['aplicar'])($consulta, $valor);
        }

        $registros = $consulta->get();

        // Algunos informes rinden varias filas por registro (un expediente con
        // tres diagnósticos son tres renglones).
        $expandir = $informe->expandir();
        $filas = $expandir ? $registros->flatMap($expandir) : $registros;

        $total = $filas->count();
        if ($tope) {
            $filas = $filas->take($tope);
        }

        return [
            'clave' => $clave,
            'nombre' => $informe->nombre(),
            'encabezados' => array_map(fn($c) => $definidas[$c]['etiqueta'], $columnas),
            'columnas' => $columnas,
            'filas' => $filas->map(fn($registro) => array_map(
                fn($c) => $this->texto(($definidas[$c]['valor'])($registro)),
                $columnas
            ))->values()->all(),
            'total' => $total,
            'mostradas' => $tope ? min($total, $tope) : $total,
            'filtros' => $filtros,
        ];
    }

    /** Sin selección explícita se devuelven todas las columnas del informe. */
    private function columnasPedidas(Request $request, array $definidas): array
    {
        $disponibles = array_keys($definidas);
        $pedidas = (array) $request->query('columnas', []);
        $validas = array_values(array_intersect($pedidas, $disponibles));

        return $validas ?: $disponibles;
    }

    private function filtrosAplicados(Request $request, Informe $informe): array
    {
        $aplicados = [];

        foreach ($informe->filtros() as $llave => $filtro) {
            $valor = $request->query("filtros.$llave") ?? ($request->query('filtros')[$llave] ?? null);

            if ($valor === null || $valor === '' || $valor === 'false') {
                continue;
            }

            $aplicados[$llave] = $valor;
        }

        return $aplicados;
    }

    /** Deja cualquier valor listo para una celda de texto. */
    private function texto($valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        // Un salto de línea dentro de una celda rompe la lectura del CSV.
        return trim(preg_replace('/\s*\R\s*/', ' ', (string) $valor));
    }
}
