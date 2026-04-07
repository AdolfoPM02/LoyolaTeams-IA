# Criterios de aceptación de la PoC

## Semana 1
- El repositorio tiene estructura base definida.
- Existe un backend FastAPI funcional.
- El endpoint `GET /health` responde correctamente.
- El endpoint `POST /chat` acepta un contrato definido.
- El proyecto arranca en local mediante Docker Compose.
- El flujo de GitHub queda definido con `main` y `develop`.

## Objetivo general de la PoC
- El sistema debe recibir usuario, curso, rol y pregunta.
- El backend debe poder responder de forma controlada.
- La arquitectura debe quedar preparada para integrar RAG en siguientes semanas.
- Debe existir una asignatura piloto y una estructura de metadatos reutilizable.