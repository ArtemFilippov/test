<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;

class UserList extends CBitrixComponent
{
	/**
	 * кешируемые ключи arResult
	 * @var array()
	 */
	protected $cacheKeys = array();
	
	/**
	 * дополнительные параметры, от которых должен зависеть кеш
	 * @var array
	 */
	protected $cacheAddon = array();
	
	/**
	 * парамтеры постраничной навигации
	 * @var array
	 */
	protected $navParams = array();
    /**
     * вохвращаемые значения
     * @var mixed
     */
	protected $returned;
    /**
     * тегированный кеш
     * @var mixed
     */
    protected $tagCache;

    /**
	 * подключает языковые файлы
	 */
	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}
	
    /**
     * подготавливает входные параметры
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        $result = array(
         
            'SHOW_NAV' => ($params['SHOW_NAV'] == 'Y' ? 'Y' : 'N'),
            'COUNT' => intval($params['COUNT']),
            'SORT_FIELD1' => strlen($params['SORT_FIELD1']) ? $params['SORT_FIELD1'] : 'ID',
            'SORT_DIRECTION1' => $params['SORT_DIRECTION1'] == 'ASC' ? 'ASC' : 'DESC',
            'SORT_FIELD2' => strlen($params['SORT_FIELD2']) ? $params['SORT_FIELD2'] : 'ID',
            'SORT_DIRECTION2' => $params['SORT_DIRECTION2'] == 'ASC' ? 'ASC' : 'DESC',
            'CACHE_TIME' => intval($params['CACHE_TIME']) > 0 ? intval($params['CACHE_TIME']) : 3600,
			'AJAX' => $params['AJAX'] == 'N' ? 'N' : $_REQUEST['AJAX'] == 'Y' ? 'Y' : 'N',
			'FILTER' => is_array($params['FILTER']) && sizeof($params['FILTER']) ? $params['FILTER'] : array(),
            'CACHE_TAG_OFF' => $params['CACHE_TAG_OFF'] == 'Y'
        );
        return $result;
    }
	
	/**
	 * определяет читать данные из кеша или нет
	 * @return bool
	 */
	protected function readDataFromCache()
	{
		global $USER;
		if ($this->arParams['CACHE_TYPE'] == 'N')
			return false;
		if (is_array($this->cacheAddon))
			$this->cacheAddon[] = $USER->GetUserGroupArray();
		else
			$this->cacheAddon = array($USER->GetUserGroupArray());
		return !($this->startResultCache(false, $this->cacheAddon, md5(serialize($this->arParams))));
	}
	/**
	 * кеширует ключи массива arResult
	 */
	protected function putDataToCache()
	{	
		//print_r($this->cacheKeys);
		if (is_array($this->cacheKeys) && sizeof($this->cacheKeys) > 0)
		{
			
			$this->SetResultCacheKeys($this->arResult["ITEMS"]);
		}
	}
	/**
	 * прерывает кеширование
	 */
	protected function abortDataCache()
	{
		$this->AbortResultCache();
	}
    /**
     * завершает кеширование
     * @return bool
     */
    protected function endCache()
    {
        if ($this->arParams['CACHE_TYPE'] == 'N')
            return false;
        $this->endResultCache();
    }
	
	/**
	 * проверяет подключение необходиимых модулей
	 * @throws LoaderException
	 */
	protected function checkModules()
	{
		if (!Main\Loader::includeModule('main'))
			throw new Main\LoaderException(Loc::getMessage('STANDARD_ELEMENTS_LIST_CLASS_IBLOCK_MODULE_NOT_INSTALLED'));
	}
	
	/**
	 * выполяет действия перед кешированием 
	 */
	protected function executeProlog()
	{
		if ($this->arParams['COUNT'] > 0)
		{
			if ($this->arParams['SHOW_NAV'] == 'Y')
			{
				
				$this->navParams = new \Bitrix\Main\UI\PageNavigation("nav-more-news");
				$this->navParams->allowAllRecords(true)
				   ->setPageSize($this->arParams['COUNT'])
				   ->initFromUri();

			
			}
			else
			{
				$this->navParams = new \Bitrix\Main\UI\PageNavigation("nav-more-news");
				
			}
		}
		else
			$this->navParams = false;
	}

	/**
	 * получение результатов
	 */
	protected function getResult()
	{
		
		$query = new \Bitrix\Main\Entity\Query(\Bitrix\Main\UserTable::getEntity());

		$query->registerRuntimeField(
			/*new Bitrix\Main\Entity\ReferenceField(
		         'GROUPS_ID', 
		         \Bitrix\Main\UserGroupTable::getEntity(),
		         \Bitrix\Main\ORM\Query\Join::on('this.ID', 'ref.USER_ID'),
		        // ->where('ref.GROUP_ID', 6),
		         ['join_type' => "LEFT"]

		            
		      )*/
				"GROUPS_ID", [
			        'data_type' => \Bitrix\Main\UserGroupTable::getEntity(),
			        'reference' => [
			            '=this.ID' => 'ref.USER_ID',
			            
			        ],
			        'join_type' => "LEFT"
			    ]
			);

		if(isset($this->arParams["FILTER"]["GROUPS_ID"]) && array($this->arParams["FILTER"]["GROUPS_ID"])){
			$query->whereIn('GROUPS_ID.GROUP_ID', $this->arParams["FILTER"]["GROUPS_ID"]);
	           
        	

		}
		//print_r($this->arParams["FILTER"]["GROUPS_ID"]);

		$query->setOrder(array($this->arParams['SORT_FIELD1'] => $this->arParams['SORT_DIRECTION1'], $this->arParams['SORT_FIELD2'] => $this->arParams['SORT_DIRECTION2']));
		
		$query->setSelect(array("ID", "NAME", "EMAIL", "GROUPS_ID.GROUP_ID"));

		if(in_array("6", $this->arParams["FILTER"]["GROUPS_ID"])){
			//echo "sds";
			$query->addSelect("UF_IOGV");
		}
		
		
		$users = $query->exec();

		//print_r($query->getQuery());
		//echo "<pre>";
		//print_r($users);

		//$iterator = \CIBlockElement::GetList($sort, $filter, false, $this->navParams, $select);
		while ($user = $users->fetch())
		{
			//print_r($user);
			$this->arResult['ITEMS'][] = array(
				'ID' => $user['ID'],
            	'NAME' => $user['NAME'],
            	'EMAIL' => $user['EMAIL'],
            	'GROUP_ID' => $user['MAIN_USER_GROUPS_ID_GROUP_ID'],
            	'UF_IOGV' => $user['UF_IOGV'],
			);
		}
		if ($this->arParams['SHOW_NAV'] == 'Y' && $this->arParams['COUNT'] > 0)
		{
			$this->arResult['NAV_STRING'] = $iterator->GetPageNavString('');
		}
		//print_r($this->arResult);
	}

	/**
	 * выполняет действия после выполения компонента, например установка заголовков из кеша
	 */
	protected function executeEpilog()
	{
		
	}

	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
		global $APPLICATION, $USER;
		try
		{
			$this->checkModules();
			
			$this->executeProlog();
			if ($this->arParams['AJAX'] == 'Y')
				$APPLICATION->RestartBuffer();
			
			

			$cache = Bitrix\Main\Data\Cache::createInstance();
			
			$cache_manager = Bitrix\Main\Application::getInstance()->getTaggedCache();

			$this->arParams["CACHE_TIME"] = IntVal($this->arParams["CACHE_TIME"]);
			$CACHE_ID = SITE_ID."|".$APPLICATION->GetCurPage()."|";
			// Кеш зависит только от подготовленных параметров без "~"
			foreach ($this->arParams as $k => $v)
			   if (strncmp("~", $k, 1))
			      $CACHE_ID .= ",".$k."=".$v;
			$CACHE_ID .= "|".$USER->GetGroups();

			
			if ($cache->startDataCache($this->arParams["CACHE_TIME"], $CACHE_ID, "/".SITE_ID.$this->GetRelativePath()))
			{
			   // Запрос данных и формирование массива $arResult
				$this->getResult();
			
			   // Подключение шаблона компонента
			   $this->IncludeComponentTemplate();

			   $templateCachedData = $this->GetTemplateCachedData();

			   $cache_manager->startTagCache("/".SITE_ID.$this->GetRelativePath());
			   $cache_manager->registerTag('users');
			   $cache_manager->endTagCache();

			   $cache->endDataCache(
			      array(
			        "arResult" => [
			        	"ITEMS" => $this->arResult["ITEMS"]
			        ],
			    	"templateCachedData" => $templateCachedData
			      )
			   );
			}
			else
			{
			   $this->arResult["ITEMS"] = extract($cache->getVars());
			   $this->SetTemplateCachedData($templateCachedData);
			}
			

			$this->executeEpilog();
			if ($this->arParams['AJAX'] == 'Y')
				die();
			return $this->returned;
		}
		catch (Exception $e)
		{
			$this->AbortResultCache();
			ShowError($e->getMessage());
		}
	}


}
?>