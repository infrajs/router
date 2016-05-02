<?php

chdir('../../../');
require_once('vendor/autoload.php');

//Список операций выполняющихся при любом запросе со спецсимволом в адресе [-~!] и при запросах без файлов
//DirectoryIndex не срабатывает и обращение к папкам вызывает index.php теперь с выполнением указанных ниже функций
//Если систем имеет свою систему прав, кэшироваия и тп, стандартный index.php должен запускаться без роутера. 
//Нужно точней настроить .htaccess


//Создание папки cache если её нет и тп.
infrajs\update\Update::init();

//По дате авторизации админа выход и если браузер прислал информацию что у него есть кэш
//Заголовок Cache-control:no-store в расширении Nostore::on() запретит создавать кэш, если станет ясно, что modfeied не нужен
infrajs\access\Access::modified(); 

//Заголовки по умолчанию для Cache-Controll
infrajs\nostore\Nostore::init();

//Вспомогательные заголовки с информацией о правах пользователя test debug admin
infrajs\access\Access::headers();

//Заполняем config.path.search путями до установленных расширений
infrajs\config\search\Search::init();
//Поиск совпадения адреса с файлом
//Редирект также кэшируется в modified, когда обращение к статике
infrajs\path\Path::init();



//Контроллер... должен быть файл в корне index.json
//Если сайт не использует контроллер то до этого места доходим только, когда 404 и лишний запуск не существенен
//Либо следующая строчка обеспечивает формирование всего html если контроллер используется.

$query = substr(urldecode($_SERVER['REQUEST_URI']), 1);
if (!in_array($query{0}, ['~', '!', '-'])) {
	infrajs\controller\Controller::init();
}

$conf = infrajs\config\Config::get('router');
infrajs\path\Path::req($conf['404']);
