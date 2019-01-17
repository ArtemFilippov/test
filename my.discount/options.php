<?
use Bitrix\Main\Localization\Loc;
use	Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);


$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

Loader::includeModule($module_id);

CJSCore::Init(array("jquery"));
//CAjax::Init();
//Asset::getInstance()->addJs("/bitrix/js/".$module_id."/jquery.min.js");
Asset::getInstance()->addJs("/bitrix/js/".$module_id."/my_script.js");

Asset::getInstance()->addCss("/bitrix/css/".$module_id."/style.css");


$aTabs = array(
	array(
		"DIV" 	  => "discount_edit",
		"TAB" 	  => Loc::getMessage("DISCOUNT_OPTIONS_TAB_NAME"),
		"TITLE"   => Loc::getMessage("DISCOUNT_OPTIONS_TAB_NAME"),
		"PAGE_TYPE" => "site_settings",
        "SITE_ID" => SITE_ID,
		"OPTIONS" => array(
			Loc::getMessage("DISCOUNT_OPTIONS_TAB_COMMON"),
			array(
				"xml_file",
				Loc::getMessage("DISCOUNT_OPTIONS_TAB_SWITCH_ON"),
				serialize(array()),
				array("file",array())
			)
		)
	)
);

$tabControl = new CAdminTabControl(
	"tabControl",
	$aTabs
);

$tabControl->Begin();

?>

<form id="xml_load" action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>" method="post">

	<?
	foreach($aTabs as $aTab){

		if($aTab["OPTIONS"]){
		
			$tabControl->BeginNextTab();

			//__AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);

			if($aTab["OPTIONS"][1][0] == "xml_file"):?>
			<tr>
				<td><?=Loc::getMessage("DISCOUNT_OPTIONS_TAB_SWITCH_ON")?></td>
				<td><?echo CFile::InputFile($aTab["OPTIONS"][1][0], 20, $str_IMAGE_ID="");?></td>
			</tr>
			<?endif;
		}
	}

	$tabControl->Buttons();
	?>

	<input type="submit" name="apply1" value="<? echo(Loc::GetMessage("DISCOUNT_OPTIONS_INPUT_APPLY")); ?>" class="" />
	

	<?
	echo(bitrix_sessid_post());
	?>

</form>
<div class="error">
	<p></p>
</div>

<div class="progress">
	<p></p>
</div>

<script type="text/javascript">
	
</script>
<?
$tabControl->End();
?>