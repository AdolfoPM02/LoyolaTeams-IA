# Backlog inicial PoC LoyolaTeams-IA

## Prioridad alta
1. Definir contrato del endpoint `/chat`
2. Implementar `POST /chat` dummy en backend
3. Investigar cómo obtener `user_id`, `course_id` y rol desde Moodle
4. Crear interfaz mínima de chat conectada al backend
5. Probar extracción de texto desde PDFs
6. Diseñar metadatos para RAG:
   - course_id
   - role
   - source
   - chunk_id

## Prioridad media
7. Definir estrategia de chunking
8. Elegir base vectorial
9. Preparar pipeline de embeddings
10. Diseñar prompts base para alumno y profesor