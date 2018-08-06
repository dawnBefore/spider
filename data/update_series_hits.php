<?php
include '../include/Request.php';
include '../include/simple_html_dom.php';
include '../include/Utils.php';

// 抓58车的车系关注数据
$base_url = 'http://car.58che.com/series/s5_n';
for ($i = 1; $i <= 100; $i++) { 
	$url = $base_url . $i . '.html';

	$content = Utils::crawlContent($url, true);
	if (!$content) continue;
	$html = str_get_html($content);

	$items = $html->find('.s_list li');
	foreach ($items as $key => $item) {
		$name = $item->find('.sub1', 0)->plaintext;
		$detailUrl = $item->find('.sub1', 0)->href;
		$focus = $item->find('strong', 0)->plaintext;
		$name = $item->find('.sub1', 0)->plaintext;
		$price = $item->find('.col1', 0)->plaintext;

		$seriesId = preg_replace('/\/\/www.58che.com\/(\d+)\//s', '$1', $detailUrl);

		echo '************************************'."\n";
		echo '车系id: '.$seriesId."\n";
		echo '名称: '.$name."\n";
		echo '价格: '.$price."\n";
		echo '多少人关注: '.$focus."\n";
	}
}