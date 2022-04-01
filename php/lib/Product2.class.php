<?php 

class Product2 {

    const TBL_PRODUCTS = "products";
    static public $counter = 0;
    static public $flag_add_db = 1;
    static public function add(
                                $code,
                                $category,
                                $subcategory,
                                $name_product,
                                $url,
                                $content,
                                $vnal,
                                $color,
                                $price,
                                $thumb,
                                $thumb_path,
                                $images,
                                $images_path,
                                $category_translit,
                                $subcategory_translit
                                ) {


        $params = [
            $code,
            $category,
            $subcategory,
            $name_product,
            $url,
            $content,
            $vnal,
            $color,
            $price,
            $thumb,
            $thumb_path,
            $images,
            $images_path,
            $category_translit,
            $subcategory_translit
        ];
                                        
        $sql = "insert ignore into ".self::TBL_PRODUCTS." (  code,
                                        category,
                                        subcategory,
                                        name_product,
                                        url,
                                        content,
                                        vnal,
                                        color,
                                        price,
                                        thumb,
                                        thumb_path,
                                        images,
                                        images_path,
                                        category_translit,
                                        subcategory_translit
                                        ) values ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
        
        try {
            $result = DB::add($sql, $params);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

static public function innerParse($parse_url, $limit=300) {
    $parse_url .= "&limit=$limit";
    $catalog = [];
    $html = file_get_contents($parse_url );
    $doc = phpQuery::newDocument($html);
    $category = $doc->find("ul.breadcrumb > li:nth-child(2) > a")->text();
    if ( empty($category) ) {
        
    }
    $h1 = $doc->find(".heading-h1 > h1")->text();     
    // Ссылка на превью 
    $thumbs = [];
    $thumbs_path = [];
    $thumb = $doc->find(".product-thumb__image img");
    foreach ($thumb as $row) {
        $_src = pq($row)->attr('src');
        $thumbs[] = $_src;
    }

    // название и ссылка 
    $links = $doc->find(".product-thumb__caption a.product-thumb__name");
    foreach ($links as $k=> $row) {
        $ent = pq($row);
        $name = $ent->text();
        $url = $ent->attr('href');
        $catalog[$url]["subcategory"] = $h1;
        $catalog[$url]["category"] = $category;
        $catalog[$url]["url"] = $url;

        $html_inner = file_get_contents($url );
        $doc_inner = phpQuery::newDocument($html_inner);
        $title_name = $doc_inner->find(".heading-h1 > h1")->text();
        $catalog[$url]["name_product"] = $title_name;

        // price 
        $prices2 = $doc_inner->find("div.product-page__price")->text();
        $clr = $doc_inner->find("span.option__name")->text();
        $catalog[$url]["price"] = trim($prices2);
        $catalog[$url]["color"] = trim($clr);

        // Ссылка на превью 
        $catalog[$url]["thumb"] = $thumbs[$k];


        // images 
        $images = $doc_inner->find("a.product-page__image-addit-a");
        $str_src = "";
        foreach ($images as $k=> $row) {
            $enta = pq($row);
            $pra = trim($enta->attr('href'));
            $str_src .= $pra . "***";
        }
        $catalog[$url]["images"] = $str_src;
        $catalog[$url]["images_path"] = "";
        
        $doc_inner->find('div.product-data__item div.product-data__item-div')->remove();
        $code = $doc_inner->find(".model")->text();
        $vnal = $doc_inner->find(".stock")->text();
        $catalog[$url]["vnal"] = trim($vnal);

        $model = $doc_inner->find('meta[itemprop="model"]');   
        foreach ($model as $row) {
            $catalog[$url]["code"] = trim(pq($row)->attr('content'));
        }

        $ar_tp = explode('.', $catalog[$url]["thumb"]);
        $r = $ar_tp[count($ar_tp) - 1];
        $catalog[$url]["thumb_path"] = "img/catalog/{$catalog[$url]["code"]}/thumb/thumb_{$catalog[$url]["code"]}.{$r}";

        $text = $doc_inner->find('#tab-description')->text(); 
        $catalog[$url]["content"] = $text;
        // 

        
    }
    


    $arr_pre = [];
    foreach ($catalog as $key => $value) {
        $arr_pre[] = $value;
        $category = !$value["category"] ? $value["subcategory"] : $value["category"];
        $category_translit = Catalog::translit($value["category"]);
        $subcategory_translit = Catalog::translit($value["subcategory"]);
        if ( !Catalog::translit($value["category"]) ) $category_translit = $subcategory_translit;
        if ( self::$flag_add_db ) 
            $result_add = Product2::add(
                                        $value["code"],
                                        $category,
                                        $value["subcategory"],
                                        $value["name_product"],
                                        $value["url"],
                                        $value["content"],
                                        $value["vnal"],
                                        $value["color"],
                                        $value["price"],
                                        $value["thumb"],
                                        $value["thumb_path"],
                                        $value["images"],
                                        $value["images_path"],
                                        $category_translit,
                                        $subcategory_translit
            );
        if ($result_add) {
            self::$counter++;
        }
    }
    return $arr_pre;

}








}