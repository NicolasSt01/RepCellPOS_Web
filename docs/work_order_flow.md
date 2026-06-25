# Flujo de Orden de Trabajo (OT)

```mermaid
flowchart TD
    %% Estilos
    classDef terminal fill:#f43f5e,color:#fff,stroke:#e11d48
    classDef email fill:#3b82f6,color:#fff,stroke:#2563eb
    classDef action fill:#22c55e,color:#fff,stroke:#16a34a
    classDef state fill:#f59e0b,color:#fff,stroke:#d97706
    classDef client fill:#a855f7,color:#fff,stroke:#9333ea

    %% Nodos estado
    RECIBIDA["recibida"]
    ESPERA["en_espera"]
    REVISION["en_revision"]
    DIAG["diagnosticada"]
    COT_ENVIADA["cotizacion_enviada"]
    COT_APROBADA["cotizacion_aprobada"]
    REPARA["en_reparacion"]
    REPARADA["reparada"]
    TERMINADA["terminada"]
    CANCELADA["cancelada"]

    %% Acciones
    CREAR[\"Crear OT /]
    PRINT["Imprimir Ticket QR"]
    ASIGNAR["Asignar Técnico"]
    COTIZAR["Crear Cotización"]
    ADD_ITEM["Agregar Items"]
    ENVIAR_COT["Enviar Cotización"]
    APROBAR_COT["Aprobar Cotización"]
    RECHAZAR_COT["Rechazar Cotización"]
    CAMBIO_ESTADO["Cambiar Estado\n(changeStatus)"]
    TRACKING["Tracking Público\n/cliente"]

    %% Notificaciones
    NOTIF_CREACION{{"📧 NotificationService\norder_created\n→ WorkOrderReceipt"}}:::email
    NOTIF_COT_ENV{{"📧 NotificationService\nquote_sent\n→ WorkOrderStatusChanged"}}:::email
    NOTIF_COT_APR{{"📧 NotificationService\nquote_approved\n→ WorkOrderStatusChanged"}}:::email
    NOTIF_COT_REC{{"📧 NotificationService\nquote_rejected\n→ WorkOrderStatusChanged"}}:::email
    NOTIF_STATUS{{"📧 NotificationService\nstatus_changed\n→ WorkOrderStatusChanged"}}:::email

    %% Flujo principal
    CREAR --> RECIBIDA
    RECIBIDA -->|auto| ESPERA
    RECIBIDA -.->|validTransition| CANCELADA

    CREAR -.-> PRINT
    CREAR -..-> NOTIF_CREACION

    ESPERA -->|quote send| COT_ENVIADA
    ESPERA --> REVISION
    ESPERA -.->|validTransition| CANCELADA

    REVISION --> DIAG
    REVISION -.->|validTransition| CANCELADA

    DIAG -->|quote send| COT_ENVIADA
    DIAG -.->|validTransition| CANCELADA

    COT_ENVIADA -->|quote approve| COT_APROBADA
    COT_ENVIADA -.->|quote reject| CANCELADA

    COT_APROBADA --> REPARA
    COT_APROBADA -.->|validTransition| CANCELADA

    REPARA --> REPARADA
    REPARA -.->|validTransition| CANCELADA

    REPARADA --> TERMINADA

    %% #3: ready_for_pickup
    REPARADA -..-> NOTIF_PICKUP{{"📧 NotificationService\nready_for_pickup\n→ WorkOrderStatusChanged"}}:::email
    NOTIF_PICKUP -.->|3 días sin recoger| RECORDATORIO{{"📧 NotificationService\npickup_reminder\n(comando diario)"}}:::email

    %% Cotización
    COTIZAR --> ADD_ITEM
    ADD_ITEM --> ENVIAR_COT
    ENVIAR_COT --> COT_ENVIADA
    ENVIAR_COT -..-> NOTIF_COT_ENV

    APROBAR_COT --> COT_APROBADA
    APROBAR_COT -..-> NOTIF_COT_APR

    RECHAZAR_COT --> CANCELADA
    RECHAZAR_COT -..-> NOTIF_COT_REC

    %% Tracking
    TRACKING --> APROBAR_COT
    TRACKING --> RECHAZAR_COT

    %% ChangeStatus
    CAMBIO_ESTADO -->|validate canTransitionTo| COT_APROBADA
    CAMBIO_ESTADO -->|validate canTransitionTo| REPARA
    CAMBIO_ESTADO -->|validate canTransitionTo| REPARADA
    CAMBIO_ESTADO -->|validate canTransitionTo| TERMINADA
    CAMBIO_ESTADO -->|validate canTransitionTo| CANCELADA
    CAMBIO_ESTADO -->|validate canTransitionTo| REVISION
    CAMBIO_ESTADO -->|validate canTransitionTo| DIAG
    CAMBIO_ESTADO -..-> NOTIF_STATUS

    %% Extras
    ASIGNAR -.->|sin notificación| ESPERA

    TERMINADA:::terminal
    CANCELADA:::terminal
    RECIBIDA:::state
    ESPERA:::state
    REVISION:::state
    DIAG:::state
    COT_ENVIADA:::state
    COT_APROBADA:::state
    REPARA:::state
    REPARADA:::state
    CREAR:::action
    ENVIAR_COT:::action
    APROBAR_COT:::action
    RECHAZAR_COT:::action
    CAMBIO_ESTADO:::action
    TRACKING:::client
```

## Eventos vs Estado

| Evento | Status que asigna | ¿Quién lo cambia? | ¿Notifica? |
|--------|------------------|-------------------|------------|
| `order_created` | `recibida` → `en_espera` (auto) | `WorkOrderController::store()` | ✅ `WorkOrderReceipt` |
| `quote_sent` | → `cotizacion_enviada` | `QuoteController::send()` | ✅ `WorkOrderStatusChanged` |
| `quote_approved` | → `cotizacion_aprobada` | `Quote::approve()` / `TrackingController` | ✅ `WorkOrderStatusChanged` |
| `quote_rejected` | → `cancelada` | `Quote::reject()` / `TrackingController` | ✅ `WorkOrderStatusChanged` |
| `status_changed` | cualquiera válido | `WorkOrderController::changeStatus()` | ✅ `WorkOrderStatusChanged` |
| `ready_for_pickup` | `reparada` | `WorkOrderController::changeStatus()` (adicional) | ✅ `WorkOrderStatusChanged` |
| `pickup_reminder` | `reparada` (≥3d) | `pickup:remind` (comando diario) | ✅ `WorkOrderStatusChanged` |

## Canales de notificación

```
notification_preference → NotificationService::dispatch()
  ├── email   → sendEmail()   → Mail::to( )  ──→ WorkOrderReceipt / WorkOrderStatusChanged
  ├── whatsapp → sendWhatsapp() → webhook n8n ──→ mensaje texto
  └── call     → markAsLogged()
```
