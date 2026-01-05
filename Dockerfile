FROM php:8.2-alpine

WORKDIR /app

COPY . .

CMD ["php", "colle.php"]