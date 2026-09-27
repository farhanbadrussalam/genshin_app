# ── Stage 1: Builder ──────────────────────────────────────────────────────────
FROM python:3.12-slim AS builder

WORKDIR /app

# Install dependensi build untuk paket native (jika diperlukan)
RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc \
    && rm -rf /var/lib/apt/lists/*

# Copy dan install requirements ke direktori lokal user
COPY requirements.txt .
RUN pip install --user --no-cache-dir -r requirements.txt


# ── Stage 2: Runtime (image final lebih kecil) ───────────────────────────────
FROM python:3.12-slim AS runtime

# Metadata
LABEL maintainer="genshin-app"
LABEL description="HoYoLAB Microservice — FastAPI + genshin.py"

WORKDIR /app

# Salin dependensi dari builder stage
COPY --from=builder /root/.local /root/.local

# Pastikan script lokal user bisa dieksekusi
ENV PATH=/root/.local/bin:$PATH

# Salin kode aplikasi
COPY . .

# Port yang akan diekspose
EXPOSE 8001

# Variabel environment default (bisa di-override via docker-compose atau CLI)
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1
ENV HOST=0.0.0.0
ENV PORT=8001
ENV WORKERS=2

# Perintah menjalankan Uvicorn dengan multiple workers
CMD ["sh", "-c", "uvicorn main:app --host $HOST --port $PORT --workers $WORKERS"]
