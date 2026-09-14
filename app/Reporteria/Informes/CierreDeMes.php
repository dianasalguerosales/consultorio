<?php

namespace App\Reporteria\Informes;

use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Servicio;
use App\Models\User;
use App\Reporteria\Informe;

/**
 * El cierre contable del período: todo lo que se cobró, con quién lo cobró y
 * quién lo respaldó.
 *
 * Va sobre `pagos` y no sobre `citas` como la vista de Pagos: ahí se listan las
 * citas para saber cuáles faltan cobrar, y acá interesa solo el dinero que
 * entró. Por eso una cita sin pagar no aparece — no es una fila del cierre.
 */
class CierreDeMes extends Informe
{
    public function clave(): string
    {
        return 'cierre-de-mes';
    }

    public function nombre(): string
    {
        return 'Cierre de mes';
    }

    public function descripcion(): string
    {
        return 'Lo cobrado en el período, con quién registró y quién autorizó.';
    }

    public function tablas(): string
    {
        return 'pagos + citas + pacientes + servicios + users';
    }

    public function consulta()
    {
        // Los roles hacen falta para decir si el cobro necesitaba autorización.
        $personas = ['terapeuta', 'administrativo', 'encargado'];

        return Pago::query()
            ->with([
                'paciente',
                'cita.servicio',
                'cita.atendidoPor',
                'programa',
                'registradoPor' => fn($u) => $u->with($personas)->with('roles:id,name'),
                'autorizadoPor' => fn($u) => $u->with($personas),
            ])
            ->orderBy('fecha')
            ->orderBy('id');
    }

    public function columnas(): array
    {
        return [
            'fecha' => ['etiqueta' => 'Fecha de pago', 'valor' => fn(Pago $p) => $this->fecha($p->fecha)],
            'paciente' => ['etiqueta' => 'Paciente', 'valor' => fn(Pago $p) => $this->nombrePaciente($p->paciente)],
            'sesion' => ['etiqueta' => 'Fecha de la sesión', 'valor' => fn(Pago $p) => $this->fecha($p->cita?->fecha)],
            'servicio' => ['etiqueta' => 'Servicio', 'valor' => fn(Pago $p) => $p->cita?->servicio?->nombre],
            'atiende' => ['etiqueta' => 'Atiende', 'valor' => fn(Pago $p) => $this->atiende($p->cita)],
            'programa' => ['etiqueta' => 'Programa', 'valor' => fn(Pago $p) => $p->programa?->nombre],
            'monto' => ['etiqueta' => 'Monto', 'valor' => fn(Pago $p) => $p->monto],
            'precio' => ['etiqueta' => 'Precio de la cita', 'valor' => fn(Pago $p) => $p->cita?->precio_aplicado],
            'metodo' => ['etiqueta' => 'Tipo de pago', 'valor' => fn(Pago $p) => $p->metodo],
            'documento' => ['etiqueta' => 'N.° de documento', 'valor' => fn(Pago $p) => $p->numero_autorizacion],
            'estado' => ['etiqueta' => 'Estado del pago', 'valor' => fn(Pago $p) => ucfirst((string) $p->estado)],
            'registro' => ['etiqueta' => 'Registró', 'valor' => fn(Pago $p) => $p->registradoPor?->nombre_completo],
            'autoriza' => ['etiqueta' => 'Autorizó', 'valor' => fn(Pago $p) => $this->quienAutoriza($p)],
            'autorizado_en' => ['etiqueta' => 'Fecha autorización', 'valor' => fn(Pago $p) => $p->autorizado_en?->format('d/m/Y H:i')],
        ];
    }

    public function filtros(): array
    {
        return array_merge(
            $this->rangoFechas('fecha', 'Pago'),
            $this->filtroPaciente(fn($q, $v) => $q->where('paciente_id', $v)),
            [
                'metodo' => [
                    'etiqueta' => 'Tipo de pago',
                    'tipo' => 'select',
                    // Lista fija del modelo, no un catálogo: ver Pago::METODOS.
                    'opciones' => fn() => array_map(
                        fn(string $m) => ['valor' => $m, 'etiqueta' => $m],
                        Pago::METODOS
                    ),
                    'aplicar' => fn($q, $v) => $q->where('metodo', $v),
                ],

                // Es lo que separa una sucursal de la oficina central: quién cobró.
                'registrado_por' => [
                    'etiqueta' => 'Registró',
                    'tipo' => 'select',
                    'opciones' => $this->opcionesUsuarios(),
                    'aplicar' => fn($q, $v) => $q->where('registrado_por', $v),
                ],

                'autorizado_por' => [
                    'etiqueta' => 'Autorizó',
                    'tipo' => 'select',
                    'opciones' => $this->opcionesUsuarios(),
                    'aplicar' => fn($q, $v) => $q->where('autorizado_por', $v),
                ],

                'sin_autorizar' => [
                    'etiqueta' => 'Solo cobros sin autorizar',
                    'tipo' => 'checkbox',
                    // Los que espera autorización son los de un auxiliar: se
                    // descartan los roles que cobran por sí mismos.
                    'aplicar' => fn($q, $v) => $q
                        ->whereNull('autorizado_por')
                        ->whereHas('registradoPor', fn($u) => $u->whereDoesntHave(
                            'roles',
                            fn($r) => $r->whereIn('name', Pago::ROLES_SIN_AUTORIZACION)
                        )),
                ],
            ]
        );
    }

    /**
     * Quién autorizó, o por qué no hay nadie. Un guion suelto dejaría igual al
     * cobro que espera autorización y al que nunca la necesitó.
     */
    private function quienAutoriza(Pago $pago): string
    {
        if ($pago->autorizadoPor) {
            return $pago->autorizadoPor->nombre_completo;
        }

        if (! $pago->registradoPor) {
            return 'Sin registrar';
        }

        return $pago->requiereAutorizacion() ? 'Pendiente de autorizar' : 'No requiere';
    }

    /** Los usuarios que pueden aparecer cobrando o autorizando. */
    private function opcionesUsuarios(): callable
    {
        return fn() => User::with(['terapeuta', 'administrativo', 'encargado'])
            ->whereHas('roles', fn($r) => $r->whereIn('name', ['administrador', 'coordinador', 'auxiliar']))
            ->get()
            ->map(fn(User $u) => ['valor' => $u->id, 'etiqueta' => $u->nombre_completo])
            ->sortBy('etiqueta')
            ->values()
            ->all();
    }
}
