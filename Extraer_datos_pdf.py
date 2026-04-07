import fitz # Esta es la biblioteca PyMuPDF, que se utiliza para trabajar con archivos PDF
import os

archivos_pdf = [
    r"C:\Users\andre\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\NOTAS_SOFTWARE_COMUNICACIONES.pdf",
    r"C:\Users\andre\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\GUIA_DOCENTE_SOFTWARE_COMUNICACIONES.pdf",
    r"C:\Users\andre\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\GRUPOS_PROYECTO_SOFTWARE_COMUNICACIONES.pdf",
    r"C:\Users\andre\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\TEMA1_SOFTWARE_COMUNICACIONES.pdf",
    r"C:\Users\andre\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\TEMA2_SOFTWARE_COMUNICACIONES.pdf",
    r"C:\Users\andre\Desktop\PROYECTO_IA\LoyolaTeams-IA\ARCHIVOS_IA\SOFTWARE_COMUNICACIONES\TEMA3_SOFTWARE_COMUNICACIONES.pdf",
]

def extraccion_documentos_pdf(lista_archivos):
    documentos_procesados = []

    for ruta_pdf in lista_archivos:
        print(f"Procesando: {ruta_pdf}")
        try:
            documento = fitz.open(ruta_pdf)
            texto_completo = ""

            for pagina in range(len(documento)):
                pagina = documento.load_page(pagina)
                texto_completo += pagina.get_text("text")

            nombre_archivo = os.path.basename(ruta_pdf)

            documento_final = {
                "page_content": texto_completo,
                "metadata": {
                    "course_id": "software_comunicaciones",
                    "source": nombre_archivo,
                    "rol": "ambos" #Por defecto lo vamos a poner para alumnos y profesores
                }
            }
            documentos_procesados.append(documento_final)
            print(f"Extraídos {len(texto_completo)} caracteres correctamente.\n")
        
        except Exception as e:
            print(f"Error al procesar {ruta_pdf}: {e}\n")

    return documentos_procesados

mis_datos = extraccion_documentos_pdf(archivos_pdf)

if mis_datos:
    print("-" * 50)
    print("MUESTRA DEL PRIMER DOCUMENTO PROCESADO:")
    print("METADATOS:", mis_datos[0]["metadata"])
    print("CONTENIDO (primeros 200 caracteres):")
    print(mis_datos[0]["page_content"][:200] + "...")