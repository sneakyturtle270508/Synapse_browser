
<img width="867" height="288" alt="Screenshot_2026-04-02_at_20 39 34-removebg-preview" src="https://github.com/user-attachments/assets/76da60d6-b1cc-4f02-8252-75b2b3fe9714" />

A private search engine powered by Searx, deployed with Docker.

## Features

- Privacy-respecting search (Searx)
- Uses Google, Bing, DuckDuckGo engines
- Docker deployment
- Clean, fast UI

## Quick Start

### 1. Install Docker
```bash
# Ubuntu
curl -fsSL https://get.docker.com | sh

# Mac/Windows
# Download Docker Desktop from https://docker.com
```

### 2. Run
```bash
cd williams-browser
docker-compose up -d
```

### 3. Open
```
http://localhost
```

## Files

```
├── index.html           # Frontend
├── api.php              # PHP API (connects to Searx)
├── docker-compose.yml   # Docker setup
├── searx/
│   └── settings.yml     # Searx configuration
└── README.md
```

## Customization

### Change Searx engines
Edit `searx/settings.yml`:
```yaml
engines:
  - name: google
    disabled: false
  - name: bing
    disabled: false
```

### Set custom base URL
```bash
SEARX_URL=https://your-domain.com docker-compose up -d
```

### HTTPS with Nginx (optional)
Add nginx service in docker-compose.yml for HTTPS support.

## Stop

```bash
docker-compose down
```

## Notes

- Searx runs on port 8080 (API access)
- Frontend runs on port 80
- First start takes ~1 minute for Searx to initialize
