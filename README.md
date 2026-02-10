## LAMP Todo (Docker)

This project runs a basic PHP + Apache + MySQL todo list using Docker Compose.

### Prerequisites (Windows)

- Install Docker Desktop: https://www.docker.com/products/docker-desktop/
- During install, enable WSL 2 integration when prompted.
- Reboot if Docker asks you to.

### How to Run (Windows)

1. Open PowerShell in this repo folder.
2. Build and start the stack:

```bash
docker compose up --build
```

3. Open the app in your browser:

```
http://localhost:8080
```

4. Stop the stack:

```bash
docker compose down
```

### Notes

- The MySQL data is stored in a named volume called `db_data`.
- To reset the database, stop the stack and remove the volume:

```bash
docker compose down -v
```


Feel free to delete, a simple TODO list in php