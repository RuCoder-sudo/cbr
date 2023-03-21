//cbr смена курса
function wc_myprice_product_field() {
woocommerce_wp_text_input( array( 'id' => '_my_price', 'class' => 'wc_input_price short', 'label' => __( 'Стоимость', 'woocommerce' ) ) ); // добавляем поле "Стоимость"
woocommerce_wp_text_input( array( 'id' => '_curency_price', 'class' => 'wc_input_price short', 'label' => __( 'Валюта', 'woocommerce' ) ) ); // добавляем поле "Валюта", где указываем только RUB, USD или EUR, по желанию можно добавить новые значения или переделать это поле в выпадающий список
}
add_action( 'woocommerce_product_options_pricing', 'wc_myprice_product_field' );


function wc_myprice_save_product( $product_id ) {
if ( ( $_POST['_my_price'] ) ) {
if ( is_numeric( $_POST['_my_price'] ) )
update_post_meta( $product_id, '_my_price', $_POST['_my_price'] );
} else delete_post_meta( $product_id, '_my_price' );

if ( ( $_POST['_curency_price'] ) ) {
update_post_meta( $product_id, '_curency_price', $_POST['_curency_price'] );
} else delete_post_meta( $product_id, '_curency_price' );
}
add_action( 'save_post', 'wc_myprice_save_product' );


function wc_myprice_show() {
global $product, $post;
// Ничего не предпринимаем для вариативных товаров
//if ( $product->product_type <> 'variable' ) {
$my_price = get_post_meta( $product->id, '_my_price', true );
$curency = get_post_meta( $product->id, '_curency_price', true );
// woocommerce_price( $RUB )

$blogtime = current_time('mysql'); // записываем текущее время и дату
list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $blogtime ); //сохраняем отдельно значения даты, часов, минут и т.д.

if ($hour == 7 || $hour == 12 || $hour == 17 || $hour == 22 || $hour == 1 || $hour == 4) { // указываем на протяжении каких часов мы обновляем (перезаписываем) курс на новый
$data="var=go";
$fp = fsockopen("https://rf-coder.ru", 80, $errno, $errstr, 10); // открыть указанный хост по 80 порту
$out = "POST /currency/get_currency.php HTTP/1.1\n"; // открыть данный скрипт
$out .= "Host: https://rf-coder.ru\n";
$out .= "Referer: https://rf-coder.ru/\n";
$out .= "User-Agent: Opera\n";
$out .= "Content-Type: application/x-www-form-urlencoded\n";
$out .= "Content-Length: ".strlen($data)."\n\n";
$out .= $data."\n\n";
fputs($fp, $out); // отправка данных принимающему скрипту
fclose($fp);	
}
  
// открываем файл с курсом валют и записываем в массив
$lines = file('https://rf-coder.ru/currency.txt');

	if ($lines) {		
// проверяем заполнено ли поле, если да, то умножаем на курс и записываем в $custom_price
if ($curency == "RUB") 
{
	$custom_price = $my_price;
}

if ($curency == "USD") 
{
	$custom_price = $my_price * $lines[0];
}

if ($curency == "EUR") 
{
	$custom_price = $my_price * $lines[1];
}
$custom_price = round($custom_price, 2); // округляем до сотых, чтобы в regular_price не записывались огромные дроби

update_post_meta( $post->ID, '_regular_price', $custom_price );
update_post_meta( $post->ID, '_price', $custom_price );	
	}
}
