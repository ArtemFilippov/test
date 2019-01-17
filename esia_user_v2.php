<?php
use \Bitrix\Main\UserTable;
use \Bitrix\Main\Entity\Query;

class EsiaUserForBx
{
	/**
	 * Контейнер для библиотеки esia.
	 * @var object
	 */
	protected $libesia;

	/**
	 * Конфиг библиотеки esia.
	 * @var array
	 */
	protected $libconfig = [];

	/**
	 * Массив данных пользователя Битрикс.
	 * @var array
	 */
	protected $bx_user = [];

	/**
	 * ID пользователя Битрикс.
	 * @var integer
	 */
	protected $bx_user_id = 0;

	/**
	 * Массив полей пользователя для получения методом Битрикс GetList. 
	 * @var [type]
	 */
	protected $bx_user_fields = ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'];
	
	/**
	 * Массив свойств пользователя для получения методом Битрикс GetList. 
	 * @var array
	 */
	protected $bx_user_select = [];

	/**
	 * Массив обновленных данных пользователя.
	 * @var array
	 */
	protected $updates = [];

	/**
	 * Массив ошибок.
	 * @var array
	 */
	public $errors = [];



	/**
	 * Альтернативный конструктор. 
	 * Позволяет создать экземпляр класса с
	 * произвольным конфигом для библиотеки esia.
	 *  
	 * @param  array   $config  [ 'key' => 'value', ... ]
	 * @param  boolean $replace Если true, полностью перезапишет конфиг по умолчанию
	 * @return EsiaUser
	 */
	static public function run(array $config = [], $replace = false)
	{
		$esia_user = new static();
		if (!empty($config)) {
			if ($replace) $esia_user->importConfig($config);
			else {
				foreach ($config as $key => $value) {
					$esia_user->setConfigItem($key, $value);
				}
			}
		}

		$esia_user->runEsia();

		return $esia_user;
	}

	/**
	 * Альтернативный конструктор. 
	 * Создает объект с перенаправлением на переданную страницу.
	 * 
	 * @param  string $url Абсолютный адрес страницы
	 * @return EsiaUser
	 */
	static public function runToUrl($url)
	{
		return self::run(['redirectUrl' => $url]);
	}

	/**
	 * Запускает работу библиотеки esia
	 * 
	 * @return void
	 */
	public function runEsia()
	{
		$this->libesia = new \esia\OpenId($this->libconfig);
	}


	/**
	 * Заменяет конфиг библиотеки esia по умолчанию
	 * на переданный. 
	 * 
	 * @param  array  $config
	 * @return void
	 */
	public function importConfig(array $config)
	{
		$this->libconfig = $config;
	}

	/**
	 * Устанавливает переданное значение в ключ конфига библиотеки esia.
	 * 
	 * @param string $key   
	 * @param string $value 
	 */
	public function setConfigItem($key, $value)
	{
		$this->libconfig[$key] = $value;
	}


	/**
	 * Формирует токен из GET, полученного от ЕСИА, либо из переданной строки.
	 *  
	 * @param  string $token 
	 * @return string|false
	 */
	public function getResponse($token = '')
	{
		$token = ($token) ? $token : $_GET['code'];

		if (!empty($token)) {

			return $this->libesia->getToken($token);

		}
		else return false;
	}


	/**
	 * Возвращает url для авторизации через ЕСИА.
	 * 
	 * @return string
	 */
	public function getAuthUrl()
	{
		return $this->libesia->getUrl();
	}

	/**
	 * Возвращает html-код ссылки для авторизации через ЕСИА.
	 * 
	 * @return string
	 */
	public function getAuthLink()
	{
		return '<a href="'.$this->libesia->getUrl().'">Войти через портал Госуслуги</a>';
	}


	/**
	 * Возвращает фильтр для выборки пользователей из системы.
	 * Расширяется кастомным методом modifyBxUserFilter($filter).
	 * 
	 * @return array
	 */
	public function getBxUserFilter()
	{
		$default_filter = [ 'EMAIL' => $this->getEmail() ];

		if (method_exists($this, 'modifyBxUserFilter')) return $this->modifyBxUserFilter($default_filter);

		else return $default_filter;
	}


	/**
	 * Проверяет наличие пользователя в системе.
	 * Если пользователь найден, сохраняет в свойствах объекта
	 * его данные.
	 * 
	 * @return boolean
	 */
	public function isset()
	{
		$user = $this->select();
		if ($user) {
			$this->bx_user = $user;
			$this->bx_user_id = $user['ID'];
			return true;
		}
		else return false;
	}


	/**
	 * Возвращает данные пользователя из системы.
	 * 
	 * @return array
	 */
	public function select()
	{
		/*$by = array("NAME" => "ASC");
		$order = '';
		$filter = $this->getBxUserFilter();
		$select = [  
			'FIELDS' => $this->bx_user_fields, 
			'SELECT' => $this->bx_user_select, 
			'NAV_PARAMS' => [ 'nTopCount' => '1' ] 
		];*/

		$query = new Query(UserTable::getEntity());
		$query = $query
			->setSelect($this->bx_user_fields)
			->where(
				Query::filter()
					->logic('or')
					->where(
						[
							['XML_ID', 'esia'.$this->libesia->oid],
							['EMAIL', ($this->getEmail()) ? $this->getEmail() : ""]
						])
				);

		
		$result = $query->exec()->fetch();

		//print_r($filter);
		// print_r($this->bx_user_fields);
		// print_r($this->bx_user_select);
		// print_r($filter);
		// die();
		//$rs = \CUser::GetList($by, $order, $filter, $select);
		
		//$user = $rs->Fetch();
		// print_r($result);
		// die();
		// return $user;
		return $result;
	}


