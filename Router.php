<?php
namespace infrajs\router;
use infrajs\update\Update;
use infrajs\access\Access;
use infrajs\nostore\Nostore;
use infrajs\controller\Controller;
use infrajs\path\Path;
use infrajs\once\Once;
use infrajs\env\Env;
use akiyatkin\error\Error;
use infrajs\config\Config;

class Router {
	static public $main = false; //Метка что содержание страницы для пользователя
	static public $conf = array(
		"404" => "-router/404.php"
	);
	static public function init($main = false) //true если файл не найден
	{
		Once::func( function () use ($main) {
			//Роутре работает в двух режимах
			$query = substr(urldecode($_SERVER['REQUEST_URI']), 1);
			$ch = substr($query,0,1);
			Router::$main = $main && (!$query || !in_array($ch, ['~', '!', '-']));

			//Список операций выполняющихся при любом запросе со спецсимволом в адресе [-~!] и при запросах без файлов
			//или при яном вызове в скрипте Router::init();
			
			//Собирается конфиг .infra.json из корня проекта
			//Теперь при первом обащении к классу расширения будет собираться его конфиг .infra.json из папки расширения
			Config::init();
			Config::get('router');
			//Показываем и скрываем ошибки в зависимости от режима


			Error::init();

			//Заголовки по умолчанию для Cache-Controll
			Nostore::init(Router::$main);
			if (Router::$main) {

				Config::get(); //Нужно собрать все расширения, чтобы выполнились все подписки
				
				//Установка системы до обращения к Окружению
				Update::check();
				Access::modified(Env::name()); 
				
				if (Env::get('nostore')) {
					//У Nostore кривое API хрен поймёшь, как этим Cache-control управлять.
					Nostore::on();
				} else if (Env::$defined && !Nostore::is()) { //Ключ что окружение изменено пользователем
					Nostore::offPrivate();
				}
				
			} else {
				//По дате авторизации админа выход и если браузер прислал информацию что у него есть кэш
				//Заголовок Cache-control:no-store в расширении Nostore::on() запретит создавать кэш, если станет ясно, что modfeied не нужен	
				Update::check();
				Access::modified(); 
			}
			//Вспомогательные заголовки с информацией о правах пользователя test debug admin
			Access::headers();
		});
	}
	static public function apply()
	{
		//Поиск совпадения адреса с файлом
		//Редирект также кэшируется в modified, когда обращение к статике, по правилам Nostore
		$r = Path::init();
		if ($r) return;

		//Контроллер... должен быть файл в корне index.json
		//Если сайт не использует контроллер то до этого места доходим только, когда 404 и лишний запуск не существенен
		//Либо следующая строчка обеспечивает формирование всего html если контроллер используется.
		$r = false;
		if (Router::$main) $r = Controller::init();
		if (!$r) {
			$conf = Config::get('router');
			Path::req($conf['404']);
		}
	}
}
