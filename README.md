# Sistema de Gestión - Clínica Psicológica

## Descripción del Proyecto
Este es el backend centralizado para la gestión de citas y pacientes en una clínica psicológica, desarrollado como Proyecto Final para el Diplomado de Back-End Developer. El sistema permite administrar de forma segura los registros de pacientes, el directorio de psicólogos, la disponibilidad de horarios y la asignación de citas.

## Funcionalidades Principales (CRUD)
- **Autenticación (Login):** Acceso seguro para recepcionistas mediante sesiones en PHP y contraseñas encriptadas (`password_hash`).
- **Módulo de Psicólogos:** Registro de profesionales (validando correos únicos), listado y asignación de días/horarios de atención.
- **Módulo de Pacientes:** Registro validando documento de identidad y correo, búsqueda y edición de expedientes.
- **Módulo de Citas:** Consulta de disponibilidad, cálculo automático del fin de consulta y validación para evitar cruces de horarios.

## Tecnologías Utilizadas
- **Backend:** PHP 8+ (nativo).
- **Base de Datos:** MySQL / MariaDB (conexión segura mediante PDO).
- **Frontend / UI:** HTML5 y Tailwind CSS (mediante CDN para un diseño moderno).

## Requisitos Previos
Para levantar este proyecto en tu entorno local necesitas:
- Servidor web local (XAMPP, MAMP, LAMP o Laragon).
- PHP versión 8.0 o superior.
- Servidor de base de datos MySQL o MariaDB.
- Git instalado en tu computadora.

## Instalación y Configuración Paso a Paso

1. **Clonar el repositorio:**
   Abre tu terminal y ejecuta:
   ```bash
   git clone [https://github.com/jorgemoralesprofesional/clinica-psicologica.git](https://github.com/jorgemoralesprofesional/clinica-psicologica.git)
   cd clinica-psicologica
