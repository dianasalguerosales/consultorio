<?php

namespace App\Http\Controllers;

use App\Personas\Personas;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Los roles de una persona se le asignan a su usuario: en este sistema los
 * roles viven en `users` (Spatie), no en las tablas de personas.
 */
class RolesController extends Controller
{
    public function update(Request $request, string $tipo, int $id)
    {
        $persona = $this->persona($tipo, $id);
        $user = $persona->user;

        abort_unless(
            $user,
            422,
            'Debe asignar un usuario a esta persona para poder gestionar sus roles.'
        );

        $datos = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $roles = $datos['roles'] ?? [];

        $this->verificarQuePuedaOtorgar($request, $user, $roles);
        $this->verificarQueNoSeQuedeSinAdministrador($request, $user, $roles);

        $user->syncRoles($roles);

        return back()->with(
            'success',
            $roles
                ? 'Roles actualizados: ' . implode(', ', $roles) . '.'
                : 'Se le quitaron todos los roles.'
        );
    }

    private function persona(string $tipo, int $id)
    {
        $modelo = Personas::modelo($tipo);

        abort_unless($modelo, 404, "El tipo de persona '{$tipo}' no existe.");

        return $modelo::with('user')->findOrFail($id);
    }

    /**
     * El rol de administrador solo lo da o lo quita un administrador.
     *
     * Sin esto un coordinador podía nombrar administrador a un colega y que
     * ese colega lo nombrara de vuelta: dos pasos para escalar sin que ningún
     * administrador lo autorice.
     */
    private function verificarQuePuedaOtorgar(Request $request, $user, array $roles): void
    {
        if ($request->user()->hasRole('administrador')) {
            return;
        }

        $tocaAdministrador = in_array('administrador', $roles, true)
            !== $user->hasRole('administrador');

        if ($tocaAdministrador) {
            throw ValidationException::withMessages([
                'roles' => 'Solo un administrador puede otorgar o quitar el rol de administrador.',
            ]);
        }
    }

    /**
     * Dos candados que evitan quedarse afuera del sistema:
     *
     * nadie edita sus propios roles, y no se puede dejar la aplicación sin
     * ningún administrador. Los dos son irreversibles desde la interfaz.
     */
    private function verificarQueNoSeQuedeSinAdministrador(Request $request, $user, array $roles): void
    {
        if ($user->id === $request->user()->id) {
            throw ValidationException::withMessages([
                'roles' => 'No puede cambiar sus propios roles. Pídaselo a otro administrador.',
            ]);
        }

        if (! $user->hasRole('administrador') || in_array('administrador', $roles, true)) {
            return;
        }

        $otros = Role::where('name', 'administrador')->first()
            ?->users()->where('users.id', '!=', $user->id)->count() ?? 0;

        if ($otros === 0) {
            throw ValidationException::withMessages([
                'roles' => 'Es el único administrador. Asigne el rol a otra persona antes de quitárselo.',
            ]);
        }
    }
}
