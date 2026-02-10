# inf1005-project

# For desolatedstorm
after db creation & test data is created and tested, export the db file
1. Go to `Server -> Data Export`
2. Select the correct schema
3. Under Export options select `Dump Structure only`
4. Export to `001-schema.sql`
Then,
repeat steps 1 & 2
3. select `Dump Data` for export options
4. Export to `002-seed-dev.sql`
5. add boths files in the project `db/init/` folder

---

# SETUP INSTRUCTIONS
1. Pull from staging (or clone if you haven't)
2. Run `cp .env.example .env`
3. Update `.env` with your own values (these are local credentials)
4. If containers are still running, stop and remove volumes:
   `docker compose down -v` OR `use the GUI`
5. Start containers again:
   `docker compose up -d` OR `use the GUI`

---


