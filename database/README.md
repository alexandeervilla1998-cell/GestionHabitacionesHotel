# Base de datos

Motor: **MySQL**. Todas las tablas de negocio manejan borrado lógico mediante la columna `activo` (excepto `facturas` y `pagos`, donde se removió tras la migración `quitar_activo_facturas_pagos`, ya que una factura/pago emitido no debe desactivarse).

Esquema definido en [migrations/](migrations); datos de ejemplo en [seeders/](seeders).

## Tablas principales

**`usuarios`** — cuentas de acceso al panel (admin/recepcionista)
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| nombre | string(100) | |
| correo | string(150) | unique |
| password | string(255) | hash |
| rol | enum | `admin`, `recepcionista` |
| activo | boolean | default true |
| creado_en / actualizado_en | timestamp | |

**`clientes`** — huéspedes del hotel
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| nombre | string(100) | |
| correo | string(150) | unique |
| telefono | string(20) | nullable |
| identificacion | string(50) | unique, nullable |
| activo | boolean | default true |
| creado_en / actualizado_en | timestamp | |

**`habitaciones`** — inventario de habitaciones
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| numero | string(10) | unique |
| tipo | enum | `individual`, `doble`, `suite`, `familiar` |
| precio_por_noche | decimal(10,2) | |
| estado | enum | `disponible`, `ocupada`, `mantenimiento` |
| imagen | string(255) | nullable, ruta en `public/uploads/habitaciones` |
| activo | boolean | default true |
| creado_en / actualizado_en | timestamp | |

**`servicios`** — catálogo de servicios adicionales
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| nombre | string(100) | |
| precio | decimal(10,2) | |
| activo | boolean | default true |
| creado_en / actualizado_en | timestamp | |

**`reservas`** — reservas de huéspedes
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| cliente_id | FK → clientes.id | on delete cascade |
| fecha_entrada / fecha_salida | date | |
| estado | enum | `pendiente`, `confirmada`, `cancelada`, `completada` |
| activo | boolean | default true |
| creado_en / actualizado_en | timestamp | |

**`detalle_reservas`** — habitación(es) asociadas a una reserva (línea de detalle)
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| reserva_id | FK → reservas.id | on delete cascade |
| habitacion_id | FK → habitaciones.id | on delete restrict |
| noches | integer | |
| precio_noche | decimal(10,2) | precio congelado al momento de reservar |
| subtotal | decimal(10,2) | |
| activo | boolean | default true |

**`reserva_servicio`** — servicios adicionales contratados en una reserva (tabla pivote con datos extra)
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| reserva_id | FK → reservas.id | on delete cascade |
| servicio_id | FK → servicios.id | on delete restrict |
| cantidad | integer | |
| precio_unitario | decimal(10,2) | |
| subtotal | decimal(10,2) | |
| activo | boolean | default true |

**`facturas`** — una factura por reserva confirmada
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| reserva_id | FK → reservas.id, unique | on delete cascade |
| numero_factura | string(50) | unique, formato `FAC-AAAA-000000` |
| subtotal / impuestos / total | decimal(10,2) | |
| fecha_emision | timestamp | nullable |

**`pagos`** — pagos aplicados a una factura
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| factura_id | FK → facturas.id | on delete cascade |
| monto | decimal(10,2) | |
| metodo_pago | enum | `efectivo`, `tarjeta_crédito`, `tarjeta_débito`, `transferencia` |
| estado_pago | enum | `pendiente`, `completado`, `fallido`, `reembolsado` |
| creado_en | timestamp | |

## Relaciones

```
usuarios (staff)            clientes (huéspedes)
                                  │ 1
                                  │
                                  ▼ N
                              reservas ──┬── N:1 ──▶ (vía detalle_reservas) habitaciones
                                  │ 1     └── N:1 ──▶ (vía reserva_servicio) servicios
                                  ▼ 1
                              facturas
                                  │ 1
                                  ▼ N
                                pagos
```

- Un cliente puede tener muchas reservas.
- Una reserva pertenece a un cliente y, mediante `detalle_reservas`, a una o más habitaciones; mediante `reserva_servicio`, a cero o más servicios.
- Una reserva confirmada genera una única factura.
- Una factura puede tener múltiples pagos (pagos parciales) hasta cubrir el `saldo_pendiente`.
- `usuarios` es independiente de `clientes`: el staff (admin/recepcionista) no reserva habitaciones, solo administra el sistema.

## Datos de ejemplo (seeders)

Ejecutar con `php artisan migrate --seed`:
- `UsuarioSeeder` — 1 admin y 2 recepcionistas
- `ClienteSeeder` — 5 clientes
- `HabitacionSeeder` — 8 habitaciones
- `ServicioSeeder` — 10 servicios
