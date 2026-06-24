"""
mock_fastapi.py
Servidor FastAPI de prueba para block_ragassistant.
Simula respuestas del backend RAG real.

Uso:
    pip install fastapi uvicorn
    python mock_fastapi.py
    Escucha en http://localhost:8000
"""

from fastapi import FastAPI, Header, HTTPException
from pydantic import BaseModel
from typing import Optional, List
import time
import uuid

app = FastAPI(title="RAG Assistant Mock", version="1.0.0")

# ── Modelos de entrada ──────────────────────────────────────────────────────

class AskRequest(BaseModel):
    question: str
    course_id: int
    user_id: int
    context_id: Optional[int] = None
    cm_id: Optional[int] = None
    section_id: Optional[int] = None
    roles: List[str] = []
    lang: str = "es"
    top_k: int = 5

class IndexRequest(BaseModel):
    course_id: int
    requested_by: int
    mode: str = "incremental"
    force_reindex: bool = False

# ── Endpoints ───────────────────────────────────────────────────────────────

@app.post("/ask")
async def ask(body: AskRequest, authorization: Optional[str] = Header(None)):
    """Simula una respuesta RAG con fuentes."""
    start = time.time()

    # Simula contexto insuficiente si la pregunta es muy corta
    if len(body.question) < 5:
        return {
            "answer": "No he encontrado información suficiente en los materiales del curso para responder con seguridad.",
            "sources": [],
            "status": "insufficient_context",
            "confidence": 0.18,
            "latency_ms": int((time.time() - start) * 1000),
            "warnings": ["No relevant chunks found"]
        }

    return {
        "answer": f"[MOCK] Respuesta simulada para el curso {body.course_id}:\n\n"
                  f"Según los materiales disponibles, '{body.question}' tiene relación con "
                  f"el Tema 3 (páginas 12-15) y la Guía Docente. Este es un servidor de "
                  f"prueba — conecta la URL real de tu compañero en la configuración del bloque.",
        "sources": [
            {
                "title": "Tema_3.pdf",
                "course_id": body.course_id,
                "cm_id": body.cm_id or 0,
                "page": 12,
                "score": 0.87,
                "url": f"http://localhost:8000/mod/resource/view.php?id={body.cm_id or 1}"
            },
            {
                "title": "Guia_Docente.pdf",
                "course_id": body.course_id,
                "cm_id": None,
                "page": 3,
                "score": 0.74,
                "url": None
            }
        ],
        "status": "ok",
        "confidence": 0.82,
        "latency_ms": int((time.time() - start) * 1000) + 120,
        "warnings": []
    }


@app.post("/index-course")
async def index_course(body: IndexRequest, authorization: Optional[str] = Header(None)):
    """Simula el inicio de un job de indexación."""
    job_id = str(uuid.uuid4())[:8]
    return {
        "job_id": job_id,
        "status": "queued",
        "message": f"Indexación {'completa' if body.force_reindex else 'incremental'} "
                   f"del curso {body.course_id} en cola. Job: {job_id}"
    }


@app.get("/health")
async def health():
    return {"status": "ok", "service": "RAG Assistant Mock"}


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
