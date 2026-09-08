// Selección centralizada de avatares. Todos los assets viven en public/images.
//
// Antes cada vista repetía su propia cadena de ternarios y leía el género de
// forma distinta (objeto vs string, normalizado vs no), lo que hacía que
// Usuarios.vue y Expedientes.vue nunca acertaran el match. Aquí queda una sola
// regla por tipo de sujeto: usuarios se distinguen por rol, pacientes por género.

const GENERICO = '/images/avatar.webp';

// La BD guarda "Femenino"/"Masculino" capitalizado, y según la vista el género
// llega como objeto ({ nombre }) o como string plano. Aceptamos ambos.
const normalizarGenero = (genero) => {
    const valor = typeof genero === 'object' && genero !== null ? genero.nombre : genero;
    return valor?.toLowerCase() ?? null;
};

/* ---------- Usuarios (por rol) ---------- */

const POR_ROL = {
    administrador: '/images/Admin.webp',
    coordinador: '/images/Coordinador.webp',
    terapeuta: '/images/Terapeuta.webp',
    auxiliar: '/images/Auxiliar.webp',
};

// Orden de prioridad cuando el usuario tiene más de un rol.
const JERARQUIA = ['administrador', 'coordinador', 'terapeuta', 'auxiliar', 'encargado', 'pruebas'];

export const rolPrincipal = (roles = []) => JERARQUIA.find((r) => roles.includes(r)) ?? null;

export function avatarUsuario(roles = [], genero = null) {
    const rol = rolPrincipal(roles);

    // El encargado no tiene imagen de rol: se distingue por género (Madre/Padre).
    if (rol === 'encargado') {
        const g = normalizarGenero(genero);
        if (g === 'femenino') return '/images/Madre.webp';
        if (g === 'masculino') return '/images/Padre.webp';
        return GENERICO;
    }

    return POR_ROL[rol] ?? GENERICO;
}

export const ETIQUETAS_ROL = {
    administrador: 'Administrador',
    coordinador: 'Coordinador',
    terapeuta: 'Terapeuta',
    auxiliar: 'Auxiliar',
    encargado: 'Encargado',
    pruebas: 'Pruebas',
};

/* ---------- Pacientes (por género) ---------- */

export function avatarPaciente(genero = null) {
    const g = normalizarGenero(genero);
    if (g === 'femenino') return '/images/Femenino.webp';
    if (g === 'masculino') return '/images/Masculino.webp';
    return GENERICO;
}
