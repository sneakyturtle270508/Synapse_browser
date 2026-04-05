# Synapse Browser - Hugging Face Spaces
# Serves the search UI with proxy to public Searx

FROM python:3.11-slim

WORKDIR /app

# Install dependencies
RUN pip install --no-cache-dir fastapi uvicorn httpx jinja2

# Copy application files
COPY app.py /app/
COPY index.html /app/
COPY api.php /app/
COPY logo.png /app/ 2>/dev/null || true

EXPOSE 7860

CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "7860"]
