<?php
namespace infrajs\router;
use infrajs\update\Update;
use infrajs\access\Access;
use infrajs\nostore\Nostore;
use infrajs\nostore\Modified;
use infrajs\controller\Controller;
use infrajs\path\Path;
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
		//Once::func( function () use ($main) {
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
			//Nostore::init(Router::$main);
			Nostore::init();
			
			if (Router::$main) Config::get(); //Нужно собрать все расширения, чтобы выполнились все подписки	

			//Установка системы до обращения к Окружению
			Update::check();

			if (Router::$main) {
				
				Env::init(); //Окржение можено изменить только через контроллер, так как применение параметро означает зависимость от них и кэшировать можно только если есть соответствующая првоерка с редиректом

				Modified::etagtime(Env::getName(), Access::adminTime());				
				
				if (Env::get('nostore')) { //К окружение можно обращаться только при работе контроллера, потому что будет редирект если потреуется с неактуального public кэша
					//У Nostore кривое API хрен поймёшь, как этим Cache-control управлять.
					Nostore::on();
				} else if (Env::$defined && !Nostore::is()) { //Ключ что окружение изменено пользователем
					//Если пользователь изменил окружение мы не хотим чтобы данный кэш сохранялся для всех, как дефаулт
					//Это имеет значение только для контроллера
					Nostore::offPrivate();
				}
			} else {
				//По дате авторизации админа выход и если браузер прислал информацию что у него есть кэш
				//Заголовок Cache-control:no-store в расширении Nostore::on() запретит создавать кэш, если станет ясно, что modfeied не нужен	
				Modified::time(Access::adminTime());
			}
		
			
			//Вспомогательные заголовки с информацией о правах пользователя test debug admin
			Access::headers();
			
		//});
	}
	static public $end = false;
	static public function apply()
	{
		//Поиск совпадения адреса с файлом
		//Редирект также кэшируется в modified, когда обращение к статике, по правилам Nostore
		
		$r = Path::init();
		if ($r) {
			Router::$end = true;
			return;
		}

		//Контроллер... должен быть файл в корне index.json
		//Если сайт не использует контроллер то до этого места доходим только, когда 404 и лишний запуск не существенен
		//Либо следующая строчка обеспечивает формирование всего html если контроллер используется.
		$r = false;
		if (Router::$main) $r = Controller::init();
		if (!$r) {
			$conf = Config::get('router');
			Path::req($conf['404']);
		}
		Router::$end = true;
	}
}
