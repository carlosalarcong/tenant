# Datos Dummy - Migración PostgreSQL 16

## Resumen

Se han insertado datos dummy en las 3 bases de datos tenant después de la migración exitosa de MySQL 8.0.43 a PostgreSQL 16.11.

**IMPORTANTE:** Todos los usuarios tienen password: `123456`

Hash bcrypt utilizado: `$2y$10$4geWtxz82BfqJIlttDElLOZfoyyRzedb/LPijIkP.4AfCp.lqVqHa`

---

## Base de datos: melisalacolina

### Usuarios (4)
- **admin.lacolina** (admin@lacolina.cl) - ROLE_ADMIN
- **dr.gonzalez** (doctor@lacolina.cl) - ROLE_DOCTOR
- **enf.martinez** (enfermera@lacolina.cl) - ROLE_NURSE
- **recep.sanchez** (recepcion@lacolina.cl) - ROLE_RECEPTIONIST

### Personas (3)
- **Pedro Pérez González** (RUT: 12345678-9)
  - Fecha nacimiento: 1985-03-15
  - Educación: Técnica
  - Estado civil: Casado/a
  - Ocupación: Empleado/a
  - Teléfono: +56912345678
  - Email: pedro@lacolina.cl

- **María López Martínez** (RUT: 98765432-1)
  - Fecha nacimiento: 1990-07-22
  - Educación: Media
  - Estado civil: Soltero/a
  - Ocupación: Independiente
  - Teléfono: +56987654321
  - Email: maria@lacolina.cl

- **Jorge Silva Rojas** (RUT: 11222333-4)
  - Fecha nacimiento: 1955-11-10
  - Educación: Básica
  - Estado civil: Divorciado/a
  - Ocupación: Jubilado/a
  - Teléfono: +56911222333
  - Email: jorge@lacolina.cl

---

## Base de datos: melisa_template

### Usuarios (4)
- **admin.template** (admin@template.cl) - ROLE_ADMIN
- **dr.fernandez** (doctor@template.cl) - ROLE_DOCTOR
- **enf.castro** (enfermera@template.cl) - ROLE_NURSE
- **recep.rojas** (recepcion@template.cl) - ROLE_RECEPTIONIST

### Personas (3)
- **Carlos Fernández Torres** (RUT: 15555666-7)
  - Fecha nacimiento: 1982-05-20
  - Educación: Universitaria
  - Estado civil: Casado/a
  - Ocupación: Empleado/a
  - Teléfono: +56915555666
  - Email: carlos@template.cl

- **Ana Castro Muñoz** (RUT: 16777888-9)
  - Fecha nacimiento: 1995-09-14
  - Educación: Técnica
  - Estado civil: Soltero/a
  - Ocupación: Estudiante
  - Teléfono: +56916777888
  - Email: ana@template.cl

- **Rosa Rojas Vargas** (RUT: 13444555-K)
  - Fecha nacimiento: 1960-12-03
  - Educación: Media
  - Estado civil: Viudo/a
  - Ocupación: Jubilado/a
  - Teléfono: +56913444555
  - Email: rosa@template.cl

---

## Base de datos: melisahospital

### Usuarios (4)
- **admin.hospital** (admin@hospital.cl) - ROLE_ADMIN
- **dr.morales** (doctor@hospital.cl) - ROLE_DOCTOR
- **enf.diaz** (enfermera@hospital.cl) - ROLE_NURSE
- **recep.torres** (recepcion@hospital.cl) - ROLE_RECEPTIONIST

### Personas (3)
- **Luis Morales Bravo** (RUT: 17888999-0)
  - Fecha nacimiento: 1978-11-25
  - Educación: Universitaria
  - Estado civil: Soltero/a
  - Ocupación: Empleado/a
  - Teléfono: +56917888999
  - Email: luis@hospital.cl

- **Patricia Díaz Soto** (RUT: 18999000-1)
  - Fecha nacimiento: 1988-04-17
  - Educación: Técnica
  - Estado civil: Casado/a
  - Ocupación: Independiente
  - Teléfono: +56918999000
  - Email: patricia@hospital.cl

- **Manuel Torres Ramírez** (RUT: 14555666-7)
  - Fecha nacimiento: 1965-08-30
  - Educación: Media
  - Estado civil: Divorciado/a
  - Ocupación: Desempleado/a
  - Teléfono: +56914555666
  - Email: manuel@hospital.cl

---

## Mantenedores Insertados (en las 3 bases)

### Geográficos
- **Países:** 3 (Chile, Argentina, Perú)
- **Regiones:** 3 (Metropolitana, Valparaíso, Biobío)
- **Provincias:** 3 (Santiago, Valparaíso, Concepción)
- **Municipios:** 4 (Las Condes, Providencia, Viña del Mar, Concepción)

### Generales
- **Géneros:** 3 (Masculino, Femenino, Otro)
- **Tipos de Identificación:** 3 (RUT, Pasaporte, Cédula Extranjera)
- **Estados Civiles:** 4 (Soltero/a, Casado/a, Divorciado/a, Viudo/a)
- **Niveles Educativos:** 5 (Sin Educación, Básica, Media, Técnica, Universitaria)
- **Ocupaciones:** 5 (Empleado/a, Independiente, Estudiante, Jubilado/a, Desempleado/a)
- **Religiones:** 5 (Católica, Evangélica, Musulmana, Judaica, Otra)
- **Grupos Étnicos:** 4 (Aymara, Quechua, Mapuche, Ninguno)

### Salud
- **Tipos de Seguros:** 3 (FONASA, ISAPRE, Particular)
- **Seguros de Salud:** 4 (FONASA A, FONASA B, Banmédica, Colmena)

---

## Scripts Utilizados

### Script SQL de Mantenedores
Ubicación: `/var/www/html/melisa_tenant/scripts/insert_dummy_data.sql`

Este script contiene todos los mantenedores básicos que se insertaron en las 3 bases de datos.

### Comandos de Inserción de Personas
Se utilizaron comandos SQL directos personalizados para cada tenant, asegurando que cada base de datos tenga datos diferentes y únicos.

---

## Verificación

Para verificar los datos insertados en cualquier tenant:

```bash
# Ver usuarios
PGPASSWORD=melisamelisa psql -U melisa -h localhost -d melisalacolina -c "SELECT username, email FROM member ORDER BY id;"

# Ver personas
PGPASSWORD=melisamelisa psql -U melisa -h localhost -d melisalacolina -c "SELECT name, last_name, identification, email FROM person ORDER BY id;"

# Ver mantenedores
PGPASSWORD=melisamelisa psql -U melisa -h localhost -d melisalacolina -c "
  SELECT 'Usuarios' as tabla, COUNT(*) as total FROM member
  UNION ALL SELECT 'Personas', COUNT(*) FROM person
  UNION ALL SELECT 'Países', COUNT(*) FROM pais
  UNION ALL SELECT 'Regiones', COUNT(*) FROM region;"
```

---

## Notas Importantes

1. **Password:** Todos los usuarios (member) tienen el mismo password hasheado: `123456`
2. **Datos Únicos:** Cada base de datos tiene usuarios y personas diferentes
3. **Mantenedores Comunes:** Los mantenedores básicos son los mismos en las 3 bases
4. **Estado de Migración:** Todas las bases están marcadas como `DATABASE_MIGRATED` en la tabla `tenant_db` de `melisa_central`

---

**Fecha de creación:** 2026-01-31
**Autor:** Sistema de migración PostgreSQL
