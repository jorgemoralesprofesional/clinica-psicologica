# Sistema de Gestión - Clínica Psicológica

## Descripción del Proyecto
Este es el backend centralizado y robusto para la gestión integral de una clínica psicológica, desarrollado como Proyecto Final para el **Diplomado de Back-End Developer**. El sistema prioriza la seguridad, la transaccionalidad de datos y una experiencia de usuario estandarizada, permitiendo administrar profesionales de la salud mental, catálogos de especialidades con relaciones complejas, horarios laborales, expedientes de pacientes y la asignación de citas clínicas.

---

## Funcionalidades y Módulos Principales (CRUD)

* **Autenticación y Seguridad (Login):** Acceso protegido para administradores y recepcionistas mediante control de sesiones en PHP y cifrado seguro de credenciales con `password_hash()`.
* **Módulo de Psicólogos:** 
  * Registro y edición de perfiles profesionales con validación de correos únicos.
  * Configuración de duración de consulta (30, 45 o 60 minutos).
  * **Asignación Múltiple de Especialidades:** Interfaz visual basada en checkboxes conectados mediante una tabla pivote normalizada (`psicologo_especialidad`).
  * **Gestión de Horarios:** Asignación dinámica de días de atención laboral y bloques horarios por especialista (`horarios_atencion`).
* **Módulo de Pacientes:** Registro validando documentos de identidad, expedientes clínicos y control de búsquedas y modificaciones.
* **Módulo de Citas:** Consulta de disponibilidad de agenda, cálculo automático del tiempo de fin de consulta y validación lógica para evitar cruces de horarios entre profesionales.
* **UI/UX Estandarizada:** Integración global de **SweetAlert2** para notificaciones flotantes (Toasts) y diálogos de confirmación controlados mediante parámetros de URL (`?mensaje=` / `?error=`) saneados con `urlencode()`.

---

##  Tecnologías y Arquitectura

* **Backend:** PHP 8+ nativo estructurado bajo un enfoque modular y seguro.
* **Base de Datos:** MySQL / MariaDB con consultas preparadas mediante **PDO** y control de integridad transaccional (`beginTransaction`, `commit`, `rollBack`).
* **Frontend / UI:** Tailwind CSS (vía CDN) para un diseño adaptable, limpio y de utilidad rápida.
* **Control de Versiones:** Git y GitHub mediante flujos de trabajo basados en ramas de características (`feat/sweetalert2-integration`, `feature/especialidades-multiples`).

---

##  Requisitos Previos

Para levantar este proyecto en tu entorno local necesitas:
* Servidor web local compatible (XAMPP, MAMP, LAMP o Laragon).
* PHP versión 8.0 o superior.
* Servidor de base de datos MySQL o MariaDB.
* Git instalado en tu computadora.

---

##  Instalación y Configuración Paso a Paso

1. **Clonar el repositorio:**
   Abre tu terminal y ejecuta:
   ```bash
   git clone [https://github.com/jorgemoralesprofesional/clinica-psicologica.git](https://github.com/jorgemoralesprofesional/clinica-psicologica.git)
   cd clinica-psicologica