	/**
	 * Регистрирует пользователя в системе.
	 *
	 * @return boolean
	 */
	public function add()
	{
		$user = new \CUser;

		$data = $this->composeDataForBx();
		
		$id = $user->Add($data);
		if (intval($id) > 0) {
			$this->bx_user_id = $id;
			return true;
		}
		else {
			$this->errors[] = $user->LAST_ERROR;
			return false;
		} 
	}


	/**
	 * Авторизует пользователя в системе.
	 * 
	 * @return boolean
	 */
	public function auth()
	{
		if ($this->bx_user_id > 0) {
			$user = new CUser;
			if ($user->Authorize($this->bx_user_id)){
				// print_r($this->bx_user_id);
				// die();
				return true;
			} 
			else {
				$this->errors[] = 'Не удалось авторизовать пользователя';
				return false;
			}
		}
		else {
			$this->errors[] = 'ID пользователя не получен';
			return false;
		}
	}


	/**
	 * Обновляет данные пользователя в системе.
	 * 
	 * @return boolean
	 */
	public function update()
	{
		if (!empty($this->updates)) {
			$user = new CUser;

			if ($user->update($this->bx_user_id, $this->updates)) {
				$this->updates = [];
				$this->bx_user = $this->select();
				return true;
			}
			else {
				$this->errors[] = $user->LAST_ERROR;
				return false;
			} 
		}
		else {
			$this->errors[] = 'Обновление не удалось: нечего обновлять';
			return false;
		} 
	}


	/**
	 * Проверяет наличие несовпадений в данных из ЕСИА и системы,
	 * фармирует массив обновлений.
	 * 
	 * @return boolean
	 */
	public function checkUpdates()
	{
		if (empty($this->bx_user)) $this->bx_user = $this->select();

		if (!empty($this->bx_user)) {
			$data = $this->composeDataForBx2();
			$updates = [];
			foreach ($this->bx_user as $key => $value) {
				if (array_key_exists($key, $data)) {
					if ($data[$key] != $value) {
						$updates[$key] = $data[$key];
					}
				}
			}
			if (count($updates) > 0) {
				$this->updates = $updates;
				return true;
			}
			else {
				$this->updates = [];
				return false;
			} 
		}
		else {
			$this->errors[] = 'Не удалось проверить обновления: не получены данные о пользователе из системы';
			return false;
		}
	}


	/**
	 * Собирает данные из ответа ЕСИА и транслирует их в формат Битрикса.
	 * Расширяется кастомным методом modifyDataForBx($data).
	 * 
	 * @return array
	 */
	public function composeDataForBx()
	{
		$data = [
			'NAME' => $this->getName(),
			'LAST_NAME' => $this->getLastName(),
			'SECOND_NAME' => $this->getSecondName(),
			'EMAIL' => $this->getEmail(),
			'LOGIN' => $this->getEmail(),
			'PASSWORD' => $this->createPassword($this->getEmail()),
		];
		if (method_exists($this, 'modifyDataForBx')) {
			return $this->modifyDataForBx($data);
		}
		else return $data;
	}

	public function composeDataForBx2()
	{
		$data = [
			'NAME' => $this->getName(),
			'LAST_NAME' => $this->getLastName(),
			'SECOND_NAME' => $this->getSecondName(),
			'EMAIL' => $this->getEmail(),
			
		];
		if (method_exists($this, 'modifyDataForBx')) {
			return $this->modifyDataForBx($data);
		}
		else return $data;
	}


	/**
	 * Возвращает email.
	 * 
	 * @return string
	 */
	public function getEmail()
	{
		$email = $this->libesia->getContactInfo();
		//echo "<pre>";var_dump($this->libesia->getContactInfo());die();
		return $email[0]->value; 
	}
	
	/**
	 * Возвращает имя.
	 * @return string
	 */
	public function getName()
	{
		return $this->libesia->getPersonInfo()->firstName;
	}
	
	/**
	 * Возвращает фамилию.
	 * @return string
	 */
	public function getLastName()
	{
		return $this->libesia->getPersonInfo()->lastName;
	}
	
	/**
	 * Возвращает отчество.
	 * @return string
	 */
	public function getSecondName()
	{
		return $this->libesia->getPersonInfo()->middleName;
	}

	/**
	 * Создает пароль.
	 * @return string
	 */
	public function createPassword($str)
	{
		return md5($str.time());
	}

	/**
	 * Возвращает данные пользователя, полученные из системы.
	 * @return array
	 */
	public function getBxUser()
	{
		return $this->bx_user;
	}
	public function getUserId()
	{
		return $this->bx_user_id;
	}
}
?>