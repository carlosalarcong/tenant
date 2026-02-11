# Ejemplo de Uso de Traducciones de Mantenedores

## En Templates Twig

```twig
{# Usando el dominio maintainers #}
<h1>{{ 'maintainers.persona.title'|trans({}, 'maintainers') }}</h1>

{# En melisahospital mostrará: "Gestión de Pacientes Hospitalizados" #}
{# En melisalacolina mostrará: "Mis Pacientes Privados" #}
{# En default mostrará: "Gestión de Personas" #}

<button>{{ 'maintainers.common.create'|trans({}, 'maintainers') }}</button>
<span>{{ 'maintainers.persona.singular'|trans({}, 'maintainers') }}</span>
```

## En Controllers

```php
class PersonaController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {}
    
    public function index(): Response
    {
        $title = $this->translator->trans(
            'maintainers.persona.title',
            [],
            'maintainers'
        );
        
        // $title será diferente según el tenant:
        // - melisahospital: "Gestión de Pacientes Hospitalizados"
        // - melisalacolina: "Mis Pacientes Privados"
        // - default: "Gestión de Personas"
        
        return $this->render('persona/index.html.twig', [
            'page_title' => $title
        ]);
    }
}
```

## Estructura Creada

```
translations/
├── maintainers.es.yaml                    ← Fallback global (base)
├── maintainers.en.yaml                    ← Fallback global (inglés)
├── default/
│   └── maintainers.es.yaml                ← Terminología genérica
├── melisahospital/
│   └── maintainers.es.yaml                ← Terminología hospitalaria
└── melisalacolina/
    └── maintainers.es.yaml                ← Terminología clínica privada
```

## Orden de Fallback

1. **Tenant específico**: `translations/[tenant]/maintainers.es.yaml`
2. **Default tenant**: `translations/default/maintainers.es.yaml`
3. **Base global**: `translations/maintainers.es.yaml`
4. **Clave original**: Si no se encuentra en ningún lado

## Ejemplo de Diferencias por Tenant

### Clave: `maintainers.persona.title`

| Tenant | Traducción |
|--------|-----------|
| melisahospital | "Gestión de Pacientes Hospitalizados" |
| melisalacolina | "Mis Pacientes Privados" |
| default | "Gestión de Personas" |

### Clave: `maintainers.pabellon.title`

| Tenant | Traducción |
|--------|-----------|
| melisahospital | "Gestión de Pabellones Quirúrgicos" |
| melisalacolina | "Gestión de Consultorios" |
| default | "Gestión de Áreas" |
