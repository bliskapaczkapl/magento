<?php

/**
 * Plik wymiany danych z systemem Baselinker
 * @author Sewer Skrzypiński <info@baselinker.com>
 * @version 4
 * @package baselinker
 */

/** -----------------------------------------------------------------------------------------------------------
 * Ustawienia wpisywane przez sprzedawcę
 *	- Należy uzupełnić dane wprowadzając je między apostrofy
 */
$options['baselinker_pass'] = '';		//hasło do komunikacji (dostępne w panelu Baselinkera w zakładce 'sklep internetowy')

$options['db_host'] = 'localhost';		//adres hosta bazy danych (najczęściej localhost)
$options['db_user'] = '';		//użytkownik bazy danych
$options['db_pass'] = '';		//hasło bazy danych
$options['db_name'] = '';		//nazwa bazy danych
$options['db_prefix'] = '';			//prefiks tabel bazy danych - domyślnie pozostaw pusty aby wykryć automatycznie

$options['images_folder'] = 'http://ADRES_SKLEPU.pl/media/catalog/product/';  //adres folderu zawierającego zdjęcia produktów i producentów (rozpoczęty 'http://', zakończony /)

$options['store_id'] = '';			//identyfikator sklepu, automatycznie pobierany domyślny
$options['website_id'] = '';			//identyfikator wersji strony sklepu (np PL), automatycznie pobierany domyślny
$options['customer_group_id'] = '0';		//(general?)
$options['special_price'] = 1;		//czy używać ceny promocyjnej jeśli produkt jest w promocji? (0 - nie, 1 - tak)

$options['charset'] = 'UTF-8';			//zestaw znaków bazy danych (standardowo UTF-8)
$options['def_tax'] = 23;			// domyślna stawka VAT (jeśli nie uda sie dopasować ze sklepu)

$options['no_variants'] = 0;			//czy warianty produktów mają być wyświetlane niezależnie (0 - nie, 1 - tak)
$options['create_invoice'] = 0;			//czy tworzyć fakturę dla każdego zamówienia przesłanego do sklepu (0 - nie, 1 - tak);
$options['oa_stock_refresh'] = 0;		//aktualizacja stanów magazynowych przy aktualizacji zamówienia
$options['currency'] = '';		//waluta
$options['currency_rate'] = '1';		//przelicznik waluty, domyślnie 1, pozostaw pusty aby wykryć automatycznie
$options['tax_country_code'] = 'PL';	// kod kraju do wyliczania stawek podatkowych
$options['create_customer'] = 0;		//czy tworzyć konto klienta przy dodwaniu zamówienia do sklepu
$options['split_discounts'] = 0;		// kwota rabatu rozbita na wszystkie produkty zamówienia (1) lub jako osobna pozycja (0)

error_reporting(E_ERROR | E_WARNING);
date_default_timezone_set('Europe/Warsaw');

/** -----------------------------------------------------------------------------------------------------------
 * Funkcje zarządzające komunikacją (przedrostek Conn_) oraz funkcje ułatwiające zapytania SQL (przedrostek DB_)
 *	- Jednakowe niezależnie od platformy 
 *	- Nie należy edytować poniższego kodu
 */
 
 
 
/**
 * Definicja funkcji json_encode oraz json_decode dla PHP4 (istnieją domyślnie w PHP5.2), iconv() dla tablic, oraz array_walk_recursive()
 * Nie należy edytować. Credits goes to Steve http://usphp.com/manual/en/function.json-encode.php#82904
 */
