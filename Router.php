<?php
namespace infrajs\router;
use infrajs\update\Update;
use infrajs\access\Access;
use infrajs\nostore\Nostore;
use infrajs\controller\Controller;
use infrajs\config\search\Search;
use infrajs\path\Path;
use infrajs\once\Once;
use infrajs\config\Config;

class Router {
	static public $conf = array(
		"404" => "-router/404.php"
	);
	static public function init()
	{
		Once::exec(__FILE__, function () {

			//Список операций выполняющихся при любом запросе со спецсимволом в адресе [-~!] и при запросах без файлов
			//или при яном вызове в скрипте Router::init();
			
			//Собирается конфиг .infra.json из корня проекта
			//Теперь при первом обащении к классу расширения будет собираться его конфиг .infra.json из папки расширения
			Config::init();
			

			//Анализируется папка vendor Находятся все производители поддерживающие конфигурационные файлы .infra.json
			//Некий производитель angelcharly попадёт в список поиска, если у него есть библиотека с файлом .infra.json
			//Эту обработку можно убрать если производители прописаны вручную в config.path.search проекта
			//Без этой обработке, например, переопределения в кореновм .infra.json для расширения weather
			//не применятся к Weather::$conf и неinfrajs расширения будет работать со значениями по умолчанию
			//.infra.json в самих неinfrajs расширениях также не будет прочитан,
			//но значения конфига по умолчанию и так указаны в переменной класса, вроде Weather::$conf по этому не скажется на работе
			//В общем заполняем config.path.search путями до установленных расширений
			Search::init();

			
			//Автоматическая установка расширений
			//Cоздаются папка cache и для расширения mem создаётся папка cache/mem, если их нет
			//Наличие этих папок, например, обязательно для Search, который кэширует свою работу
			//Во время обновления запускаются тесты
			Update::init();

			

			//По дате авторизации админа выход и если браузер прислал информацию что у него есть кэш
			//Заголовок Cache-control:no-store в расширении Nostore::on() запретит создавать кэш, если станет ясно, что modfeied не нужен
			Access::modified(); 

			//Заголовки по умолчанию для Cache-Controll
			Nostore::init();

			//Вспомогательные заголовки с информацией о правах пользователя test debug admin
			Access::headers();

			
		});
	}
	static public function apply()
	{
		//Поиск совпадения адреса с файлом
		//Редирект также кэшируется в modified, когда обращение к статике, по правилам Nostore
		Path::init();

		//Контроллер... должен быть файл в корне index.json
		//Если сайт не использует контроллер то до этого места доходим только, когда 404 и лишний запуск не существенен
		//Либо следующая строчка обеспечивает формирование всего html если контроллер используется.

		$query = substr(urldecode($_SERVER['REQUEST_URI']), 1);
		$ch = substr($query,0,1);
		if (!in_array($ch, ['~', '!', '-'])) {
			Controller::init();
		}

		$conf = Config::get('router');
		Path::req($conf['404']);
	}
}