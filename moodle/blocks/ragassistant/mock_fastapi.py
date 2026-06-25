"""
mock_fastapi.py
Servidor FastAPI de prueba para block_ragassistant.

Replica el CONTRATO CANÓNICO de la API RAG (POST /ask):
  - Request:  { "question": str, "context": { course_id, role, ... } }  (extra="forbid")
  - Response: { status, answer, sources[], metadata{}, warnings[], latency_ms, request_id }

NO implementa RAG real: solo simula respuestas para probar el bloque Moodle
de extremo a extremo sin levantar Ollama/ChromaDB.

Uso:
    pip install fastapi uvicorn
    python mock_fastapi.py
    # Escucha en http://0.0.0.0:8000  (host.docker.internal:8000 desde Moodle/Docker)
"""

from __future__ import annotations

import time
import uuid
from typing import Optional

from fastapi import FastAPI, Header
from pydantic import BaseModel, ConfigDict, Field

app = FastAPI(title="RAG Assistant Mock", version="2.0.0")

_ALLOWED_ROLES = {"student", "teacher", "editingteacher", "guest", "admin"}


# ── Modelos de entrada (espejo del contrato canónico estricto) ───────────────

class QueryContextPayload(BaseModel):
    model_config = ConfigDict(extra="forbid")

    course_id: str = Field(..., min_length=1)
    section_id: Optional[str] = None
    activity_id: Optional[str] = None
    resource_id: Optional[str] = None
    role: str = Field(..., min_length=1)
    language: Optional[str] = None
    visibility: Optional[str] = None


class AskRequest(BaseModel):
    model_config = ConfigDict(extra="forbid")

    question: str = Field(..., min_length=1)
    context: QueryContextPayload


# ── Endpoints ────────────────────────────────────────────────────────────────

@app.post("/ask")
async def ask(body: AskRequest, authorization: Optional[str] = Header(None)):
    """Simula una respuesta RAG conforme al contrato AskResponse."""
    start = time.time()
    request_id = uuid.uuid4().hex
    course_id = body.context.course_id

    # Abstención simulada para preguntas demasiado cortas.
    if len(body.question) < 5:
        return {
            "status": "abstained",
            "answer": (
                "No he encontrado evidencia suficiente en los materiales "
                "disponibles del curso para responder con fiabilidad."
            ),
            "sources": [],
            "metadata": {
                "abstained": True,
                "abstention_reason": "low_score",
                "best_score": 0.18,
                "citation_verified": True,
                "citation_issues": [],
            },
            "warnings": ["No relevant chunks found"],
            "latency_ms": int((time.time() - start) * 1000),
            "request_id": request_id,
        }

    return {
        "status": "answered",
        "answer": (
            f"[MOCK] Respuesta simulada para el curso {course_id}:\n\n"
            f"Según los materiales disponibles, '{body.question}' tiene relación "
            f"con el Tema 3 y la Guía Docente. Servidor de prueba: conecta la URL "
            f"real de FastAPI en la configuración del bloque."
        ),
        "sources": [
            {
                "chunk_id": "tema3-0012",
                "source_uri": "http://host.docker.internal:8000/mod/resource/view.php?id=1",
                "document_title": "Tema_3.pdf",
                "section": "Tema 3",
                "page": 12,
            },
            {
                "chunk_id": "guia-0003",
                "source_uri": "course://guia-docente",
                "document_title": "Guia_Docente.pdf",
                "section": None,
                "page": 3,
            },
        ],
        "metadata": {
            "abstained": False,
            "abstention_reason": None,
            "best_score": 0.82,
            "citation_verified": True,
            "citation_issues": [],
        },
        "warnings": [],
        "latency_ms": int((time.time() - start) * 1000) + 120,
        "request_id": request_id,
    }


@app.get("/health")
async def health():
    return {
        "status": "ok",
        "service": "rag-assistant-mock",
        "version": "2.0.0",
        "environment": "mock",
    }


if __name__ == "__main__":
    import uvicorn

    uvicorn.run(app, host="0.0.0.0", port=8000)
