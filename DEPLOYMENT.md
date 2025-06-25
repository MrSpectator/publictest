# Deployment Guide

## Local Development

1. Build and start containers:
   ```
   docker-compose up --build
   ```

2. Access the app at [http://localhost:8080](http://localhost:8080)

## Deploying to Render

1. Push your code to a Git repository.
2. Connect the repo to Render and select the `render.yaml` file.
3. Set environment variables as needed in the Render dashboard.
4. Deploy!

## Notes

- All API endpoints are public.
- Swagger docs are served at `/swagger-docs/`.
- CORS is enabled for Swagger docs.