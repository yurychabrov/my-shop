<?php 
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
require_once("vendor/autoload.php");
require_once "lib/core.php";



$url_catalog = "https://prostodrop.ru/rekomend/";
$catalog = [];
$html = file_get_contents($url_catalog );
$doc = phpQuery::newDocument($html);

$links_categorys = [];
$links_category = $doc->find("ul.menu__collapse li a.menu__level-1-a");
foreach ($links_category as $k=> $row) {
	$ent = pq($row);
	$url = $ent->attr('href'); 

    if ( !preg_match('/^http/', $url) ) continue;
    $html_2 = file_get_contents($url );
    $doc_2 = phpQuery::newDocument($html_2);
    $links_category_2 = $doc_2->find("div.category-list div a");
    $links_categorys[$url][] = $url;                             

        foreach ($links_category_2 as $k2=> $row2) {
            $ent = pq($row2);
            $url2 = $ent->attr('href');
            $links_categorys[$url][] = $url2;
        }  
}

$links_categorys_right = [];
foreach ($links_categorys as $key => $valArr) {
    if (preg_match('/path=154/', $key)) continue;
    if ( count($valArr) > 1) {
        array_shift($valArr);
        $links_categorys_right[$key] = $valArr;
    } 
    else 
        $links_categorys_right[$key] = $valArr;
}


 //echo "<pre>"; print_r($links_categorys_right); exit;

$home_catalog = [];

$result = DB::set("truncate table ".Product2::TBL_PRODUCTS);

$arr_pre = [];
foreach ($links_categorys_right as $key => $arr_urls) {
    foreach ($arr_urls as $k => $value) {
        $arr_pre[] = Product2::innerParse($value);
    }
}

echo "<pre>"; print_r($arr_pre); exit;




echo "Было добавлено записей - ". Product2::$counter;





