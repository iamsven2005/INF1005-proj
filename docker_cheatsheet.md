
# 🐳 Docker Cheatsheet

### 🔹 Docker Compose
| Command | Description |
|----------|-------------|
| `docker compose up -d` | Start in background mode |
| `docker compose down` | Stop and remove containers |
| `docker compose down -v` | Remove containers and volumes |
| `docker compose logs -f` | View live logs |
| `docker compose restart` | Restart all services |
| `docker compose exec <service> bash` | Open a shell inside a service |

---

### 🧠 Tips
- Use `docker compose` instead of `docker-compose` (newer syntax).
- To rebuild containers: `docker compose up -d --build`
- To view container resource usage: `docker stats`
