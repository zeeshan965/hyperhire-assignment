## Installation
Assignment was developed Linux OS and on PHP 8.3.13 and composer 2.8.2 versions.

- sudo apt install php8.3 php8.3-xml php8.3-mbstring php8.3-dev php8.3-mysqli php8.3-swoole
- composer install
- create .env file from .env.example
- add DB credentials 
- php artisan migrate
- php artisan octane:install
  (I used swoole with octane for multi-threading)
- php artisan octane:start
- php artisan octane:status
- open application swagger docs from here http://localhost:8000/api/documentation
- 

