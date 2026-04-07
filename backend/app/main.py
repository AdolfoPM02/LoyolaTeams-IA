from fastapi import FastAPI
from pydantic import BaseModel
from typing import List

app = FastAPI(title="LoyolaTeams-IA API")

class ChatRequest(BaseModel):
    user_id: int
    course_id: int
    role: str
    question: str

class ChatResponse(BaseModel):
    answer: str
    sources: List[str]
    course_id: int
    role: str

@app.get("/health")
def health():
    return {"status": "ok"}

@app.post("/chat", response_model=ChatResponse)
def chat(request: ChatRequest):
    return ChatResponse(
        answer=f"Respuesta dummy del asistente para el curso {request.course_id}",
        sources=[],
        course_id=request.course_id,
        role=request.role
    )