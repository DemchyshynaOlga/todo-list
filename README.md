# Laravel 9 + PHP 8.3 + MySQL Docker Setup


```bash
docker-compose up -d --build
```

Контейнери автоматично:
- Зберуть npm assets
- Виконають міграції бази даних
- Запустять всі сервіси

## Доступ до сервісів

| Сервіс | URL |
|--------|-----|
| Laravel App | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |
| MySQL | localhost:3306 |

**Дані для входу:**
- Email: `admin@test.com`
- Password: `password`
