<?php

namespace App\Http\Controllers;

use App\Catalogos\Catalogos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * El CRUD de los seis catálogos de /parametros. Reemplaza a los controladores
 * de Servicio, Especialidad y Escolaridad, que eran el mismo código tres veces.
 */
class CatalogoController extends Controller
{
    /** Validación de los campos extra que declara el registro. */
    private const REGLAS = [
        'entero' => ['nullable', 'integer', 'min:1'],
        'moneda' => ['nullable', 'numeric', 'min:0'],
        'texto' => ['nullable', 'string', 'max:255'],
    ];

    public function store(Request $request, string $catalogo)
    {
        $c = $this->definicion($catalogo);

        $c['modelo']::create($this->validar($request, $c));

        return back()->with('success', $this->mensaje($c, 'cread'));
    }

    public function update(Request $request, string $catalogo, int $id)
    {
        $c = $this->definicion($catalogo);
        $registro = $c['modelo']::findOrFail($id);

        $registro->update($this->validar($request, $c, $id));

        return back()->with('success', $this->mensaje($c, 'actualizad'));
    }

    public function destroy(string $catalogo, int $id)
    {
        $c = $this->definicion($catalogo);

        $c['modelo']::findOrFail($id)->delete();

        return back()->with('success', $this->mensaje($c, 'eliminad'));
    }

    /** El catálogo tiene que estar en el registro; si no, no existe. */
    private function definicion(string $clave): array
    {
        $catalogo = Catalogos::buscar($clave);

        abort_unless($catalogo, 404, "El catálogo '{$clave}' no existe.");

        return $catalogo;
    }

    private function validar(Request $request, array $c, ?int $ignorar = null): array
    {
        $reglas = [
            'nombre' => ['required', 'string', 'max:255', Rule::unique(Catalogos::tabla($c), 'nombre')->ignore($ignorar)],
            'activo' => ['boolean'],
        ];

        if ($c['conDescripcion']) {
            $reglas['descripcion'] = ['nullable', 'string'];
        }

        $etiquetas = [];

        foreach ($c['campos'] as $campo) {
            $reglas[$campo['clave']] = self::REGLAS[$campo['tipo']];
            // Sin esto el error sale como "sesiones por mes" en vez de
            // "Cantidad de citas", que es lo que dice la pantalla.
            $etiquetas[$campo['clave']] = $campo['etiqueta'];
        }

        return $request->validate($reglas, [], $etiquetas);
    }

    /** "Servicio creado", "Escolaridad creada": el género va en el registro. */
    private function mensaje(array $c, string $verbo): string
    {
        return "{$c['titulo']} {$verbo}{$c['genero']} correctamente";
    }
}
