<?include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use \Bitrix\Iblock\ElementTable;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Query;

global $USER;

if (!$USER->IsAdmin()) {die();}

//echo json_encode($_FILES["file"]);

if(!file_exists($_SESSION["file"])) {$_SESSION["step"] =1;}


if($_SESSION["step"]==1){
	if( !empty( $_FILES["file"]) ){
	    $error = false;
	    $files = array();
	 
	    $uploaddir = $_SERVER["DOCUMENT_ROOT"].'/upload/'; // . - текущая папка где находится submit.php
	 
	        if( move_uploaded_file( $_FILES["file"]['tmp_name'], $uploaddir . basename($_FILES["file"]['name']) ) ){
	            $files = realpath( $uploaddir . $_FILES["file"]['name'] );
	            $_SESSION["file"] = $files;

	            $document = simplexml_load_file($files);
				$doc = $document->cards;
				$d = $doc->card;
				$d[0]->skidka;
	            if(isset($d[0]->skidka)){
	            	unset($_SESSION["step"]);
	            	$_SESSION["step"] = 2;
	            }
	            else{
	            	$error = true;
	            	$err = "XML имеет не правильный формат!";
	            }
	            
	        }
	        else{
	            $error = true;
	            $err = "Ошибка загрузки файла!";
	        }
	  
	 
	    $data = $error ? array('error' => $err, "success" => "false") : array('files' => $files, "success" => "true", "step" => 2);
	 
	    echo json_encode( $data );
	    die();
	}
}
if($_SESSION["step"] == 2){
	Loader::includeModule('iblock');

	$query = new Query(ElementTable::getEntity());
		$query
		    ->setSelect(array("ID", "NAME"))
		    ->setFilter(array("IBLOCK_ID" => 17))
		    ->setOrder(array("ID" => "ASC"))
		    ->setLimit(1500);
		$result = $query->exec()->fetchAll();

		$count = count($result);

		if($count > 0){
			foreach ($result as $key) {
				//print_r($key["ID"]); echo "<pre>";
				CIBlockElement::Delete($key["ID"]);
			}

			echo json_encode(array("progress" => "continue", "step" => 2, "count" => $count ));
		}
		else {
			
			$_SESSION["step"] = 3;
			echo json_encode(array("progress" => "done", "step" => 3, "count" => $count));
			die();
		}

	
}
if($_SESSION["step"] == 3){
	

	Loader::includeModule('iblock');
	global $USER;
	
	$document = simplexml_load_file($_SESSION["file"]);
	$doc = $document->cards;
	
	$count = count($doc->card);
	$d = $doc->card;

	$timeout = 30;
	$iter = (!empty($_SESSION["iter"]) ? $_SESSION["iter"] : 0);

	$start_seconds = microtime(true);
	$end_second = $start_seconds + $timeout;



	for ($i=$iter; $i < $count; $i++) {
	
	$time = microtime(true);

	
	if($time > $end_second || ($_SESSION["iter"] >=$count-1)){
		$_SESSION["iter"] = $i;
		break;
	}

    $_SESSION["iter"] = $i;

    //echo $_SESSION["iter"], " _ "; echo $count-1, " "; echo $i; echo "<br>";
	//echo $time, " _ "; echo $end_second; echo "<br>";
    //echo $i;print_r($d[$i]->code); echo "<br>";

    $el = new CIBlockElement;
    $PROP = array();
	$PROP[98] = intval($d[$i]->skidka);  // свойству с кодом 12 присваиваем значение "Белый"
	$PROP[99] = intval($d[$i]->balance);        // свойству с кодом 3 присваиваем значение 38
	$PROP[100] = intval($d[$i]->nextskidka);  // свойству с кодом 12 присваиваем значение "Белый"
	$PROP[101] = intval($d[$i]->nextsumm);

	$arLoadProductArray = Array(
	  "MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
	  "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
	  "IBLOCK_ID"      => 17,
	  "PROPERTY_VALUES"=> $PROP,
	  "NAME"           => $d[$i]->name,
	  "ACTIVE"         => "Y",            // активен
	  "CODE" => $d[$i]->code
	  );
	$PRODUCT_ID = $el->Add($arLoadProductArray);
	unset($el);
	}

	if($_SESSION["iter"] >=$count-1){
		unlink($_SESSION["file"]);
		unset($_SESSION["file"]);
		unset($_SESSION["step"]);
		unset($_SESSION["iter"]);

		echo json_encode(array("col"=> $count, "iter"=>$_SESSION["iter"], "timeout"=> 30, "progress" => "done"));
		die();
	}
	else{
		echo json_encode(array("col"=> $count,"iter"=>$_SESSION["iter"], "timeout"=> 30, "progress" => "continue", "step" => 3));
	}
}
?>