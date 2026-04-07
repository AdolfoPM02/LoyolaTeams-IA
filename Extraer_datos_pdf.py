import fitz # Esta es la biblioteca PyMuPDF, que se utiliza para trabajar con archivos PDF
import os
from langchain_text_splitters import RecursiveCharacterTextSplitter
import json

archivos_pdf = [
    r"C:\Users\USUARIO\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\GRUPOS_PROYECTO_SOFTWARE_COMUNICACIONES.pdf",
    r"C:\Users\USUARIO\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\GUIA_DOCENTE_SOFTWARE_COMUNICACIONES.pdf",
    r"C:\Users\USUARIO\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\GRUPOS_PROYECTO_SOFTWARE_COMUNICACIONES.pdf",
    r"C:\Users\USUARIO\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\TEMA1_SOFTWARE_COMUNICACIONES.pdf",
    r"C:\Users\USUARIO\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\TEMA2_SOFTWARE_COMUNICACIONES.pdf",
    r"C:\Users\USUARIO\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\TEMA3_SOFTWARE_COMUNICACIONES.pdf",
]

#Configuro el troceador (Chunking)
text_splitter = RecursiveCharacterTextSplitter(chunk_size=1000, chunk_overlap=200, length_function=len, is_separator_regex=False)
#Chunck size indica el tamaño de cada trozo en caracteres
#Chunck overlap indica la cantidad de caracteres que se van solapando

def extraccion_documentos_pdf(lista_archivos):
    documentos_procesados = []

    for ruta_pdf in lista_archivos:
        print(f"Procesando: {ruta_pdf}")
        try:
            documento = fitz.open(ruta_pdf)
            texto_completo = ""

            for num_pagina in range(len(documento)):
                pagina = documento.load_page(num_pagina)
                texto_completo += pagina.get_text("text")

            print(f"Extraídos {len(texto_completo)} caracteres correctamente.\n")

            nombre_archivo = os.path.basename(ruta_pdf)

            trozos = text_splitter.split_text(texto_completo)

            for i, trozo in enumerate(trozos):
                documento_final = {
                    "page_content": trozo,
                    "metadata": {
                        "course_id": "software_comunicaciones",
                        "source": nombre_archivo,
                        "chunk_id" : i,
                        "rol": "ambos" #Por defecto lo vamos a poner para alumnos y profesores
                    }
                }
                documentos_procesados.append(documento_final)
            print(f"Archivo troceado en {len(trozos)} trozos.\n")

        except Exception as e:
            print(f"Error al procesar {ruta_pdf}: {e}\n")

    return documentos_procesados

mis_datos = extraccion_documentos_pdf(archivos_pdf)

nombre_archivo_salida = "chunks_software_comunicaciones.json"

with open(nombre_archivo_salida, "w", encoding="utf-8") as archivo_salida:
    json.dump(mis_datos, archivo_salida, ensure_ascii=False, indent=4)

print(f"Datos troceados guardados en {nombre_archivo_salida}")

if mis_datos:
    print("-" * 50)
    print("MUESTRA DEL PRIMER TROZO PROCESADO (CHUNK 0):")
    print("METADATOS:", mis_datos[0]["metadata"])
    print("CONTENIDO (primeros 200 caracteres):")
    print(mis_datos[0]["page_content"][:200] + "...")
    print("-" * 50)
    print("MUESTRA DEL SEGUNDO TROZO PROCESADO (CHUNK 1):")
    print("METADATOS:", mis_datos[1]["metadata"])
    print("CONTENIDO (primeros 200 caracteres):")
    print(mis_datos[1]["page_content"][:200] + "...")