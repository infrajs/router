# Роутер Infrajs
Включает:
- Работа коротких адресов с символами **-~!** для сторонних вендоров - [infrajs/config-search](https://github.com/infrajs/config-search)
- Автоматическую инсталяцию расширений - [infrajs/update](https://github.com/infrajs/update)
- Конфиг .infra.json расширений автоматически загружается и выполняется при обращени к любому классу расширения - [infrajs/config](https://github.com/infrajs/config)
- простейшие уровни доступа test debug admin - [infrajs/access](https://github.com/infrajs/access)
- HTTP-заголовки по умолчанию от расширений - [infrajs/nostore](https://github.com/infrajs/nostore), [infrajs/access](https://github.com/infrajs/access)
- контроллер слоёв index.json - [infrajs/controller](https://github.com/infrajs/controller)

## Использовние
Рабочей папкой php скриптов должен быть корень проекта. Это важное требование для совместимости [infrajs/path](https://github.com/infrajs/path). Изменить рабочую папку очень просто с помощью станадртной php функции [chdir](http://php.net/manual/function.chdir.php)
```php
use infrajs\router\Router;
use infrajs\path\Path;
if (!is_file('vendor/autoload.php')) {
	chdir('../../../'); //Путь до корня проекта с папкой vendor/
	require_once('vendor/autoload.php');	
	Router::init();
}
$src = Path::theme('-plugin/test.php'); //vendor/name/plugin/test.php
```
Если настроен .htaccess
```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ vendor/infrajs/router/index.php [L,QSA]
```
Сокращения будут работать в адресной строке  **-test/test.php**. из скрипта можно убрать ```Router::init()```, ```chdir()``` и ```vendor/autoload.php```, они уже будут выполнены в ```vendor/infrajs/router/index.php```, указанному в ```.htaccess```.
```php
use infrajs\path\Path;

$src = Path::theme('-test/test.php'); //vendor/test/test.php
```
Желательно оставлять возможность прямого обращения к файлам, без сокращений:
```php
if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}

```

или самый универальный вариант

```php
if (!is_file('vendor/autoload.php')) chdir('../');
require_once('vendor/autoload.php');
Router::init();
```


## Описание
Роутер сделан максимально производительным, в нём несколько мгновеных инициализаций и передача управления вызванному файлу.
infrajs/router представляет собой базовую зависимость для всех новых расширений. Позволяет интегрировать стороннее расширение в среду infrajs. Используется в расширениях
 - [oduvanio/teremok](https://github.com/oduvanio/teremok)
 - [infrajs/imager](https://github.com/infrajs/imager)
 - [angelcharly/weather](https://github.com/ange187/weather)

## Перенаправить запросы на свой php файлы
С точки зрения роутера все запросы, для которых не найдено файла, являются 404 ошибкой. По этому нужно определить 404 страницу, как php файл, на который и будут приходить все запросы.
Нужно создать файл **.infra.json** в корне проекта.

```json
{
	"router":{
		"404":"index.php"
	}
}
```

## .htaccess только для сокращённых адресов
```
RewriteEngine on
RewriteCond %{REQUEST_URI} ^/[-~\!]
RewriteRule ^(.*)$ vendor/infrajs/router/index.php [L,QSA]
```
