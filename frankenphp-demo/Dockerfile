FROM dunglas/frankenphp

COPY . /app
WORKDIR /app

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/app/Caddyfile", "--adapter", "caddyfile"]
