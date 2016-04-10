# Роутер Infrajs для .htaccess
**Disclaimer:** Module is not complete and not ready for use yet.

```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ vendor/infrajs/router/index.php [L,QSA]
```

Используется с другими расширениями [infrajs/controller](https://github.com/infrajs/controller), [infrajs/imager](https://github.com/infrajs/imager)
Необходимо для объединения нескольких расширений. Включает основные компоненты Infrajs.

- [infrajs/config](https://github.com/infrajs/config) - конфигурационные файлы .infra.json
- [infrajs/path](https://github.com/infrajs/path) - короткие адреса,
- [infrajs/controller](https://github.com/infrajs/controller) - контроллер слоёв index.json в корне проекта
- [infrajs/nostore](https://github.com/infrajs/nostore) - управление кэшем браузера
- [infrajs/access](https://github.com/infrajs/access) - простейшие уровни доступа test debug admin
- [infrajs/update](https://github.com/infrajs/update) - установки при первом запуске и при следующих обновлениях


## Перенаправить запросы на index.php
С точки зрения роутера все запросы для которых не найдено файла являются 404 ошибкой. По этому нужно определить 404 страницу как php файл, на который и будут приходить все запросы.
Нужно создать файл .infra.json в корне проекта.
```json
{
	"router":{
		"404":"index.php"
	}
}

```