if (!function_exists('json_encode'))
{
	function json_encode($a=false,$is_key=false)
	{if (is_null($a)) return 'null';if ($a === false) return 'false';if ($a === true) return 'true';
	if (is_scalar($a)){if(is_int($a)&&$is_key){return '"'.$a.'"';} if (is_float($a)){return floatval(str_replace(",", ".", strval($a)));}if (is_string($a)){
    static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
    return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';} else return $a;} $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)){if (key($a) !== $i){$isList = false;break;}}
    $result = array(); if ($isList){foreach ($a as $v) $result[] = json_encode($v); return '[' . join(',', $result) . ']';}
    else {foreach ($a as $k => $v) $result[] = json_encode($k,true).':'.json_encode($v); return '{' . join(',', $result) . '}';}}
}
if (!function_exists('json_decode'))
{
	function json_decode($json, $assoc = true)
	{$comment = false; $out = '$x='; for ($i=0; $i<strlen($json); $i++) { if (!$comment) {if (($json[$i] == '{') || ($json[$i] == '['))
	$out .= ' array('; else if (($json[$i] == '}') || ($json[$i] == ']'))   $out .= ')'; else if ($json[$i] == ':')    $out .= '=>';
    else $out .= $json[$i]; } else $out .= $json[$i]; if ($json[$i] == '"' && $json[($i-1)]!="\\")    $comment = !$comment;} eval($out . ';'); return $x;}
}
if (!function_exists('array_walk_recursive'))
{
    function array_walk_recursive(&$input, $funcname, $userdata = "")
    {if (!is_callable($funcname)){return false;}if (!is_array($input)){return false;}foreach ($input AS $key => $value){
	if (is_array($input[$key])){array_walk_recursive($input[$key], $funcname, $userdata);}else{$saved_value = $value;
	if (!empty($userdata)){$funcname($value, $key, $userdata);}else{$funcname($value, $key);}if ($value != $saved_value)
	{$input[$key] = $value;}}}return true;}
}

function array_iconv(&$val, $key, $userdata)
{$val = iconv($userdata[0], $userdata[1], $val);}
function recursive_iconv($in_charset, $out_charset, $arr)
{if (!is_array($arr)){return iconv($in_charset, $out_charset, $arr);}$ret = $arr;
array_walk_recursive($ret, "array_iconv", array($in_charset, $out_charset));return $ret;} 


/**
 * Funkcje wykonujące zapytania SQL
 */
function DB_Query($sql)
{
	global	$dbh;

	if (func_num_args() > 1)
	{
		$i = 0;

		foreach(func_get_args() as $val)
		{
			if ($i==0)
			{
				$i++; 
				continue;
			}

			if (is_array($val))
			{
				foreach ($val as $k => $v)
				{
					$sql = str_replace('{'.$k.'}', substr($dbh->quote($v), 1, -1), $sql);
				}
			}
			else
			{
				$sql = str_replace('{'.($i-1).'}', substr($dbh->quote($val), 1, -1), $sql);
			}

			$i++;
		}
	}

	if (!($sth = $dbh->prepare($sql)))
	{
		$err = $dbh->errorInfo();
		Conn_error('db_query', 'SQL error: ' . $err[2]);
	}

	if (!($sth->execute()))
	{
		$err = $sth->errorInfo();
		Conn_error('db_query', 'SQL error: ' . $err[2]);
	}

	return $sth;
}

function DB_Result($sth, $num = 0) { if (DB_NumRows($sth) > $num){return $sth->fetchColumn($num);} return false; }

function DB_Identity() { global $dbh; return $dbh->lastInsertId(); }

function DB_NumRows($sth) { return $sth->rowCount(); }

function DB_Fetch($sth) { return $sth->fetch(PDO::FETCH_ASSOC); }


/**
 * Funkcja obsługująca żądania i wysyłająca odpowiedź.
 * Zalecane jest pozostawienie funkcji w tej postaci niezależnie od platformy
 * @global array $options : tablica z ustawieniami ogólnymi
 */
function Conn_Init()
{
	global $options;
	
	//sprawdzanie poprawności hasła wymiany danych
	if(!isset($_POST['bl_pass']))
	{Conn_Error("no_password","Odwołanie do pliku bez podania hasła. Jest to poprawny komunikat jeśli plik integracyjny został otworzony w przeglądarce internetowej.");}
	elseif($options['baselinker_pass'] == "" || $options['baselinker_pass'] !== $_POST['bl_pass'])
	{Conn_Error("incorrect_password");}
	
	//zmiana kodowania danych wejściowych
	if($options['charset'] != "UTF-8")
	{
		foreach($_POST as $key => $val)
		{$_POST[$key] = iconv('UTF-8', $options['charset'].'//IGNORE', $val);}
	}
	
	//łączenie z bazą danych sklepu
	Shop_ConnectDatabase($_POST);
	
	//rozbijanie tablic z danymi
	if(isset($_POST['orders_ids'])){$_POST['orders_ids'] = explode(',', $_POST['orders_ids']);}
	if(isset($_POST['products_id'])){$_POST['products_id'] = explode(',', $_POST['products_id']);}
	if(isset($_POST['fields'])){$_POST['fields'] = explode(',', $_POST['fields']);}
	if(isset($_POST['products'])){$_POST['products'] = json_decode($_POST['products'], true);}

	//sprawdzanie czy podana metoda jest zaimplementowana
	if(function_exists("Shop_".$_POST['action']))
	{
		$method = "Shop_".$_POST['action'];
		Conn_SendResponse($method($_POST));
	}
	else
	{Conn_Error("unsupported_action", "No action: ".$_POST['action']);}
}


/**
 * Funkcja generująca odpowiedź do systemu w formacie JSON
 * @global array $options tablica z ustawieniami ogólnymi
 */
function Conn_SendResponse($response)
{
	global $options;

	//zmiana kodowania danych wyjściowych
	if($options['charset'] != "UTF-8" && count($response) > 0)
	{
		foreach($response as $key => $val)
		{$response[$key] = recursive_iconv($options['charset'], 'UTF-8//IGNORE', $val);}
	}

	print json_encode($response);
	exit();
}


/**
 * Funkcja wypisująca kominukat błędu w formacie JSON i kończąca skrypt
 * Zalecane jest pozostawienie funkcji w tej postaci niezależnie od platformy
 * @param string $error_code kod błędu (standardowe wartości: db_connect, db_query, no_action)
 * @param string $error_text opis błędu
 */
function Conn_Error($error_code, $error_text = '')
{
	print json_encode(array('error' => true, 'error_code' => $error_code, 'error_text' => $error_text));
	exit();
}


 /**
 * Ewentualne wczytanie dodatkowych funkcji z pliku baselinker_pm.php (BaseLinker Product Managment) 
 * Zawarte w dodatkowym pliku funkcje rozszerzają możliwości integracji ze sklepem o funkcje pozwalające dodawać i zmieniać kategorie, produkty oraz warianty.
 * Obsługa tych funkcji jest wymagana przez niektóre moduły BaseLinkera (np. moduły integrujące system z programami typu ERP)
 * Plik baselinker_pm.php jest dostępny dla wybranych platform sklepów. Skontaktuj się z administratorem w cely uzyskania pliku.
 */ 
if(file_exists("baselinker_pm.php"))
{include("baselinker_pm.php");}


//inicjacja komunikacji
Conn_Init(); 




/** -----------------------------------------------------------------------------------------------------------
 * Funkcje obsługiwania żądań (przedrostek Shop_)
 *	- Zależne od platformy sklepu
 *	- Do edycji dla deweloperów
 */


 
 /**
 * Funkcja zwracająca wersję pliku wymiany danych
 * Przy tworzeniu pliku należy skonsultować numer wersji i nazwę platformy
 * z administracją systemu Baselinker
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica z danymi platformy z polami:
 * 		platform => nazwa platformy
 * 		version => numer wersji pliku
 */
function Shop_FileVersion($request)
{
	$response['platform'] = "Magento";
	$response['version'] = "4.1.77"; //wersja pliku integracyjnego, nie wersja sklepu! 
	$response['standard'] = 4; //standard struktury pliku integracyjnego - obecny standard to 4.
	
	return $response;
}


/**
 * Funkcja zwracająca listę zaimplementowanych metod pliku
 * Zalecane jest pozostawienie funkcji w tej postaci niezależnie od platformy
 */
function Shop_SupportedMethods()
{
	$result = array();
	$methods = get_defined_functions();

	foreach($methods['user'] as $m)
	{
		if ($options['no_variants'] and $m == 'Shop_ProductsPrices')
		{
			continue; // przy spłaszczonej liście produktów korzystamy wyłącznie z ProductsList!
		}

		if (stripos($m, 'shop_') === 0)
		{$result[] = substr($m,5);}
	}

	return $result;
}



 /**
 * Funkcja nawiązująca komunikację z bazą danych sklepu
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return boolean wartość logiczna określajaca sukces połączenia z bazą danych
 */
function Shop_ConnectDatabase($request)
{
	global $options; //globalna tablica z ustawieniami
	global $dbh; // handler bazy danych

	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy
	
	// wydzielenie portu z nazwy hosta
	if (preg_match('/^\s*([\w\-\.]+):(\d+)\s*$/', $options['db_host'], $m))
	{
		$options['db_host'] = $m[1];
		$options['db_port'] = $m[2];
	}

	// wygenerowanie DSN
	$dsn = "mysql:dbname=${options['db_name']};host=${options['db_host']}";

	if ($options['db_port'])
	{
		$dsn .= ";port=${options['db_port']}";
	}

	// nawiązanie połączenia z bazą danych sklepu
	try {
		$dbh = new PDO($dsn, $options['db_user'], $options['db_pass']);
	} catch (Exception $ex) {
		Conn_Error('db_connection', $ex->getMessage());
	}

	if($options['charset'] == "UTF-8")
	{DB_Query("SET NAMES utf8");}

	DB_Query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
	
	//automatyczne wyszukiwanie prefiksu bazy danych
	if($dbp == "")
	{	
		$unique_table = "catalog_category_entity_varchar"; //wyszukiwanie tabeli z unikalną nazwą
		$search_table = DB_Query("SHOW TABLES LIKE '%${unique_table}'");
		
		if(DB_NumRows($search_table) >= 1)
		{$options['db_prefix'] = str_replace($unique_table, '', DB_Result($search_table)); $dbp = $options['db_prefix'];}
		else
		{Conn_Error("database_prefix");} //nie wykryto jednoznacznie prefiksu
	}

	// wyszukiwanie domyślnej stawki podatku
	$sql = "SELECT tax_calculation_rate_id FROM `${dbp}tax_calculation_rate`
		WHERE tax_country_id = (SELECT value FROM `{$dbp}core_config_data` WHERE path = 'tax/defaults/country' ORDER BY scope_id LIMIT 1) LIMIT 1";
	$res = DB_Query($sql);
	$options['tax_calculation_rate_id'] = DB_Result($res, 0);

	// czy ceny zawierają podatek
	$sql = "SELECT `value` FROM `${dbp}core_config_data` WHERE path = 'tax/calculation/price_includes_tax'";
	$res = DB_Query($sql);
	$options['apply_tax'] = DB_NumRows($res) ? (DB_Result($res) ? 0 : 1) : 0;

	// wyszukiwanie domyślnego id witryny
	if ($options['website_id'] == '')
	{
		if ($options['store_id']) // pobierz website ID przypisany do konkretnego widoku
		{
			$sql = "SELECT website_id FROM `${dbp}core_store` WHERE store_id = '{0}' AND is_active = 1";
			$res = DB_Query($sql, $options['store_id']);
			$options['website_id'] = DB_Result($res);
		}

		if (!$options['website_id'])  // domyślny website_id
		{
			$sql = "SELECT website_id FROM `${dbp}core_website` WHERE is_default = 1";
			$res = DB_Query($sql);
			$options['website_id'] = DB_Result($res, 0);
		}
	}

	$options['website_id'] = (int)$options['website_id'];

	// wyszukiwanie domyślnego id sklepu
	if ($options['store_id'] == '')
	{
		$sql = "SELECT store_id FROM `${dbp}core_store` WHERE (website_id = {0} OR code = 'default') AND is_active = 1
			ORDER BY (website_id = {0}) DESC, (code = 'default') DESC LIMIT 1";
		$res = DB_Query($sql, $options['website_id']);
		$options['store_id'] = DB_Result($res, 0);
	}

	$options['store_id'] = (int)$options['store_id'];

	// określenie wersji Magento
	$options['magento_version'] = '99999999'; // domyślnie 99.99.99.99

	$sql = "SHOW COLUMNS FROM `${dbp}sales_flat_order_status_history` LIKE '{0}'";
	$res = DB_Query($sql, 'entity_name');

	if (!DB_NumRows($res))
	{
		$options['magento_version'] = '01050001';
	}

	// przeliczanie cen
	if (!$options['currency_rate'] and $options['currency'] and DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}directory_currency_rate'")))
	{
		$options['currency_rate'] = DB_Result(DB_Query("SELECT rate FROM `${dbp}directory_currency_rate` WHERE currency_to = '{0}' AND currency_from = (SELECT `value` FROM `${dbp}core_config_data` WHERE scope_id = 0 AND path = 'currency/options/base')", $options['currency']));
	}

	if (!$options['currency_rate'])
	{
		$sql = "SHOW TABLES LIKE '${dbp}catalog_product_index_website'";

		if (DB_NumRows(DB_Query($sql)))
		{
			$sql = "SELECT rate FROM `${dbp}catalog_product_index_website` WHERE website_id = {0}";

			if ($options['magento_version'] >= '01060000')
			{
				$sql .= " ORDER BY website_date DESC";
		}

			$res = DB_Query($sql, $options['website_id']);

			$options['currency_rate'] = DB_NumRows($res) ? 1/DB_Result($res) : 1;
		}
	}

	// podatek od kosztów wysyłki
	$sql = "SELECT tcr.rate 
		FROM `${dbp}tax_calculation_rate` tcr
		JOIN `${dbp}tax_calculation` tc ON tc.tax_calculation_rate_id = tcr.tax_calculation_rate_id
		JOIN `${dbp}core_config_data` ccd ON ccd.path = 'tax/classes/shipping_tax_class' AND value = tc.product_tax_class_id
		ORDER BY scope_id = '{0}' DESC, scope = 'default' DESC LIMIT 1";
	$options['shipping_tax_rate'] = (float)DB_Result(DB_Query($sql, $options['store_id']));
}





 /**
 * Funkcja zwraca listę kategorii sklepowych
 * Zwracana tabela powinna być posortowana alfabetycznie
 * W nazwie kategorii podrzędnej powinna być zawrta nazwa nadkategorii - np "Komputery/Karty graficzne" zamiast "Karty graficzne"
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica z listą kategori sklepowch w formacie:
 * 		id kategorii => nazwa kategorii
 */
function Shop_ProductsCategories($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	$categories = $paths = $response = $active = array();
	$root_path = '';

	if ($options['website_id']) // wykrywanie gałęzi startowej dla wybranego website_id
	{
		$sql = "SELECT path FROM `${dbp}catalog_category_entity`
			WHERE entity_id = (SELECT root_category_id FROM `${dbp}core_store_group` WHERE website_id = '{0}' LIMIT 1)";

		if ($root_path = DB_Result(DB_Query($sql, $options['website_id'])))
		{
			$root_path .= '/%';
		}
	}
	

	//pobieranie kategorii z bazy i zapisywanie do tabeli
	$sql = "SELECT cce.entity_id AS id, if(isnull(ccev.value), ccev1.value, ccev.value) AS name, parent_id, path, position, if(isnull(ccei.value), ccei1.value, ccei.value) active
		FROM `${dbp}catalog_category_entity` cce
		/* name */
		JOIN `${dbp}eav_attribute` ean ON ean.entity_type_id = cce.entity_type_id AND ean.attribute_code = 'name'
		LEFT JOIN `${dbp}catalog_category_entity_varchar` ccev ON cce.entity_id = ccev.entity_id AND ccev.attribute_id = ean.attribute_id AND ccev.store_id = {0}
		LEFT JOIN `${dbp}catalog_category_entity_varchar` ccev1 ON cce.entity_id = ccev1.entity_id AND ccev1.attribute_id = ean.attribute_id AND ccev1.store_id = 0 
		/* active */
		JOIN `${dbp}eav_attribute` eaa ON eaa.entity_type_id = cce.entity_type_id AND eaa.attribute_code = 'is_active'
		LEFT JOIN `${dbp}catalog_category_entity_int` ccei ON ccei.entity_id = cce.entity_id AND ccei.attribute_id = eaa.attribute_id AND ccei.store_id = {0} 
		LEFT JOIN `${dbp}catalog_category_entity_int` ccei1 ON ccei1.entity_id = cce.entity_id AND ccei1.attribute_id = eaa.attribute_id AND ccei1.store_id = 0
		WHERE path LIKE '{1}'";

	$res = DB_Query($sql, $options['store_id'], $root_path ? $root_path : '%');

	while ($cat = DB_Fetch($res))
	{
		$categories[$cat['id']] = $cat['name'];
		$active[$cat['id']] = $cat['active'];
		$paths[$cat['id']] = $cat['path'];
	}
	
	
	// konwersja nazw kategorii do pełnych ścieżek
	foreach ($paths as $cat_id=>$path)
	{
		$category_path = array();

		foreach (explode('/', $path) as $path_cat_id)
		{
			if (isset($categories[$path_cat_id]) and $active[$path_cat_id])
			{
				$category_path[] = $categories[$path_cat_id];
			}
		}

		if ($active[$cat_id])
		{
			$response[$cat_id] = implode('/', $category_path);
		}
	}

	asort($response);

	return $response;
}





 /**
 * Funkcja zwraca listę produktów z bazy sklepu
 * Zwracane liczby (np ceny) powinny mieć format typu: 123456798.12 (kropka oddziela część całkowitą, 2 miejsca po przecinku)
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu zawierająca pola:
 *		category_id => 			id kategori (wartość 'all' jeśli wszystkie przedmioty)
 *		filter_limit => 		limit zwróconych kategorii w formacie SQLowym ("ilość pomijanych, ilość pobieranych")
 *		filter_sort => 			wartość po której ma być sortowana lista produktów. Możliwe wartości:
 *								"id [ASC|DESC]", "name [ASC|DESC]", "quantity [ASC|DESC]", "price [ASC|DESC]"
 *		filter_id => 			ograniczenie wyników do konkretnego id produktu
 *		filter_ean => 			ograniczenie wyników do konkretnego ean
 *		filter_sku => 			ograniczenie wyników do konkretnego sku (numeru magazynowego)
 *		filter_name => 			filtr nazw przedmiotów (fragment szukanej nazwy lub puste pole)
 *		filter_price_from =>	dolne ograniczenie ceny (nie wyświetlane produkty z niższą ceną) 
 *		filter_price_to =>		górne ograniczenie ceny
 *		filter_quantity_from =>	dolne ograniczenie ilości produktów
 *		filter_quantity_to =>	górne ograniczenie ilości produktów
 *		filter_available =>		wyświetlanie tylko produktów oznaczonych jako dostępne (wartość 1) lub niedostępne (0) lub wszystkich (pusta wartość)
 * @return array $response tablica z listą produktów w formacie:
 * 		id produktu => 
						'name' => nazwa produktu
						'quantity' => dostępna ilość
						'price' => cena w PLN
 */
function Shop_ProductsList($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	// formuła SQL do wyliczania ceny końcowej
	$price_spec = "if(NOT isnull(sp.value) AND '${options['special_price']}' AND (isnull(spfrom.value) OR spfrom.value <= '{2}') AND (isnull(spto.value) OR spto.value >= '{2}'), sp.value, if(isnull(cpip.group_price), if(isnull(cpip.final_price), cped.value, if('${options['special_price']}', cpip.final_price, cpip.price)), cpip.group_price))*${options['currency_rate']}";

	//zmiana nazw kolumn na nazwy pól
	$request['filter_sort'] = str_replace(
		array('id', 'name', 'quantity', 'price'), 
		array('cpe.entity_id', 'cpev.value',  'if(csi.qty*csi.is_in_stock, csi.qty*csi.is_in_stock, sum(csi_v.is_in_stock*csi_v.qty))', $price_spec),
		$request['filter_sort']);

	// mapowanie id atrybutów (optymalizacja selecta poniżej)
	$attr_id = array();
	$sql = "SELECT attribute_id, attribute_code
		FROM `${dbp}eav_attribute`
		WHERE entity_type_id = (SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'catalog_product')";
	$res = DB_Query($sql);

	while ($row = DB_Fetch($res))
	{
		$attr_id[$row['attribute_code']] = $row['attribute_id'];
	}
	
	// pobieranie produktow z bazy danych
	// podstawowy select:
	$sql = "SELECT cpe.entity_id, if(isnull(cpev.value), cpev1.value, cpev.value) AS name, csi.qty*csi.is_in_stock qty, cpe.sku,
		$price_spec price, sum(csi_v.is_in_stock*csi_v.qty) vqty, cpe.type_id, cpee.value ean
		/* produkty zawężone do website_id */
		FROM `${dbp}catalog_product_entity` cpe
		JOIN `${dbp}catalog_product_website` cpw ON cpe.entity_id = cpw.product_id AND cpw.website_id = '${options['website_id']}'
		/* status */
		LEFT JOIN `${dbp}catalog_product_entity_int` cpest ON cpest.entity_id = cpe.entity_id AND cpest.attribute_id = ${attr_id['status']} AND cpest.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_int` cpest1 ON cpest1.entity_id = cpe.entity_id AND cpest1.attribute_id = ${attr_id['status']} AND cpest1.store_id = 0
		/* visibility */
		LEFT JOIN `${dbp}catalog_product_entity_int` cpevi ON cpevi.entity_id = cpe.entity_id AND cpevi.attribute_id = ${attr_id['visibility']} AND cpevi.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_int` cpevi1 ON cpevi1.entity_id = cpe.entity_id AND cpevi1.attribute_id = ${attr_id['visibility']} AND cpevi1.store_id = 0
		/* name */
		LEFT JOIN `${dbp}catalog_product_entity_varchar` cpev ON cpev.entity_id = cpe.entity_id AND cpev.attribute_id = ${attr_id['name']} AND cpev.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_varchar` cpev1 ON cpev1.entity_id = cpe.entity_id AND cpev1.attribute_id = ${attr_id['name']} AND cpev1.store_id = 0
		/* price */
		LEFT JOIN `${dbp}catalog_product_entity_decimal` cped ON cped.entity_id = cpe.entity_id AND cped.attribute_id = ${attr_id['price']} AND cped.store_id = {0}
		/*LEFT JOIN `${dbp}catalog_product_entity_decimal` cped1 ON cped1.entity_id = cpe.entity_id AND cped1.attribute_id = ${attr_id['price']} AND cped1.store_id = 0*/
		LEFT JOIN `${dbp}catalog_product_index_price` cpip ON cpip.entity_id = cpe.entity_id AND cpip.customer_group_id = {1}
		AND cpip.website_id = '${options['website_id']}'
		/* ean */
		LEFT JOIN `${dbp}catalog_product_entity_varchar` cpee ON cpee.entity_id = cpe.entity_id AND cpee.attribute_id = '${attr_id['ean']}'
		/* special_price */
		LEFT JOIN `${dbp}catalog_product_entity_decimal` sp ON sp.entity_id = cpe.entity_id AND sp.attribute_id = ${attr_id['special_price']} AND sp.store_id = {0}
		/*LEFT JOIN `${dbp}catalog_product_entity_decimal` sp1 ON sp1.entity_id = cpe.entity_id AND sp1.attribute_id = ${attr_id['special_price']} AND sp1.store_id = 0*/
		/* zakres czasowy promocji */
		LEFT JOIN `${dbp}catalog_product_entity_datetime` spfrom ON spfrom.entity_id = cpe.entity_id AND spfrom.attribute_id = ${attr_id['special_from_date']} AND spfrom.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_datetime` spto ON spto.entity_id = cpe.entity_id AND spto.attribute_id = ${attr_id['special_to_date']} AND spto.store_id = {0}
		/* relation */
		LEFT JOIN `${dbp}catalog_product_relation` cpr ON cpr.child_id = cpe.entity_id
		LEFT JOIN `${dbp}catalog_product_entity` cpep ON cpep.entity_id = cpr.parent_id
		/* quantity */
		LEFT JOIN `${dbp}cataloginventory_stock_item` csi ON csi.product_id = cpe.entity_id
                LEFT JOIN `${dbp}catalog_product_relation` cpr_v ON cpr_v.parent_id = cpe.entity_id
                LEFT JOIN `${dbp}cataloginventory_stock_item` csi_v ON csi_v.product_id = cpr_v.child_id ";

	// jeśli do cen ma być doliczony vat:
	if ($options['apply_tax'])
	{
		// pobieramy tax class id
		$sql .= " LEFT JOIN `${dbp}catalog_product_entity_int` cptxc ON cptxc.entity_id = cpe.entity_id AND cptxc.attribute_id = ${attr_id['tax_class_id']} AND (cptxc.store_id = {0} OR cptxc.store_id = 0)";
		$sql = preg_replace('/^(SELECT )/i', '$1 cptxc.value tax_class_id,', $sql);
	}
	
	// zawężenie do kategorii:
	if ($request['category_id'] != 'all' && !empty($request['category_id']))
	{
		$sql = preg_replace('/\s+FROM\s+/', ", ccp.category_id, sum(ccp_v.category_id) variants_category_id$0", $sql);
		$sql .= " LEFT JOIN `${dbp}catalog_category_product` ccp ON ccp.product_id = cpe.entity_id AND ccp.category_id = {category_id}";
		$sql .= " LEFT JOIN `${dbp}catalog_category_product` ccp_v ON ccp_v.product_id = cpr_v.child_id AND ccp_v.category_id = {category_id}";
	}

	// tylko produkty enabled
	$sql .= ' WHERE if(isnull(cpest.value), cpest1.value, cpest.value) = 1';

	// i widoczne,  chyba że działamy w trybie bezwariantowym i rodzic jest konfigurowalny
	$sql .= " AND (if(isnull(cpevi.value), cpevi1.value, cpevi.value) <> 1" . ($options['no_variants'] ? ' OR cpep.type_id = "configurable"' : '') . ")";

	// odfiltrowywanie wariantów w zależności od wybranego trybu
	$sql .= $options['no_variants'] ? ' AND cpe.type_id <> "configurable"' : ' AND (isnull(cpep.type_id) OR cpep.type_id <> "configurable")';

	// filtry:
	if ($request['filter_id'] != '') { $sql .= " AND cpe.entity_id = '{filter_id}'";} //filtrowanie id
	if ($request['filter_sku'] != '') { $sql .= " AND cpe.sku LIKE '%{filter_sku}%'"; } //filtrowanie sku
	if ($request['filter_ean'] != '') { $sql .= " AND cpee.value LIKE '%{filter_ean}%'"; } //filtrowanie ean
	if ($request['filter_name'] != '') { $sql .= " AND if(isnull(cpev.value), cpev1.value, cpev.value) LIKE '%{filter_name}%'"; } //filtrowanie nazwy
	if ($request['filter_available'] != '') { $sql .= " AND csi.is_in_stock = '{filter_available}'"; } //produkty dostępne/niedostępne

	$sql .= ' GROUP BY cpe.entity_id';

	// filtrowanie ilości
	$filter_qty = array();

	if ($request['filter_quantity_from'] != '')
	{
		$filter_qty[] = "(qty >= '${request['filter_quantity_from']}' OR vqty >= '{filter_quantity_from}')";
	}
	if ($request['filter_quantity_to'] != '')
	{
		$filter_qty[] = "(qty <= '${request['filter_quantity_to']}' OR vqty <= '{filter_quantity_to}')";
	}

	if (count($filter_qty))
	{
		$sql .= ' HAVING ' . implode(' AND ', $filter_qty);
	}

	if ($request['category_id'] != 'all' && !empty($request['category_id']))
	{
		$sql .= count($filter_qty) ? ' AND ' : ' HAVING ';
		// dopasowanie po id kategorii produktu głównego lub któregoś z jego wariantów
		$sql .= " (not isnull(category_id) OR not isnull(variants_category_id))";
	}


	// sortowanie
	if ($request['filter_sort'] != '') { $sql .= " ORDER BY {filter_sort}"; }

	// ograniczenie liczby wyników
	if ($request['filter_limit'] != '') { $sql .= " LIMIT {filter_limit}"; }

	$response = array();
	$result = DB_Query($sql, $options['store_id'], $options['customer_group_id'], gmdate('Y-m-d H:i:s'),
		array(
			'filter_id' => $request['filter_id'],
			'filter_sku' => $request['filter_sku'],
			'filter_ean' => $request['filter_ean'],
			'filter_name' => $request['filter_name'],
			'filter_available' => $request['filter_available'],
			'filter_quantity_from' => $request['filter_quantity_from'],
			'filter_quantity_to' => $request['filter_quantity_to'],
			'category_id' => $request['category_id'],
			'filter_sort' => $request['filter_sort'],
			'filter_limit' => $request['filter_limit'],
	));
	
	while ($prod = DB_Fetch($result))
	{
		//zmiana formatu ilosci produktow w bazie
		$prod['qty'] = number_format($prod['qty'], 0, '.', '');
			
		// doliczanie podatku
		if ($options['apply_tax'])
		{
			$prod['price'] *= (tax_rate($prod['tax_class_id'])+100)/100;
			$prod['price'] = number_format($prod['price'], 2, '.', '');
		}
		
		// formatowanie ceny
		$prod['price'] = number_format($prod['price'], 2, '.', '');
		
		//filtrowanie ceny
		if($request['filter_price_from'] != "" && $prod['price'] < $request['filter_price_from']) {continue;} //dolne ograniczenie ceny
		if($request['filter_price_to'] != "" && $prod['price'] > $request['filter_price_to']) {continue;} //górne ograniczenie ceny
			
		$response[$prod['entity_id']] = array('name' => $prod['name'], 'price' => $prod['price'], 'sku' => $prod['sku'], 'ean' => $prod['ean'],
		// ilość produktu głównego jest sumą ilości wariantów!
		'quantity' => (int)$prod['qty'] ? $prod['qty'] : (($prod['type_id'] == 'bundle') ? 0 : (int)$prod['vqty']));
	}
	
	return $response;
}





 /**
 * Funkcja zwraca szczegółowe dane wybranych produktów
 * Zwracane liczby (np ceny) powinny mieć format typu: 123456798.12 (kropka oddziela część całkowitą, 2 miejsca po przecinku)
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu zawierająca pola:
 *		products_id => 			tablica z numerami id produktów
 *		fields => 				tablica z nazwami pól do zwrócenia (jeśli pusta zwracany jest cały wynik)
 * @return array $response tablica z listą produktów w formacie:
 * 		id produktu => 
						'name' => nazwa produktu, 'ean' => Kod EAN, 'sku' => numer katalogowy, 'model' => nazwa modelu lub inny identyfikator np ISBN, 
						'description' => opis produktu (może zawierać tagi HTML), 'description_extra1' => drugi opis produktu (np opis krótki) 'weight' => waga produktu w kg,
						'quantity' => dostępna ilość, 'man_name' => nazwa producenta, 'man_image' => pełny adres obrazka loga producenta,
						'category_id' => numer ID głównej kategorii, 'category_name' => nazwa kategori do której należy przedmiot, 'tax' => wielkość podatku w formie liczby (np 23)
						'price' => cena brutto w PLN,
						'images' => tablica z pełnymi adresami dodatkowych obrazków (pierwsze zdjęcie główne, reszta w odpowiedniej kolejności),
						'features' => tablica z opisem cech produktu. Poszczególny element tablicy zawiera nazwę i wartość cechy, np array('Rozdzielczość','Full HD')
						'variants' => tablica z wariantami produktu do wyboru (np kolor, rozmiar). Format pola opisany jest w kodzie poniżej
 */
function Shop_ProductsData($request)
{
	global $options; // globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy
	
	$category_tree = Shop_ProductsCategories($request);
	$response = array();
		
	// kwerenda pobierająca ID produktów oraz ceny końcowe
	$sql = "SELECT cpe.entity_id, cpip.final_price, cpip.price, cpip.group_price, csi.qty*csi.is_in_stock qty,
		cpe.attribute_set_id, cpr.parent_id, cpe.type_id
		FROM `${dbp}catalog_product_entity` cpe
		LEFT JOIN `${dbp}catalog_product_index_price` cpip ON cpip.entity_id = cpe.entity_id
		AND cpip.customer_group_id = ${options['customer_group_id']} AND cpip.website_id = '${options['website_id']}'
		LEFT JOIN `${dbp}catalog_product_relation` cpr ON cpr.child_id = cpe.entity_id
		LEFT JOIN `${dbp}catalog_product_entity` cpep ON cpep.entity_id = cpr.parent_id
		LEFT JOIN `${dbp}cataloginventory_stock_item` csi ON csi.product_id = cpe.entity_id";
		
	// zawężenie do konkretnych produktów oraz pominięcie wariantów (będą obsłużone później)
	$sql .= " WHERE cpe.entity_id IN (" . implode(', ', $request['products_id']) . ')';

	// ograniczenie tylko do produktów podstawowych (np konfigurowalnych)
	if (!$options['no_variants'])
	{
		$sql .= ' AND (isnull(parent_id) OR cpep.type_id = "bundle" OR cpep.type_id = "grouped")';
	}
		
	// ograniczenie do jednej kategorii per produkt
	$sql .= " GROUP BY cpe.entity_id";
		
	$result = DB_Query($sql);
		
	// pobieranie podstawowych danych opisowych
	while ($row = DB_Fetch($result))
	{
		$entity_id = $row['entity_id'];
		$prod = entity_data('catalog_product', $entity_id, true);

		// dane rodzica
		$prod_parent = array();

		if ($options['no_variants'] and $row['parent_id'])
		{
			$prod_parent = entity_data('catalog_product', $row['parent_id'], true);
	}
		
		$p = array(
			'sku' => $prod['sku']['value'],
			'ean' => isset($prod['ean']['value']) ? $prod['ean']['value'] : '',
			'price' => $options['currency_rate']*($row['group_price'] ? $row['group_price'] : (($row['final_price'] and $options['special_price']) ? $row['final_price'] : $row['price'])),
			'weight' => $prod['weight']['value'],
			'description' => empty($prod['description']['value']) ? $prod_parent['description']['value'] : $prod['description']['value'],
			'description_extra1' => $prod['short_description']['value'],
			'images' => ($prod['image']['value'] == 'no_selection') ? array() : array($options['images_folder'] . ($prod['image']['value'] ? $prod['image']['value'] : ('placeholder/default/' . basename(current(glob(_store_root() . '/../media/catalog/product/placeholder/default/*.???')))))),
			'name' => $prod['name']['value'],
			'quantity' => (int)$row['qty'] * (($prod['status']['value'] == 1) ? 1 : 0),
			'man_name' => (string)$prod['manufacturer']['value'],
			'features' => array(),
			'variants' => array(),
		);

		// czasem cena przechowywana jest w atrybucie produktu
		if ($p['price'] == 0 and $prod['price']['value'] > 0)
		{
			$p['price'] = $prod['price']['value'];
		}

		// główny obrazek dziedziczony od rodzica
		if ($options['no_variants'] and empty($p['images']) and isset($prod_parent['image']))
		{
			$p['images'] = ($prod_parent['image']['value'] == 'no_selection') ? array() : array($options['images_folder'] . $prod_parent['image']['value']);
		}

		// cena promocyjna
		if ($options['special_price'] and !empty($prod['special_price']['value']))
		{
			if (empty($prod['special_from_date']['value']) or $prod['special_from_date']['value'] <= gmdate('Y-m-d H:i:s'))
			{
				if (empty($prod['special_to_date']['value']) or $prod['special_to_date']['value'] >= gmdate('Y-m-d H:i:s'))
				{
					$p['price'] = $options['currency_rate']*$prod['special_price']['value'];
				}
			}
		}
		
		// stawka vat
		$tax_class_id = (int)$prod['tax_class_id']['value'];
		$p['tax'] = tax_rate($tax_class_id);

		// doliczanie podatku do ceny
		if ($options['apply_tax'])
		{
			$p['price'] = number_format($p['price']*($p['tax']+100)/100, 2, '.', '');
		}

		// dodatkowe obrazki
		$sql = "SELECT DISTINCT value FROM `${dbp}catalog_product_entity_media_gallery` mg
			JOIN `${dbp}catalog_product_entity_media_gallery_value` mgv ON mg.value_id = mgv.value_id
			WHERE mg.entity_id = {0} AND mgv.disabled = 0 AND (store_id = {1} OR store_id = 0) ORDER by position";
		$res = DB_Query($sql, $entity_id, $options['store_id']);

		// nie ma obrazków dla wariantu (lub składnika bundle) - sprawdzamy produkt główny
		if (!DB_NumRows($res) and $row['parent_id'])
		{
			$res = DB_Query($sql, $row['parent_id'], $options['store_id']);
		}

		while ($img = DB_Fetch($res))
		{
			if (empty($p['images']) or $p['images'][0] != "${options['images_folder']}${img['value']}")
			{
				$p['images'][] = $options['images_folder'] . $img['value'];
			}
		}
		
		// pobranie nazwy i id kategorii
		// (jeśli kategoria nie wprowadzona dla produktu prostego, próbujemy pobrać z konfigurowalnego)
		$sql = "SELECT product_id, category_id id, position FROM `${dbp}catalog_category_product` 
			WHERE product_id = {0}
			UNION
			SELECT product_id, category_id id, position FROM `${dbp}catalog_category_product`
			WHERE product_id = {1}
			ORDER BY (product_id = {0}) DESC, position";
		$res = DB_Query($sql, $entity_id, (int)$row['parent_id']);

		while ($cat = DB_Fetch($res))
		{
			if (isset($category_tree[$cat['id']]))
			{
				$p['category_id'] = $cat['id'];
				$p['category_name'] = $category_tree[$cat['id']];
				break;
			}
		}

		// cechy produktu
		$selection = array(); // lista wszystkich id atrybutów z wybranych (options) grup atrybutów

		foreach (preg_split('/\s*,\s*/', trim($options['attribute_group'])) as $attr_grp)
		{
			$selection[] = "'$attr_grp'";
		}

		$selection = implode(', ', $selection);

		$sql = "SELECT DISTINCT eea.attribute_id id
			FROM `${dbp}eav_entity_type` eet
			JOIN `${dbp}eav_attribute_set` eas ON eet.entity_type_id = eas.entity_type_id
			JOIN `${dbp}eav_attribute_group` eag ON eag.attribute_set_id = eas.attribute_set_id
			JOIN `${dbp}eav_entity_attribute` eea ON eag.attribute_group_id = eea.attribute_group_id
			JOIN `${dbp}catalog_eav_attribute` cea ON cea.attribute_id = eea.attribute_id AND cea.is_visible_on_front
			WHERE eag.attribute_set_id = '{0}' AND eet.entity_type_code = 'catalog_product'
			ORDER BY eag.sort_order, eea.sort_order";
			$res = DB_Query($sql, $row['attribute_set_id']);

		$variant_feature_attrs = array(); // lista potencjalnych atrybutów zdefiniowanych na poziomie wariantu

		while ($attr = DB_Fetch($res))
		{
			if (!empty($prod[$attr['id']]) and !empty($prod[$attr['id']]['value']))
			{
				$p['features'][] = array($prod[$attr['id']]['name'], $prod[$attr['id']]['value']);
			}
			else
			{
				$variant_feature_attrs[] = $attr['id'];
			}
		}

		// warianty (w sklepie zdefiniowane jako dzieci produktu bazowego)
		if ($prod['has_options'] and $row['type_id'] != 'bundle')
		{
			$collate_qty = !$p['quantity']; // czy sumować stany magazynowe wariantów

			$sql = "SELECT child_id, qty*is_in_stock qty FROM `${dbp}catalog_product_relation` cpr
				LEFT JOIN `${dbp}cataloginventory_stock_item` csi ON csi.product_id = cpr.child_id
				WHERE parent_id = {0}";
			$res = DB_Query($sql, $entity_id);
		
			while ($variant = DB_Fetch($res))
			{
				$vdata = entity_data('catalog_product', $variant['child_id'], true);

				$v = array(
					'full_name' => $vdata['name']['value'], 'name' => $vdata['name']['value'], 'quantity' => (int)$variant['qty'] * (($vdata['status']['value'] == 1) ? 1 : 0),
					'price' => $vdata['final_price']['value'] ? $vdata['final_price']['value'] : $vdata['price']['value'],
					'ean' => $vdata['ean']['value'], 'sku' => $vdata['sku']['value'],
				);

				// cena promocyjna
				if ($options['special_price'] and !empty($vdata['special_price']['value']))
				{
					if (empty($vdata['special_from_date']['value']) or $vdata['special_from_date']['value'] <= gmdate('Y-m-d H:i:s'))
					{
						if (empty($vdata['special_to_date']['value']) or $vdata['special_to_date']['value'] >= gmdate('Y-m-d H:i:s'))
						{
							$v['price'] = $options['currency_rate']*$vdata['special_price']['value'];
						}
					}
				}

				if ($collate_qty)
						{
					$p['quantity'] += $v['quantity'];
					}

				if (strpos($v['name'], $p['name']) === 0) // jeśli nazwa wariantu zawiera nazwę produktu
				{
					$v['name'] = trim(substr($v['name'], strlen($p['name']))); // obetnij ją,
				}
				else // w innym razie
				{
					$v['full_name'] = $p['name'] . ' ' . $v['name']; // poprzedź nazwą produktu bazowego
				}

				// nazwa wariantu taka sama jak produktu konfigurowalnego
				// - musimy wygenerować nazwę wariantu z wartości superatrybutów
				if ($v['name'] == '' and $v['full_name'] == $p['name'])
				{
					$sql = "SELECT attribute_id
						FROM `${dbp}catalog_product_super_attribute`
						WHERE product_id = '{0}'
						ORDER BY position";
					$res2 = DB_Query($sql, $entity_id);

					while ($super = DB_Fetch($res2))
					{
						if (!empty($vdata[$super['attribute_id']]['value']))
						{
							$v['full_name'] .= ' ' . $vdata[$super['attribute_id']]['value'];
							$v['name'] = $vdata[$super['attribute_id']]['value'];
						}
					}
				}
		
				// cena promocyjna
				if ($options['special_price'] and !empty($vdata['special_price']['value']))
				{
					if (empty($vdata['special_from_date']['value']) or $vdata['special_from_date']['value'] <= gmdate('Y-m-d H:i:s'))
					{
						if (empty($vdata['special_to_date']['value']) or $vdata['special_to_date']['value'] >= gmdate('Y-m-d H:i:s'))
						{
							$v['price'] = $vdata['special_price']['value'];
						}
					}
				}

				$v['price'] *= $options['currency_rate'];

				// doliczanie podatku
				if ($options['apply_tax'])
				{
					$v['price'] = $v['price']*($p['tax']+100)/100;
		}

				$v['price'] = number_format($v['price'], 2, '.', '');

				// cechy specficzne dla wariantu
				$vfeatures = array();

				foreach ($variant_feature_attrs as $attr_id)
				{
					if (!empty($vdata[$attr_id]['value']))
					{
						$vfeatures[$vdata[$attr_id]['name']][] = $vdata[$attr_id]['value'];
					}
				}

				// grupowanie wartości multiselect, usuwanie duplikatów (ta sama cecha z różnych atrybutów)
				if (!empty($vfeatures))
				{
					$v['features'] = array();

					foreach ($vfeatures as $vfname => $vfvalues)
					{
						$v['features'][] = array($vfname, implode('|', array_unique($vfvalues)));
					}
				}

				// zdjęcia specyficzne dla wariantu
				$v['images'] = (!$vdata['image']['value'] or $vdata['image']['value'] == 'no_selection') ? array() : array($options['images_folder'] . $vdata['image']['value']);

				$sql = "SELECT DISTINCT value FROM `${dbp}catalog_product_entity_media_gallery` mg
					JOIN `${dbp}catalog_product_entity_media_gallery_value` mgv ON mg.value_id = mgv.value_id
					WHERE mg.entity_id = {0} AND mgv.disabled = 0 AND (store_id = {1} OR store_id = 0) ORDER by position";
				$res2 = DB_Query($sql, $variant['child_id'], $options['store_id']);

				while ($img = DB_Fetch($res2))
				{
					if (empty($v['images']) or $v['images'][0] != "${options['images_folder']}${img['value']}")
					{
						$v['images'][] = $options['images_folder'] . $img['value'];
					}
				}

				// uzupełnienie wagi wariantu (jeśli podano)
				if (!empty($vdata['weight']['value']))
				{
 					$v['weight'] = $vdata['weight']['value'];
				}

				$p['variants'][$variant['child_id']] = $v;
			}
		}

		// formatowanie ceny i stawki podatkowej
		$p['price'] = number_format($p['price'], 2, '.', '');
		$p['tax'] = number_format($p['tax'], 2, '.', '');

		// pobieranie wartości dodatkowych tagów z zewnętrznego skryptu
		// wszystkie pola tablicy $p mogą zostać użyte w szablonie aukcji: Nowe pole np. $p['test'] będzie dostępne w szablonie aukcji jako tag [test]
		// poniższy warunek może pozostać identyczny niezależnie od platformy sklepu
		if (file_exists('baselinker_extra.php'))
		{
			// pobranie dodatkowych informacji z zewnętrznego pliku pliku.
			// Plik tworzony jest indywidualnie dla każdego sprzedawcy jeśli zgłosi potrzebę pobierania dodatkowych danych ze sklepu.
			// Pozwala to uniknąć ingerowania w standardowy plik baselinker.php
			include('baselinker_extra.php');
		}
		
		// wyrzucanie niepotrzebnych wartości jeśli określono pola do pobrania
		// poniższy kod może pozostać identyczny niezależnie od platformy sklepu
		if(isset($request['fields']) and !(count($request['fields']) == 1 && $request['fields'][0] == '') && !count($request['fields']) == 0)
		{
			$temp_p = array();
			foreach($request['fields'] as $field)
			{$temp_p[$field] = $p[$field];}
			$p = $temp_p;
		}

		$response[$entity_id] = $p;
	}
	
	return $response;
}





 /**
 * Funkcja zwraca stan magazynowy wszystkich produktów i ich wariantów
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica ze stanem magazynowym wszystkich produktów, w formacie:
 * 		id produktu => ID produktu jest kluczem tablicy, wartością jest tablica składająca się ze stanów wariantów
 *                             id wariantu => kluczem tablicy jest ID wariantu (0 w przypadku produktu głównego)
 *                             stan => wartościa jest stan magazynowy
 *          Przykład: array('432' => array('0' => 4, '543' => 2, '567' => 3)) - produkt ID 432, stan głównego produktu to 4, posiada dwa warianty (ID 543 i 563) o stanach 2 i 3.
 */
function Shop_ProductsQuantity($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	$sql = "SELECT attribute_id
		FROM `${dbp}eav_attribute`
		WHERE entity_type_id = (SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'catalog_product')
		AND attribute_code = 'status'";
	$status_attr_id = DB_Result(DB_Query($sql));

	$response = array();

	$sql = "SELECT cpe.entity_id, cpr.child_id variant_id,
		csi_m.qty*csi_m.is_in_stock qty_m, csi_v.qty*csi_v.is_in_stock qty_v,
		if(isnull(cpesm.value), cpesm1.value, cpesm.value) status_m, if(isnull(cpesv.value), cpesv1.value, cpesv.value) status_v
		FROM `${dbp}catalog_product_entity` cpe
		LEFT JOIN `${dbp}catalog_product_relation` cpr ON cpr.parent_id = cpe.entity_id AND cpe.type_id <> 'bundle'
		LEFT JOIN `${dbp}cataloginventory_stock_item` csi_m ON csi_m.product_id = cpe.entity_id
		LEFT JOIN `${dbp}cataloginventory_stock_item` csi_v ON csi_v.product_id = cpr.child_id
		/* status */
		LEFT JOIN `${dbp}catalog_product_entity_int` cpesm ON cpesm.entity_id = cpe.entity_id AND cpesm.attribute_id = '$status_attr_id' AND cpesm.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_int` cpesm1 ON cpesm1.entity_id = cpe.entity_id AND cpesm1.attribute_id = '$status_attr_id' AND cpesm1.store_id = 0
		LEFT JOIN `${dbp}catalog_product_entity_int` cpesv ON cpesv.entity_id = cpr.child_id AND cpesv.attribute_id = '$status_attr_id' AND cpesv.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_int` cpesv1 ON cpesv1.entity_id = cpr.child_id AND cpesv1.attribute_id = '$status_attr_id' AND cpesv1.store_id = 0
		WHERE 1";

	$count_sql = "SELECT count(*) FROM `${dbp}catalog_product_entity` cpe"; 

	if ($options['no_variants'])
	{
		$sql .= " AND cpe.type_id <> 'configurable'";
		$sql = str_replace('ON cpr.parent_id = cpe.entity_id', 'ON cpr.parent_id = 0', $sql);
	       	$count_sql .= " WHERE cpe.type_id <> 'configurable'";
	}
	else
	{
		$count_sql .= " LEFT JOIN `${dbp}catalog_product_relation` cpr ON cpr.parent_id = cpe.entity_id AND cpe.type_id <> 'bundle'";
	}

	// stronicowanie
	$per_page = 10000;
	$pages = ceil((int)DB_Result(DB_Query($count_sql))/$per_page);

	if ($pages > 1)
	{
		$page = $request['page'] ? $request['page'] : 1;
		$sql .= " LIMIT " . (($page-1)*$per_page) . ", $per_page";
	}

	$result = DB_Query($sql, $options['store_id']);

	while ($prod = DB_Fetch($result))
	{
		if (!isset($response[$prod['entity_id']][0]))
		{
			$response[$prod['entity_id']][0] = (($prod['status_m'] == 1) ? 1 : 0)*(int)$prod['qty_m'];
		}

		if ($prod['variant_id'])
		{
			$response[$prod['entity_id']][$prod['variant_id']] = (($prod['status_v'] == 1) ? 1 : 0)*(int)$prod['qty_v'];

			if (!(int)$prod['qty_m']) // sumujemy stany wariantów jeśli produkt główny ma stan zero
			{
				$response[$prod['entity_id']][0] += (($prod['status_v'] == 1) ? 1 : 0)*(int)$prod['qty_v'];
			}
		}
	}

	if ($pages > 1)
	{
		$response['pages'] = $pages;
	}
	
	return $response;
}



 /**
 * Funkcja ustawia stan magazynowy wybranych produktów i ich wariantów
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu: 
 *		products => tablica zawierająca informacje o zmianach stanu produktu. Każdy element tablicy jest również tablicą składającą się z pól:
 *					product_id => ID produktu
 *					variant_id => ID wariantu (0 jeśli produkt główny)
 *					operation => rodzaj zmiany, dopuszczalne wartości to: 'set' (ustawia konkretny stan), 'change' (dodaje do stanu magazynowego, ujemna liczba w polu quantity zmniejszy stan o daną ilość sztuk, dodatnia zwiększy)
 *					quantity => zmiana stanu magazynowego (ilośc do ustawienia/zmniejszenia/zwiększenia zależnie od pola operation)
 * @return array $response tablica zawierajaca pole z ilością zmienionych produktów:
 * 		counter => ilość zmienionych produktów
 */
function Shop_ProductsQuantityUpdate($request)
{	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	$prod_type = array(); // typy produktów
	$prod_qty = array(); // stany magazynowe przed aktualizacją

	$sql = "SELECT value FROM `${dbp}core_config_data`
		WHERE path = 'cataloginventory/item_options/manage_stock'";
	$config_manage_stock = DB_Result(DB_Query($sql)) ? 1 : 0;

	foreach ($request['products'] as $prod)
	{
		$prod_qty[$prod['product_id']] = 0; // właściwa wartość będzie pobrana poniżej
		$prod_type[$prod['product_id']] = 'simple'; // domyślnie
	}

	// mapowanie typów produktów, pobieranie aktualnych stanów
	$sql = "SELECT entity_id, type_id, qty
		FROM `${dbp}catalog_product_entity` cpe
		LEFT JOIN `${dbp}cataloginventory_stock_item` csi ON cpe.entity_id = csi.product_id
		WHERE entity_id IN ({0})";
	$res = DB_Query($sql, implode(',', array_keys($prod_qty)));

	while ($prod = DB_Fetch($res))
	{
		$prod_type[$prod['entity_id']] = $prod['type_id'];
		$prod_qty[$prod['entity_id']] = $prod['qty'];
	}

		//ustawianie ilości bezwzględnie (dokładny nowy stan) lub względnie (zmniejszenie/zwiększenie)
	while ($prod = array_shift($request['products']))
	{
		// zestawy traktowane są inaczej
		if ($prod_type[$prod['product_id']] == 'bundle' and $prod['operation'] == 'change')
		{
			// pobieramy domyślne części składowe i ich ilości
			$sql = "SELECT product_id id, selection_qty qty
				FROM `${dbp}catalog_product_bundle_selection` cpbs
				JOIN `${dbp}catalog_product_entity` cpe ON cpbs.parent_product_id = cpe.entity_id
				WHERE cpe.entity_id = {0} AND cpe.type_id = 'bundle' AND is_default = 1";
			$res = DB_Query($sql, $prod['product_id']);

			while ($item = DB_Fetch($res))
			{
				// dodajemy do listy produktów (kolejki) z ilością pomnożoną przez ilość zestawów
				$request['products'][] = array('product_id' => $item['id'], 'quantity' => $item['qty']*$prod['quantity']);
				$prod_type[$item['id']] = 'simple';
			}

			if ($prod_qty[$prod['product_id']] <= 0)
			{
				continue; // stan produktu bundle nie ulegnie zmianie
			}
		}

		$sql = "UPDATE `${dbp}cataloginventory_stock_item` SET qty = {0} WHERE product_id = {1}";
		DB_Query($sql, (($prod['operation'] == 'set') ? '' : 'qty + ') . $prod['quantity'], $prod['variant_id'] ? $prod['variant_id'] : $prod['product_id']);
		// dostosuj flagę dostępności po zmianie
		DB_Query("UPDATE `${dbp}cataloginventory_stock_item` SET is_in_stock = 0, qty = 0 WHERE product_id = '{0}' AND qty <= 0 AND (manage_stock OR (use_config_manage_stock AND $config_manage_stock)) LIMIT 1", $prod['variant_id'] ? $prod['variant_id'] : $prod['product_id']);
	}
	
	return array('counter' => count($prod_qty));
}


 /**
 * Funkcja tworzy zamówienie w sklepie na podstawie nadesłanych danych
 * Jeśli funkcja otrzyuje na wejściu ID zamówienia, aktualizuje dane zamówienie zamiast tworzyć nowe
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, zawiera informacje o zamówieniu w formacie:
 *		previous_order_id => ID zamówienia (jeśli pierwszy raz dodawane do sklepu, wartość jest pusta. Jeśli było już wcześniej dodane, wartość zawiera poprzedni numer zamówienia)
 *		delivery_fullname, delivery_company, delivery_address, delivery_city, delivery_postcode, delivery_country => dane dotyczące adresu wysyłki
 *		invoice_fullname, invoice_company, invoice_address, invoice_city, invoice_postcode, invoice_country, invoice_nip => dane dotyczące adresu płatnika faktury
 *		phone => nr telefonu, email => adres email, 
 *		delivery_method => nazwa sposóbu wysyłki, delivery_method_id => numer ID sposobu wysyłki, delivery_price => cena wysyłki
 *		user_comments => komentarz kupującego, currency => waluta zamówienia, status_id => status nowego zamówienia
 *              change_products_quantity => flaga (bool) informująca, czy po stworzeniu zamówienia zmniejszony ma zostać stan zakupionych produktów 
 *		products => tablica z zakupionymi produktami w formacie:
 *				[] => 
 *						id => ID produktu
 *                                              variant_id => ID wariantu
 *						name => nazwa produktu (używana jeśli nie można pobrać jej z bazy na podstawie id)
 *						price => cena brutto w PLN
 *						currency => waluta
 *						quantity => zakupiona ilość
 *						attributes => tablica z atrybutami produktu w formacie:
 *									[] =>
 *											name =>	nazwa atrybutu (np. "kolor")
 *											value => wartość atybutu (np. "czerwony")
 *											price => różnica ceny dla tego produktu (np. "-10.00")
 *													 zmiana ceny jest już uwzględniona w cenie produktu
 * @return array $response tablica zawierająca numer nowego zamówienia:
 * 		'order_id' => numer utworzonego zamówienia
 */
function Shop_OrderAdd($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	// walidacja listy produktów
	$order_pids = array();

	foreach ($request['products'] as $op)
	{
		$order_pids[$op['variant_id'] ? $op['variant_id'] : $op['id']] = 1;
	}

	if (DB_Result(DB_Query("SELECT count(*) FROM `${dbp}catalog_product_entity` WHERE entity_id IN (" . implode(', ', array_keys($order_pids)) . ")")) < count($order_pids))
	{
		Conn_Error('unknown_product', 'Przynajmniej jeden z produktów nie jest zdefiniowany w sklepie');
	}
	// koniec walidacji

	$customer_entity_type_id = 1;

	if ($request['invoice_fullname'] == '')
	{
		$request['invoice_fullname'] = $request['delivery_fullname'];
	}

	$sql = "SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'customer' LIMIT 1";

	if ($res = DB_Query($sql))
	{
		$customer_entity_type_id = DB_Result($res, 0);
	}

	// sprawdzamy klienta po adresie email
	$new_customer = false;

	if ($options['create_customer'])
	{
		$customer_id = 0;
		$sql = "SELECT entity_id, group_id FROM `${dbp}customer_entity` WHERE email = '{0}'";
		$res = DB_Query($sql, $request['email']);

		if ($cust = DB_Fetch($res))
		{
			$customer_id = $cust['entity_id'];
			$options['customer_group_id'] = $cust['group_id'] ? $cust['group_id'] : $options['customer_group_id'];
		}

		if (!$customer_id)
		{
			// dodajemy nową osobę:
			$group_id = (int)$options['customer_group_id'];

			$sql = "INSERT INTO `${dbp}customer_entity` (entity_type_id, attribute_set_id, website_id, email, group_id,
				increment_id, store_id, created_at, updated_at, is_active)
				VALUES
				({0}, 0, '{5}', '{1}', {2}, '', '{3}', '{4}', '{4}', 1)";

			if ($res = DB_Query($sql, $customer_entity_type_id, $request['email'], $group_id, $options['store_id'], gmdate('Y-m-d H:i:s'), $options['website_id']))
			{
				$customer_id = DB_Identity($res);
				$new_customer = true;
			}
		}
	}

	if ($customer_id and $options['create_customer']) // udało się odnaleźć lub dodać klienta
	{
		if ($new_customer)
		{
			//wyciąganie imienia i nazwiska
			$invoice_fullname_exp = explode(' ', $request['invoice_fullname']);
			$invoice_lastname = array_pop($invoice_fullname_exp);
			$invoice_firstname = implode(' ', $invoice_fullname_exp);

		$attr_values = array('firstname' => $invoice_firstname, 'lastname' => $invoice_lastname, 'taxvat' => $request['invoice_nip']);

		$sql = "SELECT attribute_id, attribute_code, backend_type FROM `${dbp}eav_attribute`
			WHERE entity_type_id = {0} AND attribute_code IN ('" . implode("', '", array_keys($attr_values)) . "')";
		$res = DB_Query($sql, $customer_entity_type_id);

		while ($attr = DB_Fetch($res))
			{
				if (empty($attr_values[$attr['attribute_code']]))
				{
					continue;
				}

				$sql = "INSERT INTO `${dbp}customer_entity_{0}` (entity_type_id, attribute_id, entity_id, value)
					VALUES ({1}, {2}, {3}, '{4}') ON DUPLICATE KEY UPDATE value = '{4}'";
				DB_Query($sql, $attr['backend_type'], $customer_entity_type_id, $attr['attribute_id'], $customer_id, $attr_values[$attr['attribute_code']]);
			}
		}

		// aktualizacja adresów
		$address_entity_type_id = 2;

		$sql = "SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'customer_address' LIMIT 1";

		if ($res = DB_Query($sql))
		{
			$address_entity_type_id = DB_Result($res, 0);
		}

		$addr_map = array('default_billing' => 'invoice', 'default_shipping' => 'delivery');

		$sql = "SELECT attribute_id, attribute_code FROM `${dbp}eav_attribute`
			WHERE entity_type_id = {0} AND attribute_code IN ('" . implode("', '", array_keys($addr_map)) . "') ORDER BY attribute_code";
		$res = DB_Query($sql, $customer_entity_type_id);

		while ($addr_type = DB_Fetch($res)) // dla każdego rodzaju adresu
		{
			// ... mapujemy pola magento do wartości z BL ...
			$atype = $addr_map[$addr_type['attribute_code']];

			$address_on_file = false;	// adres jest już zapisany w sklepie
			$address_is_default = false;	// adres jest domyślnym adresem danego typu

			// sprawdzamy wszystkie adresy klienta
			$sql = "SELECT entity_id FROM `${dbp}customer_address_entity` WHERE parent_id = '{0}'";
			$res2 = DB_Query($sql, $customer_id);

			while ($cust_addr = DB_Fetch($res2))
			{
				$addr_data = entity_data('customer_address', $cust_addr['entity_id']);

				// adres taki sam jak w zamówieniu
				if ($addr_data['street'] == $request["${atype}_address"] and $addr_data['postcode'] == $request["${atype}_postcode"])
				{
					$address_on_file = $cust_addr['entity_id'];
				}
			}

			$sql = "SELECT value FROM `${dbp}customer_entity_int` WHERE entity_id = {0} AND attribute_id = {1} LIMIT 1";
			$res2 = DB_Query($sql, $customer_id, $addr_type['attribute_id']);

			if ($def_addr_id = DB_Result($res2, 0)) // domyślny adres istnieje
			{
				if ($def_addr_id == $addess_on_file)
				{
					$address_is_default = true;
				}
			}

			if ($address_on_file) 
			{
				$addr_id = $address_on_file;
			} 
			else // dodajemy nowy adres
			{
				$sql = "INSERT INTO `${dbp}customer_address_entity` (entity_type_id, attribute_set_id, increment_id,
					parent_id, created_at, updated_at, is_active)
					VALUES ({0}, 0, '', {1}, '{2}', '{2}', 1)";
				$res2 = DB_Query($sql, $address_entity_type_id, $customer_id, gmdate('Y-m-d H:i:s'));
				$addr_id = DB_Identity($res2);
			}

			// jeśli klient nie posiada domyślnego adresu, odnotowujemy ten dodany przed chwilą
			if (!$def_addr_id)
			{
				$sql = "INSERT IGNORE INTO `${dbp}customer_entity_int` (entity_type_id, attribute_id, entity_id, value)
					VALUES ({0}, {1}, {2}, {3})";
				DB_Query($sql, $customer_entity_type_id, $addr_type['attribute_id'], $customer_id, $addr_id);
			}

			$address_id[$atype] = $addr_id;

			if (!$address_on_file) // dane adresowe wymagają zapisania w bazie
			{
				$fullname_exp = explode(' ', $request["{$atype}_fullname"]);
				$lastname = array_pop($fullname_exp);
				$firstname = implode(' ', $fullname_exp);

				$attr_values = array('firstname' => $firstname, 'lastname' => $lastname, 'company' => $request["${atype}_company"],
						'street' => $request["${atype}_address"], 'city' => $request["${atype}_city"], 'postcode' => $request["${atype}_postcode"],
						'telephone' => $request['phone'], 'email' => $request['email'], 'vat_id' => $request['invoice_nip']);

			$attr_values['country_id'] = empty($request["${atype}_country_code"]) ? 'PL' : $request["${atype}_country_code"];

			$sql = "SELECT attribute_id, attribute_code, backend_type FROM `${dbp}eav_attribute`
				WHERE entity_type_id = {0} AND attribute_code IN ('" . implode("', '", array_keys($attr_values)) . "')";
			$res2 = DB_Query($sql, $address_entity_type_id);

			// ... i aktualizujemy po kolei elementy adresu
			while ($attr = DB_Fetch($res2))
				{
					if ($attr['attribute_code'] == 'vat_id' and (!$request['invoice_nip'] or $atype != 'invoice'))
					{
						continue;
					}

					$sql = "INSERT INTO `${dbp}customer_address_entity_{0}` (entity_type_id, attribute_id, entity_id, value)
						VALUES ({1}, {2}, {3}, '{4}') ON DUPLICATE KEY UPDATE value = '{4}'";
					DB_Query($sql, $attr['backend_type'], $address_entity_type_id, $attr['attribute_id'], $addr_id, $attr_values[$attr['attribute_code']]);
				}
			}
		}
	}

	// pobieranie formatu numeru zamówienia/faktury
	$sql = "SELECT path, value
		FROM `${dbp}core_config_data`
		WHERE path like 'amnumber%' 
		ORDER BY (scope_id = '{0}' and scope = 'stores') DESC, (scope_id = '{1}' and scope = 'websites') DESC";
	$res = DB_Query($sql, $options['store_id'], $options['website_id']);
	$amnumber = array();

	while ($conf = DB_Fetch($res))
	{
		if (!isset($amnumber[$conf['path']]))
		{
			$amnumber[$conf['path']] = $conf['value'];
		}
	}

	// jeśli zamówienie jest ponownie dodawane do bazy sklepu, wcześniejsze dane są usuwane
	// przy ponownym dodawaniu zamówienia (aktualizowaniu), $request['previous_order_id'] zawiera poprzedni numer danego zamówienia w sklepie
	if ($request['previous_order_id'] != "")
	{
		if (isset($amnumber['amnumber/order/format']))
		{
			$sql = "SELECT increment_id, quote_id, store_id, creaed_at, customer_group_id FROM ${dbp}sales_flat_order WHERE entity_id = '{0}' LIMIT 1";
			$res = DB_Query($sql, $request['previous_order_id']);

			if ($order = DB_Fetch($res))
			{
				$increment_id = $order['increment_id'];
				$quote_id = $order['quote_id'];
				$options['store_id'] = $order['store_id'] ? $order['store_id'] : $options['store_id'];
				$created_date = $order['created_at'];
				$options['customer_group_id'] = $order['customer_group_id'];
			}
		}
		else
		{
			$sql = "SELECT entity_id, quote_id, store_id, created_at, customer_group_id FROM ${dbp}sales_flat_order WHERE increment_id  regexp '^0*{0}\$' LIMIT 1";
			$res = DB_Query($sql, $request['previous_order_id']);
			$increment_id = $request['previous_order_id'];

			if ($order = DB_Fetch($res))
			{
				$request['previous_order_id'] = $order['entity_id'];
				$quote_id = $order['quote_id'];
				$options['store_id'] = $order['store_id'] ? $order['store_id'] : $options['store_id'];
				$created_date = $order['created_at'];
				$options['customer_group_id'] = $order['customer_group_id'];
			}
		}

		// rejestracja zamówionych ilości w dotychczasowej wersji zamówienia
		if ($options['oa_stock_refresh'])
		{		
			$sql = "SELECT product_id, qty_ordered FROM `${dbp}sales_flat_order_item` WHERE order_id = '{0}'";
			$prev_prod_qties = array();
			$res = DB_Query($sql, $request['previous_order_id']);

			while ($op = DB_Fetch($res))
			{
				if ($op['product_id'])
				{
					$prev_prod_qties[$op['product_id']] += $op['qty_ordered'];
				}
			}
		}
	
		DB_Query("DELETE FROM `${dbp}sales_flat_order` WHERE `entity_id` = '{0}'", $request['previous_order_id']);
		DB_Query("DELETE FROM `${dbp}sales_flat_order_address` WHERE `parent_id` = '{0}'", $request['previous_order_id']);
		DB_Query("DELETE FROM `${dbp}sales_flat_order_item` WHERE `order_id` = '{0}'", $request['previous_order_id']);
		DB_Query("DELETE FROM `${dbp}sales_flat_order_payment` WHERE `parent_id` = '{0}'", $request['previous_order_id']);
		DB_Query("DELETE FROM `${dbp}sales_flat_order_grid` WHERE `entity_id` = '{0}'", $request['previous_order_id']);
		DB_Query("DELETE FROM `${dbp}sales_flat_order_status_history` WHERE `parent_id` = '{0}'", $request['previous_order_id']);
		DB_Query("DELETE FROM `${dbp}sales_order_tax` WHERE `order_id` = '{0}'", $request['previous_order_id']);
		DB_Query("DELETE FROM `${dbp}sales_flat_quote_item_option` WHERE `item_id` IN (SELECT item_id FROM `${dbp}sales_flat_quote_item` WHERE quote_id = '{0}')", $quote_id);
		DB_Query("DELETE FROM `${dbp}sales_flat_quote_item` WHERE `quote_id` = '{0}'", $quote_id);
		DB_Query("DELETE FROM `${dbp}sales_flat_quote_address` WHERE `quote_id` = '{0}'", $quote_id);
		DB_Query("DELETE FROM `${dbp}sales_flat_quote` WHERE `entity_id` = '{0}'", $quote_id);
	}
	else
	{
		$inc = 0;

		while ($inc++ < 100)
		{
		// pobieramy kolejny numer zamówienia dla wybranego sklepu
			$sql = "SELECT increment_last_id+{0}
				FROM `${dbp}eav_entity_store`
				WHERE entity_type_id = (SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'order')
				AND store_id = {1} LIMIT 1";
			$res = DB_Query($sql, $inc, $options['store_id']);
			$increment_id = DB_Result($res);

			// sprawdzamy czy ten numer nie jest zajęty
			if (!DB_Result(DB_Query("SELECT count(*) FROM `${dbp}sales_flat_order` WHERE store_id = {0} AND increment_id regexp '^0*{1}\$'", $options['store_id'], $increment_id)))
			{
				// aktualizujemy eav_entity_store
				DB_Query("UPDATE `${dbp}eav_entity_store` SET increment_last_id = increment_last_id+{0} WHERE store_id = {1} AND entity_type_id = (SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'order') LIMIT 1", $inc, $options['store_id']);
				break; // pobrany numer jest OK - nie trzeba sprawdzać następnego z rzędu
			}
		}
	}
	
	//pobieranie imienia i nazwiska
	$delivery_data = explode(' ', $request['delivery_fullname']);
	$delivery_lastname = array_pop($delivery_data);
	$delivery_firstname = implode(' ', $delivery_data);
	$invoice_data = explode(' ', $request['invoice_fullname']);
	$invoice_lastname = array_pop($invoice_data);
	$invoice_firstname = implode(' ', $invoice_data);
	
	//formatownie cen
	$request['delivery_price'] = number_format($request['delivery_price'], 2, ".", "");
	$date = gmdate("Y-m-d H:i:s");
	$created_date = $request['previous_order_id'] ? $created_date : $date;

	$delivery_methods = Shop_DeliveryMethodsList($request);
	$shipping_method = $request['delivery_method_id'] ? $request['delivery_method_id'] : 'flatrate';
	$shipping_description = $delivery_methods[(string)$request['delivery_method_id']] ? $delivery_methods[(string)$request['delivery_method_id']] : $request['delivery_method'];
	$shipping_tax = number_format($request['delivery_price']-$request['delivery_price']/(1+$options['shipping_tax_rate']/100), 4, '.', '');

	//dodanie zamowienia do tabeli sales_flat_order
	$sql = "INSERT INTO `${dbp}sales_flat_order` 
		(`entity_id`, `state`, `status`, `protect_code`, `shipping_description`, `base_shipping_amount`, `shipping_amount`, 
		`customer_email`, `store_currency_code`, `created_at`, `customer_firstname`, `customer_lastname`, `is_virtual`, `store_id`,
		`base_to_global_rate`, `base_to_order_rate`, `store_to_order_rate`, `store_to_base_rate`, `customer_is_guest`, `customer_note_notify`, `customer_group_id`,
		`increment_id`, `base_currency_code`, `global_currency_code`, `order_currency_code`, `store_name`, `shipping_tax_amount`, `base_shipping_tax_amount`, `customer_id`, `shipping_method`,
		`customer_taxvat`)
			VALUES 
		({0}, 'new', '{12}', '123456', '{1}', '{2}', '{2}', '{3}', '{9}', '{4}', '{5}', '{6}', '0', '{7}',
		'1', '1', '1', '1', '1', '0', '{13}', '{8}', '{9}', '{9}', '{9}', 'Allegro', '{15}', '{15}', {10}, '{11}',
		'{14}')";	
	DB_Query($sql, $request['previous_order_id'] ? $request['previous_order_id'] : 'null', $shipping_description,
		$request['delivery_price']-$shipping_tax,
		$request['email'], $created_date, $delivery_firstname, $delivery_lastname, $options['store_id'], $increment_id, $request['currency'] ? $request['currency'] : 'PLN', $customer_id ? $customer_id : 'null',
		$shipping_method . '_' . $shipping_method, $request['status_id'] ? $request['status_id'] : 'pending', (int)$options['customer_group_id'], $request['invoice_nip'], $shipping_tax);
	$this_order_id = DB_Identity(); //pobieranie numeru nowego zamówienia 

	//komentarz kupującego
	$sql = "INSERT INTO `${dbp}sales_flat_order_status_history` (parent_id, is_customer_notified, is_visible_on_front, comment, status, created_at"
		. (($options['magento_version'] > '01050001') ? ', entity_name' : '') . ")
		VALUES ({0}, 0, 0, '{1}', '{2}', '{3}'" . (($options['magento_version'] > '01050001') ? ", 'order'" : '') . ")";
	DB_Query($sql, $this_order_id, $request['user_comments'], $request['status_id'] ? $request['status_id'] : 'pending', gmdate('Y-m-d H:i:s'));
	
	//dodanie informacji o zamowieniu do tabeli sales_flat_order_payment
	$sql = "INSERT INTO `${dbp}sales_flat_order_payment`
				(`parent_id`, `base_shipping_amount`, `shipping_amount`, `cc_exp_month`, `cc_ss_start_year`, `method`,
				`cc_ss_start_month`, `cc_exp_year`)
		VALUES ('{0}', '{1}', '{2}', '0', '0', '{3}', '0', '0')";
	DB_Query($sql, $this_order_id, $request['delivery_price'], $request['delivery_price'], $request['payment_method_id'] ? $request['payment_method_id'] : ($request['payment_method_cod'] ? 'cashondelivery' : 'checkmo'));
	
	//adres dostawy
	$sql = "INSERT INTO `${dbp}sales_flat_order_address`
				(`parent_id`, `postcode`, `lastname`, `street`, `city`, `email`, `telephone`, `country_id`, `firstname`, `address_type`, `company`, `customer_id`, `customer_address_id`)
		VALUES ('{0}', '{1}', '{2}', '{3}', '{4}', '{5}', '{6}', '{7}',  '{8}', 'shipping', '{9}', {10}, '{11}');";
	DB_Query($sql, $this_order_id, $request['delivery_postcode'], $delivery_lastname, $request['delivery_address'],
                $request['delivery_city'], $request['email'], $request['phone'], $request['delivery_country_code'], $delivery_firstname, $request['delivery_company'], $customer_id ? $customer_id : 'null', $address_id['delivery']);
	$id_delivery = DB_Identity();

	//adres faktury
	$sql = "INSERT INTO `${dbp}sales_flat_order_address`
				(`parent_id`, `postcode`, `lastname`, `street`, `city`, `email`, `telephone`, `country_id`, `firstname`, `address_type`, `company`, `customer_id`, `customer_address_id`, `vat_id`)
		VALUES ('{0}', '{1}', '{2}', '{3}', '{4}', '{5}', '{6}', '{7}',  '{8}', 'billing', '{9}', {10}, '{11}', '{12}');";
	DB_Query($sql, $this_order_id, $request['invoice_postcode'], $invoice_lastname, $request['invoice_address'],
                $request['invoice_city'], $request['email'], $request['phone'], $request['invoice_country_code'],  $invoice_firstname, $request['invoice_company'], $customer_id ? $customer_id : 'null', $address_id['invoice'], $request['invoice_nip']);
	$id_billing = DB_Identity();

	//obsługa produktów w zamówieniu
	$sum_products_price = 0; //cena wszystkich produktow zamowienia
	$sum_price_netto = 0; //cena netto wszystkich produktow zamowienia
	$sum_product_qty = 0; //ilosc produktow zamowienia
	$sum_weight = 0; //laczna waga produktow
	$total_item_count = count($request['products']); //liczba produktów zamówienia (line items)
	$sum_products_tax = 0; //podatek od wszystkich produktów zamówienia
	$fix_shipping_tax = true; //czy przeliczyć koszty wysyłki wg stawki VAT dla produktów
	$tax_matrix = array(); // podatek dla produktów wg stawek podatkowych
	
	while ($prod = array_shift($request['products']))
	{
		$entity_id = (int)($prod['variant_id'] ? $prod['variant_id'] : $prod['id']);
		$prod_data = entity_data('catalog_product', $entity_id);

		// jeśli produkt jest typu `bundle`, pobierz jego składniki
		if ($prod_data['type_id'] == 'bundle')
		{
			$bundle_options = array();
			$bundle_selections = array();
			$children = array();

			$sql = "SELECT opts.*, cpbov.title, cpbs.selection_qty, cpbs.selection_price_value, cpbs.selection_id
				FROM
				(SELECT cpbs.option_id, cpbs.product_id FROM `${dbp}catalog_product_bundle_selection` cpbs
				JOIN `${dbp}catalog_product_entity` cpe ON cpbs.parent_product_id = cpe.entity_id 
				WHERE cpe.entity_id = {0} AND cpe.type_id = 'bundle'
				GROUP BY option_id, is_default DESC, position) opts
				JOIN `${dbp}catalog_product_bundle_option_value` cpbov ON cpbov.option_id = opts.option_id
				LEFT JOIN `${dbp}catalog_product_bundle_selection` cpbs ON cpbs.option_id = opts.option_id
				GROUP BY opts.option_id";
			$res = DB_Query($sql, $entity_id);

			while ($child = DB_Fetch($res))
			{
				$child_prod_data = entity_data('catalog_product', $child['product_id']);
				$children[] = $child['product_id'];

				array_unshift($request['products'], array(
					'id' => $child['product_id'],
					'quantity' => $prod['quantity'],
					'price' => 0,
					'parent' => $entity_id,
					'product_options' => array(
						'info_buyRequest' => array(),
						'bundle_selection_attributes' => serialize(
							array(	
								'price' => 1*number_format((float)$child['selection_price_value'], 2, '.', ''),
								'qty' => 1*$child['selection_qty'],
								'option_label' => $child['title'],
								'option_id' => $child['option_id'],
							)

						),
					),
				));

				$bundle_selections[$child['option_id']] = $child['selection_id'];

				$bundle_options[$child['option_id']] = array(
					'option_id' => $child['option_id'],
					'label' => $child['title'],
					'value' => array(
						array(
							'title' => $child_prod_data['name'],
							'qty' => ''.((int)$child['selection_qty']),
							'price' => 1*number_format((float)$child['selection_price_value'], 2, '.', ''),
						)
					)
				);
			}

			$prod['product_options'] = array(
				'info_buyRequest' => array(
					'qty' => 1*$prod['quantity'],
					//'product' => $prod['id'],
					'bundle_option' => $bundle_selections,
				//	'options' => array(),
				),
				'bundle_options' => $bundle_options,
				'product_calculations' => (int)1,
				'shipment_type' => "0",
			);

			foreach ($children as $pid)
			{
				foreach ($request['products'] as $pidx => $pdata)
				{
					if ($pdata['id'] == $pid)
					{
						$request['products'][$pidx]['product_options']['info_buyRequest'] = array(
							'qty' => 1*$prod['quantity'],
							//'product' => $prod['id'],
							'bundle_option' => $bundle_selections,
							//'options' => array(),
						);

						break;
					}
				}
			}
		}
		
		$prod_data['weight'] = (float)$prod_data['weight'];

		// stawka podatku				
		$tax_rate = number_format(tax_rate($prod_data['tax_class_id']) ? tax_rate($prod_data['tax_class_id'], $request['delivery_country_code']) : $options['def_tax'], 2, '.', '');
		
		//aktualizowanie zmiennych licząch sumy
		$tax_rate = ($tax_rate!="")?number_format($tax_rate, 2):"0.00";
		$price_netto = $prod['price'] / (1 + $tax_rate/100);
		$tax_amount = ($prod['price'] - $price_netto) * $prod['quantity'];
		$price_netto_quantity = $price_netto * $prod['quantity'];

		$row_total_incl_tax = null;
		$base_row_total_incl_tax = null;

		if (!$prod['parent'])
		{
			$sum_products_price += $prod['price'] * $prod['quantity'];
			$sum_price_netto += $price_netto * $prod['quantity'];
			$sum_products_tax += $tax_amount;
			$total_weight = $prod_data['weight'] * $prod['quantity'];
			$row_total_incl_tax = $prod['price'] * $prod['quantity'];
			$base_row_total_incl_tax = $prod['price'] * $prod['quantity'];
			$sum_product_qty += $prod['quantity'];
			$sum_weight  += $total_weight;
		}
		else // składniki budnle
		{
			$prod['price'] = null;
			$price_netto = null;
			$price_netto_quantity = null;
		}
		
		if ($tax_rate == $options['shipping_tax_rate'])
		{
			$fix_shipping_tax = false;
		}
		
		//wybieranie nazwy z bazy lub nadesłanej
		if($prod_data['name'] == "")
		{$prod_data['name'] = $prod['name'];}
		
		// Próba pobrania SKU z katalogu produktów, jeśli nie podane przez BaseLinker'a
		if (empty($prod_data['sku']))
		{
			$sql = "SELECT sku FROM `${dbp}catalog_product_entity` WHERE entity_id = '{0}' LIMIT 1";

            if ($sku = DB_Query($sql, $entity_id))
			{
                $prod_data['sku'] = DB_Result($sku);
			}
		}
		
		// dodatkowe parametry produktu (puste)
		$product_options = empty($prod['product_options']) ? array() : $prod['product_options'];
		
		//dodawanie produktu do zamowienia
		$sql = "INSERT INTO `${dbp}sales_flat_order_item` 
				(`order_id` , `product_id`, `name` , `original_price` , `qty_ordered`, 
				`price`, `base_price`, `base_original_price`, `tax_percent`, `tax_amount`, `base_tax_amount`,
				`row_total`, `base_row_total`, `weight`, `row_weight`, `price_incl_tax`, `base_price_incl_tax`,
				`row_total_incl_tax`, `base_row_total_incl_tax`, `store_id`, `product_type`, `is_virtual`, `is_qty_decimal`,
				`sku`, `parent_item_id`, `product_options`, `created_at`, `updated_at`)
			VALUES ('{0}', '{1}', '{2}', '{3}', '{4}', '{5}', '{6}', '{7}', '{8}', '{9}', '{10}',
			'{11}', '{12}', '{13}', '{14}', '{15}', '{16}', '{17}', '{18}', '{19}', '{20}', '0', '0',
			'{21}', {22}, '{23}', '{24}', '{24}');";
		DB_Query($sql, $this_order_id, $entity_id, $prod_data['name'], $prod['price'], $prod['quantity'],
                        $price_netto, $price_netto, $prod['price'], $tax_rate, $tax_amount, $tax_amount,
                        $price_netto_quantity, $price_netto_quantity, $prod_data['weight'], $total_weight, $prod['price'], $prod['price'],
                        $row_total_incl_tax, $base_row_total_incl_tax, $options['store_id'], $prod_data['type_id'] ? $prod_data['type_id'] : 'simple',
			$prod_data['sku'] ? $prod_data['sku'] : $prod['sku'] , $prod['parent'] ? $prod['parent'] : 'NULL', serialize($product_options), gmdate('Y-m-d H:i:s'));
		$this_order_products_id = DB_Identity();

		if (!$prod['parent'])
		{		
			$tax_matrix[$tax_rate][$this_order_products_id] = $tax_amount;
		}

		if ($options['oa_stock_refresh'] and $prev_prod_qties)
		{
			$sql = "SELECT value FROM `${dbp}core_config_data`
				WHERE path = 'cataloginventory/item_options/manage_stock'";
			$config_manage_stock = DB_Result(DB_Query($sql)) ? 1 : 0;

			foreach ($prev_prod_qties as $_prod_id => $_qty)
			{
				// oddajemy do magazynu
				DB_Query("UPDATE `${dbp}cataloginventory_stock_item` SET qty = qty + $_qty WHERE product_id = $_prod_id LIMIT 1");
				// ustawiamy flagę dostępności
				DB_Query("UPDATE `${dbp}cataloginventory_stock_item` SET is_in_stock = 1 WHERE product_id = '{0}' AND qty > 0 AND (manage_stock OR (use_config_manage_stock AND $config_manage_stock)) LIMIT 1", $_prod_id);
			}

			unset($prev_prod_qties); // wszystkie produkty "oddane" w tej pierwszej iteracji
		}
	
                //zmniejszanie stanu magazynowego produktu (jeśli ustawiona flaga change_products_quantity)
       	        if (($request['previous_order_id'] == '' and $request['change_products_quantity']) or $options['oa_stock_refresh'])
		{
			Shop_ProductsQuantityUpdate(array('products' => array(array('product_id' => $entity_id, 'variant_id' => 0, 'operation' => 'change', 'quantity' => -1*$prod['quantity']))));
		}

		// dla produktów bundle, uzupełniamy id_rodzica w danych dzieci
		// (zamiana product_id na line_item_id)
		if ($prod_data['type_id'] == 'bundle')
		{
			foreach ($request['products'] as $i => $prod)
			{
				if ($prod['parent'] == $entity_id)
				{
					$request['products'][$i]['parent'] = $this_order_products_id;
				}
			}
		}
	}

	if ($fix_shipping_tax)
	{
		$shipping_tax = number_format($request['delivery_price']-$request['delivery_price']/(1+$tax_rate/100), 4, '.', '');

		$sql = "UPDATE `${dbp}sales_flat_order`
			SET shipping_tax_amount = '{0}', base_shipping_tax_amount = '{0}',
			shipping_amount = '{1}', base_shipping_amount = '{1}'
			WHERE entity_id = '{2}'";
		DB_Query($sql, $shipping_tax, $request['delivery_price']-$shipping_tax, $this_order_id);
	}

	//formatowanie cen
	$sum_products_price = number_format($sum_products_price, 2, ".", "");
	$base_tax_amount = $sum_products_price - $sum_price_netto /* podatek od produktów */
	/* + podatek od wysyłki */ + $shipping_tax;
	$total_price = number_format($sum_products_price + $request['delivery_price'], 2, ".", "");
	
	// increment_id generowany według ustalonego wzoru
	if (isset($amnumber['amnumber/order/counter']))
	{
		if ($request['previous_order_id'])
		{
			// nie robimy nic - increment_id został pobrany wcześniej
		}
		else
		{
			$sql = "SELECT value + 1 FROM `${dbp}core_config_data`
				WHERE path = 'amnumber/order/counter'
				ORDER BY (scope_id = '{0}' and scope = 'stores') DESC, (scope_id = '{1}' and scope = 'websites') DESC
				LIMIT 1";

			if ($counter = (int)DB_Result(DB_Query($sql, $options['store_id'], $options['website_id'])))
			{
				$sql = "UPDATE `${dbp}core_config_data`
					SET value = {2}
					WHERE path = 'amnumber/order/counter'
					ORDER BY (scope_id = '{0}' and scope = 'stores') DESC, (scope_id = '{1}' and scope = 'websites') DESC
					LIMIT 1";
				DB_Query($sql, $options['store_id'], $options['website_id'], $counter);
				$amnumber['amnumber/order/counter'] = $counter;
			
				if (isset($amnumber['amnumber/order/format']))
				{
					$increment_id = $amnumber['amnumber/order/format'];
					// wypełnianie kolejnych tagów
					$increment_id = str_replace('{yy}', gmdate('Y'), $increment_id);
					$increment_id = str_replace('{mm}', gmdate('m'), $increment_id);
					$increment_id = str_replace('{dd}', gmdate('d'), $increment_id);
					$increment_id = str_replace('{store}', $options['store_id'], $increment_id);

					// licznik faktur
					if (strpos($increment_id, '{counter}') !== false)
					{
						if (isset($amnumber['amnumber/order/pad']))
						{
							$counter = sprintf("%0${amnumber['amnumber/order/pad']}d", $counter);
						}

						$increment_id = str_replace('{counter}', $counter, $increment_id);
					}
				}
			}
		}
	}

	$customer_tax_class_id = (int)DB_Result(DB_Query("SELECT class_id FROM `${dbp}tax_class` WHERE class_type = 'CUSTOMER'"));

	// koszyk (trochę w odwrotnej kolejności, bo z danych zapisanego już zamówienia)
	$sql = "INSERT INTO `${dbp}sales_flat_quote` (
		store_id, created_at, updated_at, is_active, items_count, items_qty,
		store_to_base_rate, store_to_quote_rate, base_currency_code, store_currency_code, quote_currency_code,
		grand_total, base_grand_total, customer_id, customer_tax_class_id, customer_group_id,
		customer_email, customer_firstname, customer_lastname, customer_note_notify, customer_is_guest,
		global_currency_code, base_to_global_rate, base_to_quote_rate, subtotal, base_subtotal,
		subtotal_with_discount, base_subtotal_with_discount, is_changed)
		SELECT
		store_id, created_at, updated_at, 1, '{1}', total_qty_ordered,
                1, 1, base_currency_code, store_currency_code, base_currency_code,
                grand_total, base_grand_total, customer_id, '{2}', customer_group_id,
                customer_email, customer_firstname, customer_lastname, customer_note_notify, customer_is_guest,
                global_currency_code, base_to_global_rate, 1, subtotal, base_subtotal,
                subtotal, base_subtotal, 0
		FROM `${dbp}sales_flat_order` WHERE entity_id = '{0}'";
	DB_Query($sql, $this_order_id, $total_item_count, $customer_tax_class_id);

	if ($quote_id = DB_Identity())
	{
		// adresy dostawy i płatności
		$sql = "INSERT INTO `${dbp}sales_flat_quote_address` (
			quote_id, created_at, updated_at, customer_id, save_in_address_book, customer_address_id, 
			address_type, email, prefix, firstname, middlename, lastname, suffix, company, street, city,
			region, region_id, postcode, country_id, telephone, fax, same_as_billing)
			SELECT {1}, now(), now(), customer_id, 0, customer_address_id, address_type,
			email, prefix, firstname, middlename, lastname, suffix, company, street, city, region, region_id,
			postcode, country_id, telephone, fax, if(address_type = 'shipping' AND '{2}' = '{3}', 1, 0)
			FROM `${dbp}sales_flat_order_address` where parent_id = '{0}'";
		DB_Query($sql, $this_order_id, $quote_id, $address_id['delivery'], $address_id['invoice']);

		// produkty w koszyku
		$sql = "SELECT item_id, parent_item_id, product_id FROM `${dbp}sales_flat_order_item`
			WHERE order_id = {0}
			ORDER BY item_id";
		$res = DB_Query($sql, $this_order_id);

		$sql = "INSERT INTO `${dbp}sales_flat_quote_item` (
			quote_id, created_at, updated_at, product_id, store_id, parent_item_id,
			is_virtual, sku, name, is_qty_decimal, weight, qty, price, base_price,
			row_total, base_row_total, row_weight, product_type,
			price_incl_tax, base_price_incl_tax, row_total_incl_tax, base_row_total_incl_tax)
			SELECT
			'{0}', created_at, updated_at, product_id, store_id, {3},
                        is_virtual, sku, name, is_qty_decimal, weight, qty_ordered, price, base_price,
                        row_total, base_row_total, row_weight, product_type,
                        price_incl_tax, base_price_incl_tax, row_total_incl_tax, base_row_total_incl_tax
			FROM `${dbp}sales_flat_order_item` WHERE order_id = '{1}' AND item_id = {2}";

		$prev_quote_item_id = 'null';

		while ($oi = DB_Fetch($res))
		{
			if (!$oi['product_id'])
			{
				continue; // brak powiązania z produktem
			}

			DB_Query($sql, $quote_id, $this_order_id, $oi['item_id'], $oi['parent_item_id'] ? $prev_quote_item_id : 'null');
			$prev_quote_item_id = DB_Identity();
			DB_Query("UPDATE `${dbp}sales_flat_order_item` SET quote_item_id = '{1}' WHERE item_id = '{0}'", $oi['item_id'], $prev_quote_item_id);
		}

		// opcje
		$sql = "SELECT sfqi.*, sfoi.product_options
			FROM `${dbp}sales_flat_quote_item` sfqi
			JOIN `${dbp}sales_flat_order_item` sfoi ON sfoi.quote_item_id = sfqi.item_id
			WHERE quote_id = '{0}' AND sfoi.product_options <> ''
			ORDER BY sfqi.item_id";
		$res = DB_Query($sql, $quote_id);

		$bo = array();
		$sql = "INSERT INTO `${dbp}sales_flat_quote_item_option` (item_id, product_id, code, value)
			VALUES ('{0}', '{1}', '{2}', '{3}')";

		while ($qi = DB_Fetch($res))
		{
			$bpo = unserialize($qi['product_options']);

			if (empty($qi['parent_item_id']) and !empty($bpo)) // produkt typu bundle
			{
				$bo['bundle_identity'] = $qi['product_id'];

				foreach ($bpo['info_buyRequest']['bundle_option'] as $boi => $boo)
				{
					$bo['bundle_identity'] .= '_' . $boo . '_' . $bpo['bundle_options'][$boi]['value'][0]['qty'];
				}

				$bo['bundle_option_ids'] = serialize(array_keys($bpo['info_buyRequest']['bundle_option']));
				DB_Query($sql, $qi['item_id'], $qi['product_id'], 'bundle_selection_ids', serialize(array_values($bpo['info_buyRequest']['bundle_option'])));
			}

 			// opcje takie same dla rodzica i wszystkich składowych produktów (pobrane powyżej)
			foreach ($bo as $bok => $bov)
			{
				DB_Query($sql, $qi['item_id'], $qi['product_id'], $bok, $bov);
			}

			DB_Query($sql, $qi['item_id'], $qi['product_id'], 'info_buyRequest', serialize($bpo['info_buyRequest']));

			if (empty($qi['parent_item_id'])) 
			{
				$bundle_parent_item_id = $qi['item_id'];
				continue;
			}

			// dotyczy tylko składników zestawu:
			DB_Query($sql, $qi['item_id'], $qi['product_id'], 'bundle_selection_attributes', $bpo['bundle_selection_attributes']);
			DB_Query($sql, $bundle_parent_item_id, $qi['product_id'], 'product_qty_' . $qi['product_id'], $bpo['info_buyRequest']['qty']);
			$bpo['bundle_selection_attributes'] = unserialize($bpo['bundle_selection_attributes']);
			DB_Query($sql, $bundle_parent_item_id, $qi['product_id'], 'selection_qty_' . $bpo['info_buyRequest']['bundle_option'][$bpo['bundle_selection_attributes']['option_id']], $bpo['info_buyRequest']['qty']);
			DB_Query($sql, $qi['item_id'], $qi['product_id'], 'selection_id',  $bpo['info_buyRequest']['bundle_option'][$bpo['bundle_selection_attributes']['option_id']]);
		}
	}			
	else
	{
		$quote_id = 'null';
	}

	//update informacji o zamowieniu
	$sql = "UPDATE `${dbp}sales_flat_order` SET 
		`base_grand_total` = '{0}', `base_subtotal` = '{1}', `base_tax_amount` = '{2}',
		`grand_total` = '{0}', `total_due` = '{0}', `subtotal` = '{1}', `tax_amount` = '{3}', `total_qty_ordered` = '{4}',
		`billing_address_id` = '{5}', `shipping_address_id` = '{6}', `subtotal_incl_tax` = '{7}', 
		`base_subtotal_incl_tax` = '{7}', `weight` = '{8}', `total_item_count` = '{9}',
		`shipping_incl_tax` = '{10}', `base_shipping_incl_tax` = '{11}', `updated_at` = '{12}',
		`increment_id` = '{13}', `quote_id` = {14}
		WHERE `entity_id` = '{15}'";
	DB_Query($sql, $total_price, $sum_price_netto, $base_tax_amount, $base_tax_amount, $sum_product_qty,
                $id_billing, $id_delivery, $sum_products_price, $sum_weight, $total_item_count,
                $request['delivery_price'], $request['delivery_price'], gmdate('Y-m-d H:i:s'), $increment_id, 
		$quote_id, $this_order_id);
	
	//update informacji o platosci
	$sql = "UPDATE `${dbp}sales_flat_order_payment` SET `base_amount_ordered` = '{0}', `amount_ordered` = '{0}' WHERE parent_id = '{1}'";
	DB_Query($sql, $total_price, $this_order_id);
	
	//dodawanie informacji wyswietlanych na stronie glownej zamowien
	$sql = "INSERT INTO `${dbp}sales_flat_order_grid`
			(`entity_id`, `status`, `store_id`, `store_name`, `base_grand_total`, `grand_total`, `increment_id`, `base_currency_code`, `order_currency_code`, 
			`shipping_name`, `billing_name`, `created_at`, `customer_id`)
		VALUES ('{0}', '{1}', '{2}', 'Allegro', '{3}', '{3}', '{4}', '{5}', '{5}', '{6}', '{7}', '{8}', {9})";
	DB_Query($sql, $this_order_id, $request['status_id'] ? $request['status_id'] : 'pending', $options['store_id'], $total_price, $increment_id,
		$request['currency'] ? $request['currency'] : 'PLN', $request['delivery_fullname'], $request['invoice_fullname'], $date, $customer_id ? $customer_id : 'null');
	
	// rejestrujemy podatek od wysyłki w rejestrze podatków
	if ($request['delivery_price'] >= 0.01)
	{
		$shipping_tax_rate = number_format($fix_shipping_tax ? $tax_rate : $options['shipping_tax_rate'], 2, '.', '');
		$tax_matrix[$shipping_tax_rate]['shipping'] = $request['delivery_price']-$request['delivery_price']/(1+$shipping_tax_rate/100);
	}

	// zapis rozbicia podatków do bazy
	// a) wg stawek
	$sql = "INSERT INTO `${dbp}sales_order_tax`
		(order_id, code, title, percent, amount, priority, position,
		 base_amount, process, base_real_amount, hidden)
		VALUES
		('{0}', '{1}%', '{2}%', '{3}', '{4}', '{5}', '{6}',
		 '{7}', '{8}', '{9}', '{10}')";
	// b) wg pozycji zamówienia
	$sql2 = "INSERT INTO `${dbp}sales_order_tax_item`
		(tax_id, item_id, tax_percent)
		VALUES ('{0}', '{1}', '{2}')";
	$i = 0; // pozycja

	foreach ($tax_matrix as $rate => $amounts)
	{
		$rate_amt = 0;

		// sumaryczna kwota podatku dla danej stawki
		foreach ($amounts as $item_id => $item_tax)
		{
			$rate_amt += $item_tax;
		}

		DB_Query($sql, $this_order_id, $rate*1, $rate*1, $rate, $rate_amt, $i, $i,
			 $rate_amt, 0, $rate_amt, 0);
		$i++;

		if ($tax_id = DB_Identity())
		{
			foreach ($amounts as $item_id => $item_tax)
			{
				if ($item_id != 'shipping')
				{
					DB_Query($sql2, $tax_id, $item_id, $rate);
				}
			}
		}
	}

	// generowanie faktury - tylko kiedy zamówienie jest zapisywane po raz pierwszy
	if (!$request['previous_order_id'] and $options['create_invoice'])
	{
		// generowanie nowego increment_id dla faktury
		$sql = "SELECT increment_last_id+1
			FROM `${dbp}eav_entity_store`
			WHERE entity_type_id = (SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'invoice')
			AND store_id = {0} LIMIT 1";
		$res = DB_Query($sql, $options['store_id']);

		if ($invoice_increment_id = DB_Result($res))
		{
			DB_Query("UPDATE `${dbp}eav_entity_store` SET increment_last_id = increment_last_id+1 WHERE store_id = {0} AND entity_type_id = (SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'invoice') LIMIT 1", $options['store_id']);
		}
		
		if (isset($amnumber['amnumber/order/format']))
		{
			$invoice_increment_id = $amnumber['amnumber/order/format'];
			// wypełnianie kolejnych tagów
			$invoice_increment_id = str_replace('{yy}', gmdate('Y'), $invoice_increment_id);
			$invoice_increment_id = str_replace('{mm}', gmdate('m'), $invoice_increment_id);
			$invoice_increment_id = str_replace('{dd}', gmdate('d'), $invoice_increment_id);
			$invoice_increment_id = str_replace('{store}', $options['store_id'], $invoice_increment_id);

			// licznik faktur
			if (strpos($invoice_increment_id, '{counter}') !== false)
			{
				$counter = (int)$amnumber['amnumber/order/counter'];

				if (isset($amnumber['amnumber/order/pad']))
				{
					$counter = sprintf("%0${amnumber['amnumber/order/pad']}d", $counter);
				}

				$invoice_increment_id = str_replace('{counter}', $counter, $invoice_increment_id);
			}
		}

		// prefiks numeru faktury
		if (isset($amnumber['amnumber/invoice/prefix']))
		{
			$invoice_increment_id = $amnumber['amnumber/invoice/prefix'] . $invoice_increment_id;
		}

		$sql = "INSERT INTO `${dbp}sales_flat_invoice` (
			/* 0-2 */ store_id, base_grand_total, shipping_tax_amount,
			/* 3-5 */ tax_amount, base_tax_amount, store_to_order_rate,
			/* 6-7 */ base_shipping_tax_amount, base_discount_amount, 

			/* 8-10 */ base_to_order_rate, grand_total, shipping_amount,
			/* 11-12 */ subtotal_incl_tax, base_subtotal_incl_tax,
			/* 13-14 */ store_to_base_rate, base_shipping_amount, 

			/* 15-17 */ total_qty, base_to_global_rate, subtotal,
			/* 18-20 */ base_subtotal, discount_amount, billing_address_id,
			/* 21-24 */ order_id, state, shipping_address_id, store_currency_code,

			/* 25-26 */ order_currency_code, base_currency_code,
			/* 27-30 */ global_currency_code, increment_id, created_at, updated_at,
			/* 31-33 */ shipping_incl_tax, base_shipping_incl_tax, email_sent)
			VALUES
			(
			'{0}', '{1}', '{2}',
			'{3}', '{4}', '{5}',
			'{6}', '{7}',
			'{8}', '{9}', '{10}',
			'{11}', '{12}',
			'{13}', '{14}',
			'{15}', '{16}', '{17}',
			'{18}', '{19}', '{20}',
			'{21}', '{22}', '{23}', '{24}',
			'{25}', '{26}',
			'{27}', '{28}', '{29}', '{30}',
			'{31}', '{32}', '{33}'
			)";

		DB_Query($sql, 
			$options['store_id'], $total_price, $shipping_tax,
			$shipping_tax+$sum_products_tax, $shipping_tax+$sum_products_tax, 1,
			$shipping_tax, 0,

			0, $total_price, $request['delivery_price'],
			$sum_products_price, $sum_products_price,
			1, $request['delivery_price'],

			$sum_product_qty, 1, $sum_products_price,
			$sum_products_price, 0, $id_billing,
			$this_order_id, 2, $id_delivery, $request['currency'], 
			
			$request['currency'], $request['currency'],
			$request['currency'], $invoice_increment_id, gmdate('Y-m-d H:i:s'), gmdate('Y-m-d H:i:s'),
			$request['delivery_price'], $request['delivery_price'], 0);
		$invoice_id = DB_Identity();

		$sql = "INSERT INTO `${dbp}sales_flat_invoice_grid` (
			entity_id, store_id, base_grand_total, grand_total, order_id,
			state, store_currency_code, order_currency_code, base_currency_code,

			global_currency_code, increment_id, order_increment_id, created_at,
			order_created_at, billing_name)
			VALUES (
			{0}, {1}, {2}, {2}, {3},
			'{4}', '{5}', '{6}', '{7}',
			'{8}', '{9}', '{10}', '{11}',
			'{12}', '{13}')";
		DB_Query($sql, $invoice_id, $options['store_id'], $total_price, $this_order_id,
			2, $request['currency'], $request['currency'], $request['currency'],

			$request['currency'], $invoice_increment_id, $increment_id, gmdate('Y-m-d H:i:s'),
			gmdate('Y-m-d H:i:s'), $request['invoice_fullname']);

		$sql = "INSERT INTO `${dbp}sales_flat_invoice_item`
			(parent_id, base_price, tax_amount, base_row_total, discount_amount, row_total, base_discount_amount, price_incl_tax, base_tax_amount, base_price_incl_tax, qty, base_cost, price, base_row_total_incl_tax, row_total_incl_tax, product_id, order_item_id, additional_data, description, sku, name, hidden_tax_amount, base_hidden_tax_amount, base_weee_tax_applied_amount, base_weee_tax_applied_row_amnt, weee_tax_applied_amount, weee_tax_applied_row_amount, weee_tax_applied, weee_tax_disposition, weee_tax_row_disposition, base_weee_tax_disposition, base_weee_tax_row_disposition)
			SELECT {0}, base_price, tax_amount, base_row_total, discount_amount, row_total, base_discount_amount, price_incl_tax, base_tax_amount, base_price_incl_tax, qty_ordered, base_cost, price, base_row_total_incl_tax, row_total_incl_tax, product_id, item_id, additional_data, description, sku, name, hidden_tax_amount, base_hidden_tax_amount, base_weee_tax_applied_amount, base_weee_tax_applied_row_amnt, weee_tax_applied_amount, weee_tax_applied_row_amount, weee_tax_applied, weee_tax_disposition, weee_tax_row_disposition, base_weee_tax_disposition, base_weee_tax_row_disposition
			FROM `${dbp}sales_flat_order_item`
			WHERE order_id = {1}";
		DB_Query($sql, $invoice_id, $this_order_id);  
	}

	// koniec generowania faktury

	$response = array("order_id" => $increment_id);
	return $response;
}


 /**
 * Funkcja pobiera zamówienia złożone w sklepie internetowym
 * Zwracane liczby (np ceny) powinny mieć format typu: 123456798.12 (kropka oddziela część całkowitą, 2 miejsca po przecinku)
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, zawiera informacje o zamówieniu w formacie:
 *		time_from => czas od którego mają zastać pobrane zamówienia - format UNIX TIME
 *		id_from => ID od którego mają zastać pobrane zamówienia
 *		only_paid => flaga określająca czy pobierane mają być tylko zamówienia opłacone (0/1)
 * @return array $response tablica zawierająca dane zamówień:
 * 		id zamówienia => array:
 *						delivery_fullname, delivery_company, delivery_address, delivery_city, delivery_postcode, delivery_country => dane dotyczące adresu wysyłki
 *						invoice_fullname, invoice_company, invoice_address, invoice_city, invoice_postcode, invoice_country, invoice_nip => dane dotyczące adresu płatnika faktury
 *						phone => nr telefonu, email => adres email, 
 *						date_add => data złożenia zamówienia,
 *						payment_method => nazwa metody płatności,
 *						user_comments => komentarz klienta do zamówienia,
 *						status_id => numer ID statusu zamówienia,
 *						delivery_method_id => numer ID metody wysyłki, delivery_method => nazwa metody wysyłki,
 *						delivery_price => cena wysyłki
 *						products => array:
 *									[] =>
 *										id => id produktu, variant_id => id wariantu produktu (0 jeśli produkt główny), 
										name => nazwa produktu
 *										quantity => zakupiona ilość, price => cena sztuki brutto (uwzględniająca atrybuty) 
 *										weight => waga produktu w kg, tax => wysokość podatku jako liczba z zakresu 0-100
 *										attributes => array: - tablica z wybieralnymi atrybutami produktów (jeśli istnieją)
 *														[] =>
 *															name => nazwa atrybutu (np 'kolor'), 
 *															value => wartość atrubutu (np 'czerwony'), 
 *															price => różnica w cenie w stosunku do ceny standardowe									
 */
