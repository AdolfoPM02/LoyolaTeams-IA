# Arquitectura mínima de la PoC

## Objetivo
Construir una PoC de asistente docente con IA integrado en Moodle, con personalización por usuario, curso y rol.

## Flujo previsto
1. Moodle o la interfaz de chat envía:
   - user_id
   - course_id
   - role
   - question

2. El backend FastAPI recibe la petición en `POST /chat`.

3. En la versión actual (semana 1), el backend devuelve una respuesta dummy.

4. En siguientes semanas, el backend:
   - consultará la base vectorial
   - recuperará contexto del curso
   - construirá el prompt
   - llamará al LLM
   - devolverá respuesta con fuentes

## Componentes
- `frontend/`: interfaz de chat
- `backend/`: API y orquestación
- `rag/`: ingesta, chunking, embeddings y recuperación
- `infra/`: infraestructura y despliegue local
- `docs/`: documentación técnica

## Alcance actual
- backend mínimo operativo
- endpoint `/health`
- endpoint `/chat` dummy
- entorno Docker funcional