function Shop_OrdersGet($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	$paczkomaty_sheepla = null;
	$response = array();
	$payment_name = Shop_PaymentMethodsList($request);

	//zapytanie pobierające zamówienia od określonego czasu
	$sql = "SELECT *, sfo.entity_id as order_id, UNIX_TIMESTAMP(CONVERT_TZ(sfo.created_at,'+00:00','+{2}:00')) as time_purchased,
		if(sfo.total_paid >= sfo.total_due, 1, if(isnull(sfop.amount_paid), 0, if(sfop.amount_paid >= sfo.grand_total, 1, 0))) as paid
		FROM `${dbp}sales_flat_order` sfo
		LEFT JOIN `${dbp}sales_flat_order_payment` sfop ON sfop.parent_id = sfo.entity_id
		LEFT JOIN `${dbp}gift_message` gm ON sfo.gift_message_id = gm.gift_message_id
		WHERE sfo.store_id = '{3}' AND sfo.created_at >= '{0}' AND sfo.increment_id > '{1}'";
    $res = DB_Query($sql, gmdate("Y-m-d H:i:s", (int)$request['time_from']), (int)$request['id_from'], date('I') ? 2 : 1, $options['store_id']);

	while ($order = DB_Fetch($res))
	{
		// sprawdzamy, czy sklep obługuje paczkomaty przez sheepla
		if (!isset($paczkomaty_sheepla))
		{
			$paczkomaty_sheepla = DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}orba_sheepla_order'"));
		}

		// sprawdzamy, czy sklep obługuje plugi poczty polskiej
		if (!isset($ws_pp))
		{
			$ws_pp = DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}ws_pocztapolska_machine'"));
		}

		// płatności przez blue media
		if ($order['method'] == 'bluepayment' and !$order['paid'] and $ai = @unserialize($order['additional_information']))
		{
			$order['paid'] = ($ai['bluepayment_state'] == 'SUCCESS') ? 1 : 0;
		}

		if ($request['only_paid'] and !$order['paid'])
		{
			continue;
		}

		$o = array();

		$o['invoice_nip'] = $order['customer_taxvat'];
	
		$invoice_address = DB_Query("SELECT * FROM `${dbp}sales_flat_order_address` WHERE parent_id = '{0}' AND address_type = 'billing'", $order['order_id']);
		$address = DB_Fetch($invoice_address);
		$o['invoice_fullname'] = $address['firstname'].' '.$address['lastname'];
		$o['invoice_company'] = $address['company'];
		$o['invoice_nip'] = $address['vat_id'] ? $address['vat_id'] : ($address['taxvat'] ? $address['taxvat'] : $o['invoice_nip']);
		$o['invoice_address'] = $address['street'];
		$o['invoice_city'] = $address['city'];
		$o['invoice_postcode'] = $address['postcode'];
		$o['invoice_country_code'] = $address['country_id'];
		$o['invoice_country'] = CountryNameISOCode($address['country_id']);
		$o['phone'] = $address['telephone'];
		$o['email'] = $address['email'] ? $address['email'] : $order['customer_email'];

		// gift message
		$o['extra_field_2'] = $order['message'];

		$delivery_address = DB_Query("SELECT * FROM `${dbp}sales_flat_order_address` WHERE parent_id = '{0}' AND address_type = 'shipping'", $order['order_id']);
		$address = DB_Fetch($delivery_address);

		if ($address['pos_code'])
		{
			$o['delivery_point_name'] = $address['pos_code'];
		}
		//paczkomaty
		if (($order['shipping_method'] == 'inpost_inpost' or $order['shipping_method'] == 'paczkomaty_paczkomaty') and preg_match('/^[A-Z]+\d+/', $address['lastname']))
		{
			$o['delivery_point_name'] = $address['lastname'];
			$o['delivery_point_address'] = $address['street'];
			$o['delivery_point_city'] = $address['city'];
			$o['delivery_point_postcode'] = $address['postcode'];
			$o['delivery_fullname'] = $o['invoice_fullname'];
			$o['delivery_company'] = $o['invoice_company'];
			$o['delivery_address'] = $o['invoice_address'];
			$o['delivery_city'] = $o['invoice_city'];
			$o['delivery_country_code'] = $o['invoice_country_code'];
			$o['delivery_country'] = $o['invoice_country'];
			$o['delivery_postcode'] = $o['invoice_postcode'];
		}
		else
		{	
			$o['delivery_fullname'] = $address ['firstname'].' '.$address['lastname'];
			$o['delivery_company'] = $address['company'];
			$o['delivery_address'] = $address['street'];
			$o['delivery_city'] = $address['city'];
			$o['delivery_postcode'] = $address['postcode'];
			$o['delivery_country_code'] = $address['country_id'];
			$o['delivery_country'] = CountryNameISOCode($address['country_id']);
		}

		// telefon brany z adresu dostawy, chyba że nie podano - wtedy z adresu płatnika
		$o['phone'] = $address['telephone'] ? $address['telephone'] : $o['phone'];

		// NIP z adresu koszyka
		if (empty($o['invoice_nip']) and !$request['only_paid'])
		{
			$o['invoice_nip'] = DB_Result(DB_Query("SELECT vat_id FROM `${dbp}sales_flat_quote_address` WHERE quote_id = '{0}' AND address_type = 'shipping'", $order['quote_id']));
		}

		// inny plugin paczkomatów:
		if ($order['shipping_method'] == 'paczkomaty_jrdpaczkomaty' and !empty($order['paczkomaty']))
		{
			if ($pm_data = json_decode($order['paczkomaty'], true))
			{
				$o['delivery_point_name'] = $pm_data['code'];
				$o['delivery_point_address'] = $pm_data['machine_info']['street'] . ' ' . $pm_data['machine_info']['buildingnumber'];
				$o['delivery_point_city'] = $pm_data['machine_info']['town'];
				$o['delivery_point_postcode'] = $pm_data['machine_info']['postcode'];

				if ($pm_data['customer_telephone'] and empty($o['phone']))
				{
					$o['phone'] = $pm_data['customer_telephone'];
				}

				if ($pm_data['paczkomaty_cod'])
				{
					$o['payment_method_cod'] = 1;
				}
			}
		}
		// nowa wersja modułu, obsługująca API ShipX
		elseif ($order['shipping_method'] == 'smpaczkomaty_smpaczkomaty' and $order['smpaczkomaty'])
		{
			if ($pm_data = json_decode($order['smpaczkomaty'], true))
			{
				if ($pm_data['code'])
				{
					$o['delivery_point_name'] = $pm_data['code'];
					$pm_addr = $pm_data['machine_info']['address_details'];
					$o['delivery_point_address'] = trim($pm_addr['street'] . ' ' . $pm_addr['building_number'] . '/' . $pm_addr['flat_mumber'], ' /');
					$o['delivery_point_city'] = $pm_addr['city'];
					$o['delivery_point_postcode'] = $pm_addr['post_code'];

					if ($pm_data['customer_telephone'] and empty($o['phone']))
					{
						$o['phone'] = $pm_data['customer_telephone'];
					}

					if ($pm_data['paczkomaty_cod'])
					{
						$o['payment_method_cod'] = 1;
					}
				}
			}
		}

		elseif ($order['inpost_machine'])
		{
			$o['delivery_point_name'] = $order['inpost_machine'];
		}
		elseif ($order['owppp_pni'])
		{
			$o['delivery_point_name'] = $order['owppp_pni'];
			$pp_addr = preg_split('/<br\/?'.'>/', $order['owppp_adres']);

			if (count($pp_addr) == 3)
			{
				$o['delivery_point_address'] = $pp_addr[2];
				$pp_addr = explode(' ', $pp_addr[1]);
				$o['delivery_point_city'] = $pp_addr[0];
				$o['delivery_point_postcode'] = $pp_addr[1];
			}
			elseif (count($pp_addr) == 4)
			{
				$o['delivery_point_address'] = $pp_addr[0] . ', ' . $pp_addr[3];
				$o['delivery_point_city'] = $pp_addr[1];
				$o['delivery_point_postcode'] = $pp_addr[2];
			}
		}

		// paczkomaty obsługiwane przez moduł sheepla
		if ($paczkomaty_sheepla)
		{
			$res2 = DB_Query("SELECT do_pl_inpost_machine_id FROM `${dbp}orba_sheepla_order` WHERE order_id = '{0}' LIMIT 1", $order['increment_id']);

			if ($machine = DB_Result($res2))
			{
				$o['delivery_point_name'] = $machine;
			}
		} // lub parcellockers
		elseif (preg_match('/^inpostparcellockers/', $order['shipping_method']) and !empty($address['inpost_parcellocker_id']))
		{
			$o['delivery_point_name'] = $address['inpost_parcellocker_id'];
		}
		elseif ($ws_pp and preg_match('/pocztapolska/', $order['shipping_method'])) // lub punkt odbioru poczty polskiej
		{
			$res2 = DB_Query("SELECT machine, description FROM `${dbp}ws_pocztapolska_machine` WHERE order_id = '{0}' LIMIT 1", $order['order_id']);

			if ($dp = DB_Fetch($res2))
			{
				$dp_addr = strip_tags($dp['description'], '<br>');
				$dp_addr = preg_split('/\s*<br\s*\/?>\s*/i', $dp_addr);
				$o['delivery_point_name'] = $dp['machine'] . ' ' . array_shift($dp_addr);
				$o['delivery_point_address'] = array_shift($dp_addr);
				$o['delivery_point_city'] = array_shift($dp_addr);

				if (preg_match('/(\d\d-\d{3})\s(.+)/', $o['delivery_point_city'], $m))
				{
					$o['delivery_point_city'] = $m[2];
					$o['delivery_point_postcode'] = $m[1];
				}
			}
		}

		// DHL parcelshop
		if (!$o['delivery_point_name'] and isset($order['dhl24pl_parcelshop']))
		{
			if ($dhl = @json_decode($order['dhl24pl_parcelshop'], true))
			{
				if ($dhl['sap'])
				{
					$o['delivery_point_name'] = $dhl['sap'];
					$o['delivery_point_address'] = trim($dhl['name'] . ', ' . $dhl['street'] . ' ' . $dhl['streetNo'] . '/' . $dhl['houseNo'], ' /,');
					$o['delivery_point_city'] = $dhl['city'];
					$o['delivery_point_postcode'] = $dhl['zip'];
				}
			}
		}

		if (empty($o['delivery_point_name']) and preg_match('/sendit_bliskapaczka/', $order['shipping_method']))
		{
			$res2 = DB_Query("SELECT pos_code FROM `${dbp}sendit_bliskapaczka_order` WHERE order_id = '{0}' LIMIT 1", $order['order_id']);

			if ($dp = DB_Result($res2))
			{
				$o['delivery_point_name'] = $dp;
			}
		}

		//klient chce fakturę
		$o['want_invoice'] = $order['invoice_yes_no'] ? 1 : 0;

		//dane do faktury z tabeli głównej
		if (!$o['invoice_nip'] and $order['invoice_company_nip'])
		{
			$o['invoice_nip'] = $order['invoice_company_nip'];
			$o['invoice_company'] = $order['invoice_company'];
			$o['invoice_address'] = $order['invoice_company_street'];
			$o['invoice_city'] = $order['invoice_company_city'];
			$o['invoice_postcode'] = $order['invoice_company_postcode'];
		}
		elseif ($order['shipping_method'] == 'paczkomaty_paczkomaty' and $order['paczkomat'])
		{
			if (preg_match('/^(.+?), (.+?) (\d\d-\d{3}).*? - ([A-Z0-9-]+)$/', $order['paczkomat'], $m))
			{
				$o['delivery_point_address'] = $m[1];
				$o['delivery_point_city'] = $m[2];
				$o['delivery_point_postcode'] = $m[3];
				$o['delivery_point_name'] = $m[4];
			}
		}

		if ($o['invoice_nip'])
		{
			$o['want_invoice'] = 1;
		}
		
	$sql = "SELECT GROUP_CONCAT(comment SEPARATOR \"\\n\")
		FROM `${dbp}sales_flat_order_status_history`
		WHERE parent_id = '{0}' AND comment <> '' AND is_visible_on_front"
		. (($options['magento_version'] > '01050001') ? " AND entity_name = 'order'" : '');
	$comment = DB_Query($sql, $order['order_id']);
	$o['user_comments'] = DB_Result($comment);

		if ($order['onestepcheckout_customercomment'] != '')
		{
			$o['user_comments'] = $order['onestepcheckout_customercomment'];
		}
		if ($order['firecheckout_customer_comment'] != '')
		{
			$o['user_comments'] = $order['firecheckout_customer_comment'];
		}
		elseif ($order['customer_note'])
		{
			$o['user_comments'] = $order['customer_note'];
		}
		
		$o['date_add'] = $order['time_purchased'];
		$o['payment_method'] = $payment_name[$order['method']] ? $payment_name[$order['method']] : ucfirst($order['method']);

		if (empty($o['payment_method']))
		{
			// dopasowanie z pominięciem prefiksu
			foreach ($payment_name as $pmt_code => $pmt_name)
			{
				if (preg_match("/_${order['method']}$/", $pmt_code))
				{
					$o['payment_method'] = $pmt_name;
					break;
				}
			}
		}

		$o['paid'] = $order['paid'];
		$o['status_id'] = $order['status'];

		//płatność za pobraniem
		if (preg_match('/pobran|cashondelivery/i', $order['method']))
		{
			$o['payment_method_cod'] = 1;
			$o['paid'] = 0;
		}
	
		// Paczkomaty przez spinacz	
		if ($order['spinacz_service'] and $order['spinacz_service'] == 'inpost' and $order['spinacz_type'] == 'punkt')
		{
			$o['delivery_point_name'] = $order['spinacz_service_details'];

			if (preg_match('/^[A-Z0-9]+ - (.+?), (.+?) (\d\d-\d{3})$/', $order['spinacz_label'], $m))
			{
				$o['delivery_point_address'] = $m[1];
				$o['delivery_point_city'] = $m[2];
				$o['delivery_point_postcode'] = $m[3];
			}
		}

		//sposób wysyłki
		$o['delivery_method'] = $order['shipping_description'];
		$o['delivery_price'] = number_format(($order['shipping_incl_tax'] ? $order['shipping_incl_tax'] : ($order['base_shipping_amount']+(float)$order['base_shipping_tax_amount']))+$order['payment_charge']+$order['fee_amount']+$order['cod_fee']+$order['cod_tax_amount']-(($order['shipping_discount_amount'] >= 0.01 and $order['discount_amount'] == 0) ? $order['shipping_discount_amount'] : 0)+($order['et_payment_extra_charge'] ? (float)$order['et_payment_extra_charge'] : (float)$order['base_et_payment_extra_charge'])+(($order['msp_cashondelivery_incl_tax'] and preg_match('/^msp_cashon/', $order['method'])) ? (float)$order['msp_cashondelivery_incl_tax'] : 0), 2, ".", "");

		// waluta
		$o['currency'] = $order['order_currency_code'];

		//produkty zamówienia
		$o['products'] = array();
		$bundles = array(); // zestawy
		$processed = array(); // warianty obsłużone w pętli poniżej

		
		$sql = "SELECT oim.*, oiv.product_id AS variant_id, oiv.name AS variant_name, oiv.qty_ordered AS variant_qty,
			oiv.price AS variant_price, oiv.discount_amount AS variant_discount,
			oiv.price_incl_tax variant_price_gross, oim.price_incl_tax price_gross,
			oiv.product_options bundle_options, oiv.item_id child_item_id, oim.parent_item_id
			FROM `${dbp}sales_flat_order_item` oim
			LEFT JOIN `${dbp}sales_flat_order_item` oiv ON oiv.parent_item_id = oim.item_id AND oiv.order_id = {0} AND oim.product_type <> 'bundle'
			WHERE oim.order_id = {0}
			ORDER BY oim.item_id";

		$result = DB_Query($sql, $order['order_id']);
		$product_discounts = 0;
		$total_products = 0; // suma produktów zamówienia

		
		while ($product = DB_Fetch($result))
		{
			if ($processed[$product['item_id']]) // ten produkt został już wcześniej obsłużony w kontekście wariantu
			{
				continue;
			}

			$prod_data = entity_data('catalog_product', $product['product_id'], false, null, array('name', 'weight', 'tax_class_id', 'type_id', 'sku', 'ean'));
			$op = array();
			$op['id'] = $product['product_id'];
			$op['name'] = $product['name'];
			$op['attributes'] = array();
			$op['item_id'] = $product['item_id']; // potrzebne kiedy ten sam produkt bundle pobrany jest w 2 lub więcej konfiguracji!
			
			// Zachowujemy dane produktu bundle
			if ($product['product_type'] == 'bundle')
			{
				$bundles[$product['item_id']] = $op;

				if ($option['split_bundles'])
				{
					continue; // w zamówieniu pojawią się tylko składowe, bez kontenera
				}
			}
			elseif ($product['product_type'] == 'configurable') // wariant
			{
				$op['variant_id'] = (int)$product['variant_id'];
				$processed[$product['child_item_id']] = 1;

				if (!empty($product['variant_name']))
				{
					if (strpos($product['variant_name'], $op['name']) === 0) // jeśli nazwa wariantu zawiera nazwę produktu
					{
						$op['name'] = $product['variant_name']; // użyj nazwy wariantu
					}
					else // w innym razie
					{
						$op['name'] .= " ${product['variant_name']}"; // doklej do nazwy produktu
					}
				}
			}

			if ($product['tax_percent'] < 0.01)
			{
				$product['tax_percent'] = $options['def_tax'];
			}

			$op['quantity'] = round($product['variant_qty'] ? $product['variant_qty'] : $product['qty_ordered']);
			$op['tax'] = number_format($product['tax_percent'], 2, '.', '');
			$op['weight'] = $product['weight'];
			$op['sku'] = $product['sku'];
			$op['ean'] = $prod_data['ean'];

			// rejestrujemy składową zestawu
			if (!$options['split_bundles'] and $product['product_type'] == 'simple' and isset($bundles[$product['parent_item_id']]))
			{
				$bundles[$product['parent_item_id']]['components'][] = $product;
				continue;
			}

			if ((float)$product['variant_price_gross'] >= 0.01)
			{
				$op['price'] = $product['variant_price_gross'];
			}
			elseif ((float)$product['price_gross'] >= 0.01)
			{
				$op['price'] = $product['price_gross'];
			}
			// obliczanie jednostkowej ceny brutto
			elseif ((float)$product['variant_price'] >= 0.01)
			{
				$op['price'] = number_format(($product['variant_price']-$product['variant_discount']/$product['variant_qty'])*(1+$op['tax']/100), 2, ".", "");
				$product_discounts += $product['variant_discount'];
			}
			else
			{
				$op['price'] = number_format(($product['price']-$product['discount_amount']/$product['qty_ordered'])*(1+$op['tax']/100), 2, ".", "");
				$product_discounts += $product['discount_amount'];
			}

			// pobieranie atrybutów
			if($product['product_options'] != "")
			{
				$product['product_options'] = unserialize($product['product_options']);

				if (is_array($product['product_options']['options']))
				{
					foreach ($product['product_options']['options'] as $attr)
					{
						$op['attributes'][] = array('name' => $attr['label'], 'value' => $attr['value'], 'price' => 0);
					}
				}

				if(is_array($product['product_options']['attributes_info']))
				{
					foreach($product['product_options']['attributes_info'] as $attribute)
					{
						$a['name'] = $attribute['label'];
						$a['value'] = $attribute['value'];
						$a['price'] = 0;
						$op['attributes'][] = $a;
					}
				}
			}
			
			$o['products'][] = $op;
			$total_products += $op['quantity']*$op['price'];
		}

		// uzupełnienie składowych bundle jako atrybutów produktu w zamówieniu przekazanym do BL
		if (count($bundles) and !$options['separate_bundles'])
		{
			foreach ($bundles as $bundle)
			{
				foreach ($o['products'] as $i => $prod)
				{
					if ($prod['item_id'] == $bundle['item_id'])
					{
						$o['products'][$i]['price'] -= $bundle['discount']/$prod['quantity'];

						foreach ($bundle['components'] as $bcomp)
						{
							// stawka podatku produktu w BL pobrana ze składnika bundle
							if ($o['products'][$i]['tax'] == 0 and $bcomp['tax_percent'] > 0)
							{
								$o['products'][$i]['tax'] = $bcomp['tax_percent'];
							}

							$o['products'][$i]['attributes'][] = array('name' => '', 'value' => ($bcomp['qty_ordered'] ? (round($bcomp['qty_ordered']).'x ') : '') . $bcomp['name'], 'price' => 0);
						}

						break;
					}
				}
			}
		}
	
		// kod rabatowy
		if ($order['coupon_code'] != '')
		{
			$o['extra_field_1'] = $order['coupon_code'];
		}

		// rabat od kwoty zamówienia
		$order['discount_amount'] = (float)$order['discount_amount'] + $product_discounts; // pomniejszony o odliczone wcześniej kwoty

		if ($order['discount_amount'] <= -0.01 and !$options['split_discounts'])
		{
			$o['products'][] = array(
				'name' => 'Rabat',
				'id' => '-',
				'tax' => $o['products'][count($o['products'])-1]['tax'],
				'quantity' => 1,
				'price' => $order['discount_amount'],
			);
		}
		// suma rabatów - rozłożona na poszczególne pozycje zamówienia
		elseif ($options['split_discounts'] and $order['discount_amount'] <= -0.01)
		{
			// współczynnik skalowania cen
			$price_scale_factor = (1+$order['discount_amount']/$total_products);
			$new_total_products = 0;

			// obniżenie cen w zamówieniu
			foreach ($o['products'] as $i => $op)
			{
				$op['price'] = number_format($price_scale_factor*$op['price'], 2, '.', '');
				$o['products'][$i] = $op;
				$new_total_products += $op['price']*$op['quantity'];
			}

			// na skutek błędów zaokrąglania pozostała reszta, którą trzeba doliczyć do któregoś produktu
			if ($total_diff = $total_products - $new_total_products + $order['discount_amount'] >= 0.01)
			{
				$diff_spilt = false; // czy reszta została już rozdzielona
				$min_qty_prod_id = $o['products'][0]['id']; // id produktu z najmniejszą ilością zakupionych sztuk

				foreach ($o['products'] as $i => $op)
				{
					// .. najlepiej do takiego, który zakupiony został w małej liczbie sztuk
					// lub liczbie sztuk pozwalającej na równe rozdzielenie reszty
					if ($op['quantity'] == 1 or !(round($total_diff*100)%$op['quantity']))
					{
						$o['products'][$i]['price'] += $total_diff/$op['quantity'];
						$diff_split = true;
						break;
					}

					if ($op['quantity'] < $o['products'][$min_qty_prod_id]['quantity'])
					{
						$min_qty_prod_id = $op['id'];
					}
				}

				// jeśli powyższe się nie uda, doklejamy różnicę do produktu z najmnieszą liczbą sztuk
				if (!$diff_split)
				{
					$o['products'][$min_qty_prod_id]['price'] += $total_diff/$op['quantity'];
				}
				
			}
		}

		// dopłata za obsługę płatności
		if (isset($order['mc_psurcharge_amount']) and $order['mc_psurcharge_amount'] >= 0.01)
		{
			$o['products'][] = array(
				'name' => 'Obsługa płatności',
				'id' => '-',
				'tax' => $o['products'][count($o['products'])-1]['tax'],
				'quantity' => 1,
				'price' => $order['mc_psurcharge_amount'],
			);
		}
		elseif (isset($order['fooman_surcharge_amount']) and $order['fooman_surcharge_amount'] >= 0.01)
		{
			$o['products'][] = array(
				'name' => 'Obsługa płatności',
				'id' => '-',
				'tax' => $o['products'][count($o['products'])-1]['tax'],
				'quantity' => 1,
				'price' => number_format($order['fooman_surcharge_amount'] + (float)$order['fooman_surcharge_tax_amount'], 2, '.', ''),
			);
		}

		$response[$order['increment_id']] = $o;
	}
	
	return $response;
}

 /**
 * Funkcja aktualizuje zamówienia wcześniej dodane do bazy
 * W przypadku zapisywania numeru nadawaczego, parametr orders_ids będzie przyjmował zawsze tablicę z jedną pozycją
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, zawiera informacje o aktualizacji zamówienia w formacie:
 *		orders_ids => ID zamówień
 *		update_type => typ zmiany - 'status', `delivery_number`, lub 'paid' (aktualizacja statusu zamówienia, dodanie numeru nadawczego i dodanie/usunięcie wpłaty)
 *		update_value => aktualizowana wartość - ID statusu, numer nadawczy lub informacja o opłaceniu zamówienia (bool true/false)
 * @return array $response tablica zawierająca potwierdzenie zmiany:
 * 		'counter' => ilość zamówień w których dokonano zmiany (nawet jeśli zamówienie pozostało takie jak wcześniej)
 */
function Shop_OrderUpdate($request)
{
	global $options; // globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; // Data Base Prefix - prefix tabel bazy

	$counter = 0;

	// dla wszystkich nadesłanych numerów zamówień
	foreach($request['orders_ids'] as $increment_id)
	{
		// pobieranie entity_id/order_id
		$res = DB_Query("SELECT entity_id FROM `${dbp}sales_flat_order` WHERE increment_id regexp '^0*{0}\$'", $increment_id);

		if (!DB_NumRows($res))
		{
			continue; // nie ma takiego zamówienia
		}

		$order_id = DB_Result($res);

		// zmiana statusu
		if ($request['update_type'] == 'status')
		{
			DB_Query("UPDATE `${dbp}sales_flat_order_grid` SET status = '{0}', updated_at = '{1}' WHERE entity_id = '{2}'",
				  $request['update_value'], gmdate('Y-m-d H:i:s'), $order_id);
			DB_Query("UPDATE `${dbp}sales_flat_order` SET status = '{0}', updated_at = '{1}' WHERE entity_id = '{2}'",
				  $request['update_value'], gmdate('Y-m-d H:i:s'), $order_id);
			DB_Query("INSERT INTO `${dbp}sales_flat_order_status_history` (parent_id, is_customer_notified, status"
				  . (($options['magento_version'] > '01050001') ? ', entity_name' : '') . ", created_at)
				  VALUES ('{0}', 0, '{1}'" . (($options['magento_version'] > '01050001') ? ", 'order'" : '')
				  . ", '{2}')", $order_id, $request['update_value'], gmdate('Y-m-d H:i:s'));
		}

		// zapisanie numeru nadawczego
		elseif ($request['update_type'] == 'delivery_number')
		{
			// szukamy wysyłki dla danego zamówienia
			$res = DB_Query("SELECT entity_id FROM `${dbp}sales_flat_shipment` WHERE store_id = '{0}' AND order_id = '{1}' LIMIT 1",
					 $options['store_id'], $order_id);

			if (!($ship_id = DB_Result($res))) // niestety, wysyłka nie jest jeszcze wprowadzona
			{
				// pobieramy dane zamówienia
				$res = DB_Query("SELECT * FROM `${dbp}sales_flat_order` WHERE entity_id = '{0}' LIMIT 1", $order_id);

				if (!$o = DB_Fetch($res)) // zamówienie nie istnieje, przechodzimy od razu do następnego
				{
					continue;
				}

				$increment_id = 100000001; // domyślna wartość startowa

				// generowanie nowego increment_id
				$sql = "SELECT increment_last_id+1 FROM `${dbp}eav_entity_store`
					WHERE entity_type_id = (SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'shipment')
					AND store_id = {0} LIMIT 1";
				$res = DB_Query($sql, $options['store_id']);

				if ($increment_id = DB_Result($res))
				{
					// aktualizujemy eav_entity_store
					DB_Query("UPDATE `${dbp}eav_entity_store` SET increment_last_id = increment_last_id+1 WHERE store_id = {0} AND entity_type_id = (SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'shipment') LIMIT 1", $options['store_id']);
				}

				// dodanie wysyłki
				DB_Query("INSERT INTO `${dbp}sales_flat_shipment` (store_id, order_id, increment_id, customer_id,
					  shipping_address_id, billing_address_id, created_at, updated_at)
					  VALUES ('{0}', '{1}', '{2}', {3}, '{4}', '{5}', '{6}', '{7}')",
					  $options['store_id'], $order_id, $increment_id, $o['customer_id'] ? $o['customer_id'] : 'null',
					  $o['shipping_address_id'], $o['billing_address_id'], gmdate('Y-m-d H:i:s'), gmdate('Y-m-d H:i:s')); 
				$ship_id = DB_Identity();

				if ($ship_id)
				{
					// aktualizacja grida
					DB_Query("INSERT INTO `${dbp}sales_flat_shipment_grid` (entity_id, store_id, order_id, increment_id, order_increment_id, created_at, order_created_at, shipping_name, total_qty) 
						  SELECT s.entity_id, s.store_id, s.order_id, s.increment_id, og.increment_id, s.created_at, og.created_at, og.shipping_name, s.total_qty 
						  FROM `${dbp}sales_flat_shipment` s LEFT JOIN `${dbp}sales_flat_order_grid` og ON s.order_id = og.entity_id WHERE s.entity_id = '{0}'", $ship_id);
				}

				DB_Query("UPDATE `${dbp}sales_flat_order_item` SET qty_shipped = qty_ordered WHERE order_id = '{0}' AND qty_ordered > 0", $order_id);
			}

			// mając ID wysyłki, możemy wprowadzić numer nadania
			$res = DB_Query("SELECT entity_id FROM `${dbp}sales_flat_shipment_track` WHERE parent_id = '{0}' AND title = '{1}' AND carrier_code = 'custom' LIMIT 1",
					$ship_id, $o['shipping_description']);

			if ($track_no_id = DB_Result($res)) // numer już wprowadzony; wystarczy go zaktualizować
			{
				DB_Query("UPDATE `${dbp}sales_flat_shipment_track` SET track_number = '{0}', updated_at = '{1}' WHERE entity_id = '{2}'",
					 $request['update_value'], gmdate('Y-m-d H:i:s'), $track_no_id);
			}
			else // dodanie nowego rekordu
			{
				DB_Query("INSERT INTO `${dbp}sales_flat_shipment_track` (parent_id, order_id, track_number, title, carrier_code, created_at, updated_at)
					  VALUES ('{0}', '{1}', '{2}', '{3}', 'custom', '{4}', '{4}')",
					  $ship_id, $order_id, $request['update_value'], $o['shipping_description'], gmdate('Y-m-d H:i:s'));
			}
		}

		// zmiana statusu wpłaty
		elseif ($request['update_type'] == 'paid')
		{
			if ($request['update_value'] == true)
			{
				DB_query("UPDATE `${dbp}sales_flat_order_payment`
					  SET amount_paid = amount_ordered, base_amount_paid = base_amount_ordered
					  WHERE parent_id = {0}",
					  $order_id);
				DB_Query("UPDATE `${dbp}sales_flat_order`
					  SET base_total_paid = base_total_due, total_paid = total_due, updated_at = '{0}'
					  WHERE entity_id = {1}",
					  gmdate('Y-m-d H:i:s'), $order_id);
				DB_Query("UPDATE `${dbp}sales_flat_order_grid`
					  SET base_total_paid = base_grand_total, total_paid = grand_total, updated_at = '{0}'
					  WHERE entity_id = {1}",
					  gmdate('Y-m-d H:i:s'), $order_id);

				// wyzerowanie kwoty do zapłaty
				DB_Query("UPDATE `${dbp}sales_flat_order` SET total_due = 0, base_total_due = 0
					  WHERE entity_id = {0} AND total_due = total_paid",
					  $order_id);
			}
			else
			{
				DB_query("UPDATE `${dbp}sales_flat_order_payment` SET amount_paid = 0, base_amount_paid = 0
					  WHERE parent_id = {0}", $order_id);
				DB_Query("UPDATE `${dbp}sales_flat_order` SET base_total_due = base_total_paid, total_due = total_paid, 
					  base_total_paid = 0, total_paid = 0, updated_at = '{0}' WHERE entity_id = {1}",
					  gmdate('Y-m-d H:i:s'),  $order_id);
				DB_Query("UPDATE `${dbp}sales_flat_order_grid`
					  SET base_total_paid = 0, total_paid = 0, updated_at = '{0}'
					  WHERE entity_id = {1}",
					  gmdate('Y-m-d H:i:s'),  $order_id);
			}
		}
		
		$counter++;
	}
	
	return array('counter' => $counter);
}


 /**
 * Funkcja zwraca listę dostępnych statusów zamówień
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica zawierająca dostępne statusy zamówień:
 * 		'status_id' => nazwa statusu
 */
function Shop_StatusesList($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy
	
	$response = array();
	$sql = "SELECT sos.status, if(isnull(sosl.label), sos.label, sosl.label) AS label
		FROM `${dbp}sales_order_status` sos
		LEFT JOIN `${dbp}sales_order_status_label` sosl ON sos.status = sosl.status AND sosl.store_id = {0}";

	$res = DB_Query($sql, $options['store_id']);

	while ($status = DB_Fetch($res))
	{
		$response[$status['status']] = $status['label'];
	}
		
	return $response;
}

 /**
 * Funkcja zwraca listę dostępnych sposobów płatności
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica zawierająca dostępne metody płatności
 * 		'payment_id' => nazwa płatności
 */
function Shop_PaymentMethodsList($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	$response = array();

	// metody aktywne w kontekście wybranego sklepu
	$sql = "SELECT path, value
		FROM `${dbp}core_config_data`
		WHERE path LIKE 'payment/%/active' AND value = '1' AND (scope = 'default' OR (scope = 'stores' AND (scope_id = 0 OR scope_id = '{0}')))";
	$res = DB_Query($sql, $options['store_id']);

	while ($method = DB_Fetch($res))
	{
		$sysname = preg_replace('/^.+\/(.+?)\/.+$/', '$1', $method['path']);
		$response[$sysname] = $sysname;
	}

	// uzupełnianie nazw (tam, gdzie dostępne)
	$sql = "SELECT path, value
		FROM `${dbp}core_config_data`
		WHERE path LIKE 'payment/%/title' AND (scope = 'default' OR (scope = 'stores' AND (scope_id = 0 OR scope_id = '{0}')) OR (scope = 'websites' AND (scope_id = 0 OR scope_id = '{1}')))
		ORDER BY scope_id";
	$res = DB_Query($sql, $options['store_id'], $options['website_id']);

	while ($method = DB_Fetch($res))
	{
		$sysname = preg_replace('/^.+\/(.+?)\/.+$/', '$1', $method['path']);

		if (isset($response[$sysname]))
		{
			$response[$sysname] = $method['value'];
		}
	}

	return $response;
}


 /**
 * Funkcja zwraca listę dostępnych sposobów wysyłki
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica zawierająca dostępne sposoby_wysyłki:
 * 		'delivery_id' => nazwa wysyłki
 */
function Shop_DeliveryMethodsList($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy
	
	$response = array();

	$sql = "SELECT ccd.path, ccdl.value
		FROM `${dbp}core_config_data` ccd
		LEFT JOIN `${dbp}core_config_data` ccdl ON ccdl.path = replace(ccd.path, '/active', '/title')
		WHERE ccd.path LIKE 'carriers/%/active' AND ccd.value = '1'
		AND (ccd.scope = 'default' OR (ccd.scope = 'stores' AND (ccd.scope_id = 0 OR ccd.scope_id = '{0}')))
		AND (ccdl.scope_id = 0 OR ccdl.scope_id = '{0}')";
	$res = DB_Query($sql, $options['store_id']);

	while ($method = DB_Fetch($res))
	{
		$sysname = preg_replace('/^.+\/(.+?)\/.+$/', '$1', $method['path']);

		// nazwa modułu zapisana w oddzielnym pliku XML
		$response[$sysname] = $method['value'];

		if (empty($response[$sysname]))
		{
			foreach (glob(_store_root()."/code/community/*/Shipping/etc/config.xml") as $xml)
			{
				if ($def = simplexml_load_file($xml))
				{
					if (isset($def->default->carriers->$sysname))
					{
						$response[$sysname] = (string)$def->default->carriers->$sysname->title;
						break;
					}
				}
			}
		}
	}
		
	return $response;
}

 /**
 * Funkcja zwraca ceny wszystkich produktów i ich wariantów
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica ze cenami wszystkich produktów, w formacie:
 * 		id produktu => ID produktu jest kluczem tablicy, wartością jest tablica składająca się ze stanów wariantów
 *                             id wariantu => kluczem tablicy jest ID wariantu (0 w przypadku produktu głównego)
 *                             cena => wartościa jest cena wariantu
 */
function Shop_ProductsPrices($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	$price_spec = "if(NOT isnull(sp.value) AND '${options['special_price']}' AND (isnull(spfrom.value) OR spfrom.value <= '{2}') AND (isnull(spto.value) OR spto.value >= '{2}'), sp.value, if(isnull(cpip.group_price), if(isnull(cpip.final_price), if(isnull(cped.value), cped1.value, cped.value), if('${options['special_price']}', cpip.final_price, cpip.price)), cpip.group_price))*${options['currency_rate']}";
	$price_spec_v = "if(NOT isnull(sp_v.value) AND '${options['special_price']}' AND (isnull(spfrom_v.value) OR spfrom_v.value <= '{2}') AND (isnull(spto_v.value) OR spto_v.value >= '{2}'), sp_v.value, if(isnull(cpip_v.group_price), if(isnull(cpip_v.final_price), if(isnull(cped_v.value), cped1_v.value, cped_v.value), if('${options['special_price']}', cpip_v.final_price, cpip_v.price)), cpip_v.group_price))*${options['currency_rate']}";

	$sql = "SELECT attribute_id, attribute_code
		FROM `${dbp}eav_attribute`
		WHERE entity_type_id = (SELECT entity_type_id FROM `${dbp}eav_entity_type` WHERE entity_type_code = 'catalog_product')
		AND attribute_code IN ('price', 'special_price', 'special_to_date', 'special_from_date')";
	$res = DB_Query($sql);

	while ($attr = DB_Fetch($res))
	{
		$attr_id[$attr['attribute_code']] = $attr['attribute_id'];
	}

	$response = array();

	$sql = "SELECT cpe.entity_id prod_id, cpr.child_id variant_id, $price_spec price, $price_spec_v vprice
		FROM `${dbp}catalog_product_entity` cpe
		LEFT JOIN `${dbp}catalog_product_relation` cpr ON cpr.parent_id = cpe.entity_id AND cpe.type_id <> 'bundle'

		/* price */
		LEFT JOIN `${dbp}catalog_product_entity_decimal` cped ON cped.entity_id = cpe.entity_id AND cped.attribute_id = ${attr_id['price']} AND cped.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_decimal` cped1 ON cped1.entity_id = cpe.entity_id AND cped1.attribute_id = ${attr_id['price']} AND cped1.store_id = 0
		LEFT JOIN `${dbp}catalog_product_index_price` cpip ON cpip.entity_id = cpe.entity_id AND cpip.customer_group_id = {1} AND cpip.website_id = {2}

		/* variant price */
		LEFT JOIN `${dbp}catalog_product_entity_decimal` cped_v ON cped_v.entity_id = cpr.child_id AND cped_v.attribute_id = ${attr_id['price']} AND cped_v.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_decimal` cped1_v ON cped1_v.entity_id = cpr.child_id AND cped1_v.attribute_id = ${attr_id['price']} AND cped1_v.store_id = 0
		LEFT JOIN `${dbp}catalog_product_index_price` cpip_v ON cpip_v.entity_id = cpr.child_id AND cpip_v.customer_group_id = {1} AND cpip_v.website_id = {2}

		/* special_price */
		LEFT JOIN `${dbp}catalog_product_entity_decimal` sp ON sp.entity_id = cpe.entity_id AND sp.attribute_id = ${attr_id['special_price']} AND sp.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_decimal` sp_v ON sp_v.entity_id = cpr.child_id AND sp_v.attribute_id = ${attr_id['special_price']} AND sp_v.store_id = {0}

		/* zakres czasowy promocji */
		LEFT JOIN `${dbp}catalog_product_entity_datetime` spfrom ON spfrom.entity_id = cpe.entity_id AND spfrom.attribute_id = ${attr_id['special_from_date']} AND spfrom.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_datetime` spto ON spto.entity_id = cpe.entity_id AND spto.attribute_id = ${attr_id['special_to_date']} AND spto.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_datetime` spfrom_v ON spfrom_v.entity_id = cpr.child_id AND spfrom_v.attribute_id = ${attr_id['special_from_date']} AND spfrom_v.store_id = {0}
		LEFT JOIN `${dbp}catalog_product_entity_datetime` spto_v ON spto_v.entity_id = cpr.child_id AND spto_v.attribute_id = ${attr_id['special_to_date']} AND spto_v.store_id = {0}

		WHERE 1";

	$count_sql = "SELECT count(*) FROM `${dbp}catalog_product_entity` cpe
		      LEFT JOIN `${dbp}catalog_product_relation` cpr ON cpr.parent_id = cpe.entity_id AND cpe.type_id <> 'bundle'";

	// stronicowanie
	$per_page = 10000;
	$pages = ceil((int)DB_Result(DB_Query($count_sql))/$per_page);

	if ($pages > 1)
	{
		$page = $request['page'] ? $request['page'] : 1;
		$sql .= " LIMIT " . (($page-1)*$per_page) . ", $per_page";
	}

	$result = DB_Query($sql, $options['store_id'], $options['customer_group_id'], $options['website_id']);

	while ($prod = DB_Fetch($result))
	{
		$response[$prod['prod_id']][0] = number_format($prod['price'], 2, '.', '');

		if ($prod['variant_id'])
		{
			$response[$prod['prod_id']][$prod['variant_id']] = number_format($prod['vprice'], 2, '.', '');
		}
	}

	if ($pages > 1)
	{
		$response['pages'] = $pages;
	}
	
	return $response;
}

/**
 * Funkcja zwraca pełny zbiór danych dla danego obiektu (entity)
 * @global	array	$options	tablica z ustawieniami ogólnymi z początku pliku
 * @param	string	$entity_type	rodzaj obiektu (np. catalog_product)
 * @param	int	$entity_id	identyfikator obiektu
 * @param	bool	$with_names	czy zwrócić właściwości jako pary nazw (etykiet) i wartości
 * @param	int	$store_id	identyfikator sklepu dla mulitistore (domyślnie pobierany z $options)
 * @return	array	tablica, której kluczem jest kod cechy a wartością jej wartość lub tablica nazwy (name) i wartości (value)
 */
function entity_data($entity_type, $entity_id, $with_names = false, $store_id = null, $fields = array())
{
	global $options; // globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; // Data Base Prefix - prefix tabel bazy
	static $src; // cache atrybutów dostępnych dla $entity_type

	$store_id = isset($store_id) ? (int)$store_id : (int)$options['store_id'];

	if (!isset($src))
	{
		$src = array();
	}

	if (!isset($src[$entity_type]))
	{
		$sql = "SELECT ea.attribute_id, if(isnull(eal.value), frontend_label, eal.value) name, backend_type, attribute_code, eet.entity_type_id, is_unique, frontend_input
			FROM `${dbp}eav_entity_type` eet
			LEFT JOIN `${dbp}eav_attribute` ea ON eet.entity_type_id = ea.entity_type_id
			LEFT JOIN `${dbp}eav_attribute_label` eal ON eal.attribute_id = ea.attribute_id AND store_id = $store_id
			WHERE eet.entity_type_code = '{0}'";
		$res = DB_Query($sql, $entity_type);

		$src[$entity_type] = array();

		foreach (array('static', 'int', 'decimal', 'text', 'varchar', 'datetime') as $type)
		{
			$src[$entity_type][$type] = array();
		}

		while ($row = DB_Fetch($res))
		{
			$src[$entity_type][$row['backend_type']][$row['attribute_code']] = array('id' => $row['attribute_id'], 'name' => $row['name'], 'entity_type_id' => $row['entity_type_id'], 'unique' => $row['is_unique'], 'boolean' => ($row['frontend_input'] == 'boolean'));
		}
	}

	$data = array();

	$sql = "SELECT * FROM `$dbp{0}_entity` WHERE entity_id = '{1}' LIMIT 1";
	$res = DB_Query($sql, $entity_type, $entity_id);
	$row = DB_Fetch($res);

	foreach (array_keys($src[$entity_type]['static']) as $fld)
	{
		$data[$fld] = $data[$src[$entity_type]['static'][$fld]['id']] = $with_names ? array('name' => (string)$src[$entity_type]['static'][$fld]['name'], 'value' => $row[$fld]) : $row[$fld];
	}

	foreach ($src[$entity_type] as $type=>$attrs)
	{
		if ($type == 'static')
		{
			continue; // wartości już pobrane z głównej tabeli
		}

		foreach ($attrs as $attribute_code=>$attr_def)
		{
			if (!empty($fields) and !in_array($attribute_code, $fields))
			{
				continue;
			}

			$sql = "SELECT value FROM `$dbp{4}_entity_{5}` 
				WHERE entity_id = '{0}' AND entity_type_id = {2} AND attribute_id = {3}";

			if ($entity_type != 'customer_address')
			{
				$sql = str_replace('SELECT value', 'SELECT value, store_id', $sql);
				$sql .= " AND (store_id = {1} OR store_id = 0) ORDER BY store_id DESC LIMIT 1";
			}

			$res = DB_Query($sql, $entity_id, $store_id, $attr_def['entity_type_id'], $attr_def['id'], $entity_type, $type);

			if ($value = DB_Result($res))
			{
				if ($attr_def['boolean'])
				{
					$value = $value ? 'tak' : 'nie';
				}
				// entity może zawierać identyfikatory wartości, które są przechowywane w odrębnej tabeli
				elseif (!$attr_def['unique'] and (preg_match('/^\d[\d,]+$/', $value) or preg_match('/^\d+$/', $value)))
				{
					$value = trim($value, ',');
					$sql = "SELECT aov.value, aov.store_id 
					FROM `${dbp}eav_attribute_option` ao
					JOIN `${dbp}eav_attribute_option_value` aov ON ao.option_id = aov.option_id 
					WHERE ao.option_id in ({0}) AND (aov.store_id = {1} OR aov.store_id = 0) AND ao.attribute_id = {2}
					ORDER BY aov.store_id DESC";
					$res = DB_Query($sql, $value, $store_id, $attr_def['id']);

					// dodatkowo może to być wartość multi-select
					if (DB_NumRows($res))
					{
						$value = array();
						$prev_store_id = 0;

						while ($sub = DB_Fetch($res))
						{
							if ($prev_store_id and $sub['store_id'] != $prev_store_id)
							{
								break; // kolejne wartości nie są dla tego sklepu
							}

							$value[] = $sub['value'];
							$prev_store_id = $sub['store_id'];
						}

						$value = implode('|', $value); // połączone wartości atrybutu
					}
				}

				$data[$attribute_code] = $data[$attr_def['id']] = $with_names ? array('value' => $value, 'name' => $attr_def['name']) : $value;
			}
		}

	}

	return $data;
}

/**
 * Funkcja mapująca klasę podatkową do stawki wyrażonej w procentach
 * @param	int	$tax_class_id	id klasy podatku
 * @return	float	stawka podatku
 */
function tax_rate($tax_class_id, $tax_country_code = '')
{
	global $options; // globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; // Data Base Prefix - prefix tabel bazy
	static $tax_rate;
	$tax_class_id = (int)$tax_class_id;
	$tax_country_code = $tax_country_code ? $tax_country_code : $options['tax_country_code'];

	if (!isset($tax_rate[$tax_class_id][$tax_country_code]))
	{
		// jeśli nie podano klasy podatku, użyj wartości domyślnej
		if (!$tax_class_id)
		{
			$sql = "SELECT rate FROM `${dbp}tax_calculation_rate` WHERE tax_calculation_rate_id = {0} AND tax_country_id = '$tax_country_code'";
			$res = DB_Query($sql, (int)$options['tax_calculation_rate_id']);
			$tax_rate[0][$tax_country_code] = (float)DB_Result($res);
		} else {
			$sql = "SELECT tcr.rate FROM `${dbp}tax_calculation` tc
				JOIN `${dbp}customer_group` cg ON tc.customer_tax_class_id = cg.tax_class_id AND cg.customer_group_id = '{0}'
				JOIN `${dbp}tax_calculation_rate` tcr ON tcr.tax_calculation_rate_id = tc.tax_calculation_rate_id AND tcr.tax_country_id = '$tax_country_code'
				AND tc.tax_calculation_rule_id IN (SELECT tax_calculation_rule_id FROM `${dbp}tax_calculation_rule`)
				WHERE tc.product_tax_class_id = {1} LIMIT 1";
			$res = DB_Query($sql, $options['customer_group_id'], $tax_class_id);

			if (DB_NumRows($res))
			{
				$tax_rate[$tax_class_id][$tax_country_code] = (float)DB_Result($res);
			}
			elseif ($options['tax_calculation_rate_id']) // nie udało się dopasować po tax_class_id produktu
			{
				$sql = "SELECT rate FROM `${dbp}tax_calculation_rate` WHERE tax_calculation_rate_id = {0}";
				$res = DB_Query($sql, (int)$options['tax_calculation_rate_id']);
				$tax_rate[$tax_class_id][$tax_country_code] = (float)DB_Result($res);
			}
			else
			{
				$tax_rate[$tax_class_id][$tax_country_code] = (int)$options['def_tax'];
			}
		}
	}

	return $tax_rate[$tax_class_id][$tax_country_code];
}

/**
 * Funkcja mapująca kod kraju do jego nazwy
 * @param	string	$code_or_name	dwuliterowy kod ISO lub nazwa
 * @return	string	nazwa lub kod kraju, w zależności od przekazanego parametru
 */
function CountryNameISOCode($code_or_name)
{
	$map = explode("\n", base64_decode('
	QURBbmRvcmEKQUVaamVkbm9jem9uZSBFbWlyYXR5IEFyYWJza2llCkFGQWZnYW5pc3RhbgpBR0Fu
	dGlndWEgaSBCYXJidWRhCkFJQW5ndWlsbGEKQUxBbGJhbmlhCkFNQXJtZW5pYQpBTkFudHlsZSBI
	b2xlbmRlcnNraWUKQU9BbmdvbGEKQVFBbnRhcmt0eWRhCkFSQXJnZW50eW5hCkFTU2Ftb2EgQW1l
	cnlrYcWEc2tpZQpBVEF1c3RyaWEKQVVBdXN0cmFsaWEKQVdBcnViYQpBWFd5c3B5IEFsYW5kemtp
	ZQpBWkF6ZXJiZWpkxbxhbgpCQUJvxZtuaWEgaSBIZXJjZWdvd2luYQpCQkJhcmJhZG9zCkJEQmFu
	Z2xhZGVzegpCRUJlbGdpYQpCRkJ1cmtpbmEgRmFzbwpCR0J1xYJnYXJpYQpCSEJhaHJham4KQklC
	dXJ1bmRpCkJKQmVuaW4KQkxTYWludC1CYXJ0aMOpbGVteQpCTUJlcm11ZHkKQk5CcnVuZWkKQk9C
	b2xpd2lhCkJSQnJhenlsaWEKQlNCYWhhbXkKQlRCaHV0YW4KQlZXeXNwYSBCb3V2ZXRhCkJXQm90
	c3dhbmEKQllCaWHFgm9ydcWbCkJaQmVsaXplCkNBS2FuYWRhCkNDV3lzcHkgS29rb3Nvd2UKQ0RE
	ZW1va3JhdHljem5hIFJlcHVibGlrYSBLb25nYQpDRlJlcHVibGlrYSDFmnJvZGtvd29hZnJ5a2HF
	hHNrYQpDR0tvbmdvCkNIU3p3YWpjYXJpYQpDSVd5YnJ6ZcW8ZSBLb8WbY2kgU8WCb25pb3dlagpD
	S1d5c3B5IENvb2thCkNMQ2hpbGUKQ01LYW1lcnVuCkNOQ2hpbnkKQ09Lb2x1bWJpYQpDUktvc3Rh
	cnlrYQpDVUt1YmEKQ1ZSZXB1Ymxpa2EgWmllbG9uZWdvIFByenlsxIVka2EKQ1hXeXNwYSBCb8W8
	ZWdvIE5hcm9kemVuaWEKQ1lDeXByCkNaUmVwdWJsaWthIEN6ZXNrYQpERU5pZW1jeQpESkTFvGli
	dXRpCkRLRGFuaWEKRE1Eb21pbmlrYQpET1JlcHVibGlrYSBEb21pbmlrYcWEc2thCkRaQWxnaWVy
	aWEKRUNFa3dhZG9yCkVFRXN0b25pYQpFR0VnaXB0CkVIU2FoYXJhIFphY2hvZG5pYQpFUkVyeXRy
	ZWEKRVNIaXN6cGFuaWEKRVRFdGlvcGlhCkZJRmlubGFuZGlhCkZKRmlkxbxpCkZLRmFsa2xhbmR5
	IChNYWx3aW55KQpGTU1pa3JvbmV6amEKRk9XeXNweSBPd2N6ZQpGUkZyYW5jamEKR0FHYWJvbgpH
	QldpZWxrYSBCcnl0YW5pYQpVS1dpZWxrYSBCcnl0YW5pYQpHREdyZW5hZGEKR0VHcnV6amEKR0ZH
	dWphbmEgRnJhbmN1c2thCkdHR3Vlcm5zZXkKR0hHaGFuYQpHSUdpYnJhbHRhcgpHTEdyZW5sYW5k
	aWEKR01HYW1iaWEKR05Hd2luZWEKR1BHd2FkZWx1cGEKR1FHd2luZWEgUsOzd25pa293YQpHUkdy
	ZWNqYQpHU0dlb3JnaWEgUG/FgnVkbmlvd2EgaSBTYW5kd2ljaCBQb8WCdWRuaW93eQpHVEd3YXRl
	bWFsYQpHVUd1YW0KR1dHd2luZWEgQmlzc2F1CkdZR3VqYW5hCkhLSG9uZ2tvbmcKSE1XeXNweSBI
	ZWFyZCBpIE1jRG9uYWxkYQpITkhvbmR1cmFzCkhSQ2hvcndhY2phCkhUSGFpdGkKSFVXxJlncnkK
	SURJbmRvbmV6amEKSUVJcmxhbmRpYQpJTEl6cmFlbApJTVd5c3BhIE1hbgpJTkluZGllCklPQnJ5
	dHlqc2tpZSBUZXJ5dG9yaXVtIE9jZWFudSBJbmR5anNraWVnbwpJUUlyYWsKSVJJcmFuCklTSXNs
	YW5kaWEKSVRXxYJvY2h5CkpFSmVyc2V5CkpNSmFtYWprYQpKT0pvcmRhbmlhCkpQSmFwb25pYQpL
	RUtlbmlhCktHS2lyZ2lzdGFuCktIS2FtYm9kxbxhCktJS2lyaWJhdGkKS01Lb21vcnkKS05TYWlu
	dCBLaXR0cyBpIE5ldmlzCktQS29yZWEgUMOzxYJub2NuYQpLUktvcmVhIFBvxYJ1ZG5pb3dhCktX
	S3V3ZWp0CktZS2FqbWFueQpLWkthemFjaHN0YW4KTEFMYW9zCkxCTGliYW4KTENTYWludCBMdWNp
	YQpMSUxpZWNodGVuc3RlaW4KTEtTcmkgTGFua2EKTFJMaWJlcmlhCkxTTGVzb3RobwpMVExpdHdh
	CkxVTHVrc2VtYnVyZwpMVsWBb3R3YQpMWUxpYmlhCk1BTWFyb2tvCk1DTW9uYWtvCk1ETW/FgmRh
	d2lhCk1FQ3phcm5vZ8OzcmEKTUZTYWludC1NYXJ0aW4KTUdNYWRhZ2Fza2FyCk1IV3lzcHkgTWFy
	c2hhbGxhCk1LTWFjZWRvbmlhCk1MTWFsaQpNTUJpcm1hIChNeWFubWFyKQpNTk1vbmdvbGlhCk1P
	TWFrYXUKTVBNYXJpYW55IFDDs8WCbm9jbmUKTVFNYXJ0eW5pa2EKTVJNYXVyZXRhbmlhCk1TTW9u
	dHNlcnJhdApNVE1hbHRhCk1VTWF1cml0aXVzCk1WTWFsZWRpd3kKTVdNYWxhd2kKTVhNZWtzeWsK
	TVlNYWxlemphCk1aTW96YW1iaWsKTkFOYW1pYmlhCk5DTm93YSBLYWxlZG9uaWEKTkVOaWdlcgpO
	Rk5vcmZvbGsKTkdOaWdlcmlhCk5JTmlrYXJhZ3VhCk5MSG9sYW5kaWEKTk9Ob3J3ZWdpYQpOUE5l
	cGFsCk5STmF1cnUKTlVOaXVlCk5aTm93YSBaZWxhbmRpYQpPTU9tYW4KUEFQYW5hbWEKUEVQZXJ1
	ClBGUG9saW5lemphIEZyYW5jdXNrYQpQR1BhcHVhLU5vd2EgR3dpbmVhClBIRmlsaXBpbnkKUEtQ
	YWtpc3RhbgpQTFBvbHNrYQpQTVNhaW50LVBpZXJyZSBpIE1pcXVlbG9uClBOUGl0Y2Fpcm4KUFJQ
	b3J0b3J5a28KUFNQYWxlc3R5bmEKUFRQb3J0dWdhbGlhClBXUGFsYXUKUFlQYXJhZ3dhagpRQUth
	dGFyClJFUmV1bmlvbgpST1J1bXVuaWEKUlNTZXJiaWEKUlVSb3NqYQpSV1J3YW5kYQpTQUFyYWJp
	YSBTYXVkeWpza2EKU0JXeXNweSBTYWxvbW9uYQpTQ1Nlc3plbGUKU0RTdWRhbgpTRVN6d2VjamEK
	U0dTaW5nYXB1cgpTSMWad2nEmXRhIEhlbGVuYSBpIFphbGXFvG5lClNJU8WCb3dlbmlhClNKU3Zh
	bGJhcmQgaSBKYW4gTWF5ZW4gKHd5c3BhKQpTS1PFgm93YWNqYQpTTFNpZXJyYSBMZW9uZQpTTVNh
	biBNYXJpbm8KU05TZW5lZ2FsClNPU29tYWxpYQpTUlN1cmluYW0KU1RXeXNweSDFmndpxJl0ZWdv
	IFRvbWFzemEgaSBLc2nEhcW8xJljYQpTVlNhbHdhZG9yClNZU3lyaWEKU1pTdWF6aQpUQ1d5c3B5
	IFR1cmtzIGkgQ2FpY29zClREQ3phZApURkZyYW5jdXNraWUgVGVyeXRvcmlhIFBvxYJ1ZG5pb3dl
	ClRHVG9nbwpUSFRhamxhbmRpYQpUSlRhZMW8eWtpc3RhbgpUS1Rva2VsYXUKVExUaW1vciBXc2No
	b2RuaQpUTVR1cmttZW5pc3RhbgpUTlR1bmV6amEKVE9Ub25nYQpUUlR1cmNqYQpUVFRyeW5pZGFk
	IGkgVG9iYWdvClRWVHV2YWx1ClRXVGFqd2FuClRaVGFuemFuaWEKVUFVa3JhaW5hClVHVWdhbmRh
	ClVNRGFsZWtpZSBXeXNweSBNbmllanN6ZSBTdGFuw7N3IFpqZWRub2N6b255Y2gKVVNTdGFueSBa
	amVkbm9jem9uZQpVWVVydWd3YWoKVVpVemJla2lzdGFuClZDU2FpbnQgVmluY2VudCBpIEdyZW5h
	ZHlueQpWRVdlbmV6dWVsYQpWR0JyeXR5anNraWUgV3lzcHkgRHppZXdpY3plClZJV3lzcHkgRHpp
	ZXdpY3plIFN0YW7Ds3cgWmplZG5vY3pvbnljaApWTldpZXRuYW0KVlVWYW51YXR1CldGV2FsbGlz
	IGkgRnV0dW5hCldTU2Ftb2EKWUVKZW1lbgpZVE1ham90dGEKWkFSZXB1Ymxpa2EgUG/FgnVkbmlv
	d2VqIEFmcnlraQpaTVphbWJpYQpaV1ppbWJhYndlCg=='));

	// wyszukiwanie nazwy kraju
	if (strlen($code_or_name) == 2)
	{
		$code_or_name = strtoupper($code_or_name);

		while ($country = array_shift($map))
		{
			if (substr($country, 0, 2) == $code_or_name)
			{
				return substr($country, 2);
			}
		}
	}
	else // wyszukiwanie kodu kraju
	{
		while ($country = array_shift($map))
		{
			if (substr($country, 2) == $code_or_name)
			{
				return substr($country, 0, 2);
			}
		}
	}

	return $code_or_name;
}

/* autodetekcja katalogu sklepu */
function _store_root()
{
	global $options;
	$look_for = 'Mage.php'; // charakterystyczna ścieżka bezpośrednio w katalogu głównym sklepu

	if (empty($options['store_root']))
	{
		$options['store_root'] = '.';

		// przeszukujemy 3 poziomy w górę i w dół względem lokalizacji pliku integracyjnego
		for ($up = 0; $up < 4; $up++)
		{
			for ($down = 0; $down < 3; $down++)
			{
				$path = getcwd() . '/' . str_repeat('../', $up) . str_repeat('*/', $down) . $look_for;

				if (glob($path)) 
				{
					$path = (glob($path));
					break 2;
				}
			}
		}

		if (is_array($path)) // znaleziono poszukiwany plik
		{
			$options['store_root'] = dirname($path[0]);
		}
	}

	return $options['store_root'];
}
?>
