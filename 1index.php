<?
if(isset($_GET['accountOid']) && intval($_GET['accountOid']) > 0)
{
	$uri = "https://www.signalstart.com/charts.json?accountOid=" . $_GET['accountOid'] . "&chartType=1&commentOption=1&z=0.3483456064451489";

	$cache_file = $_SERVER['DOCUMENT_ROOT'] . "/cache_" . $_GET['accountOid'] . ".txt";

	if (filemtime($cache_file)+86400 < time() OR !file_exists($cache_file)) {
		$content = file_get_contents($uri);
	
		if ($content) {
			file_put_contents($cache_file, $content);
			$json = json_decode($content, true);
			$arResult = json_decode($json['content']['dailyGrowthData'], true);
		}
	}
	else
	{
		$content = file_get_contents($cache_file);
		$json = json_decode($content, true);
		$arResult = json_decode($json['content']['dailyGrowthData'], true);
	}
	$max_average = 0;
	$min_average = 0;
	foreach ($arResult as $key => $str)
	{

		$max_average = $max_average < $str['g'] ? $str['g'] : $max_average;
		$min_average = $min_average > $str['g'] ? $str['g'] : $min_average;

		$date = DateTimeImmutable::createFromFormat("M d, 'y", $str['d']);

		//$arResult[$key]['date'] = $date;
		$arResult[$key]['timestamp'] = $date->getTimestamp();


		$startD = $arResult[0]['timestamp'];
		$finishD = $arResult[count($arResult)-1]['timestamp'];

		$period = $finishD - $startD;

	}
}
?>

<canvas id='canvas' width='550'  height ='267'></canvas>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", () => {

	const canvas = document.querySelector(`#canvas`);
	const ctx = canvas.getContext(`2d`);
	const { width, height } = canvas.getBoundingClientRect();
	const areaWidth = width;
	const areaHeight = height;
	const maxScalePoint = 4;
	const max_average = <?=$max_average?>;
	var point;
    var k = areaWidth / <?=$period?>;
    var k_y = areaHeight / <?=$max_average?>;

    canvas.width = areaWidth;
    canvas.height = areaHeight;

	var scalePoint = max_average / maxScalePoint;
	var textPoint = 0;
	for(let i = 1; i < maxScalePoint; i++) {
		textPoint = i * scalePoint;
		ctx.fillText(textPoint.toFixed(2), 5, areaHeight - ((i * scalePoint) * k_y));
	}
	ctx.fillText(max_average.toFixed(2), 5, 15);

	ctx.fillStyle = "#daece7";
	ctx.strokeStyle = "#08966a";
	ctx.lineWidth = 2.0;
	ctx.beginPath();
	ctx.moveTo(0, areaHeight);
  
    <?foreach($arResult as $item):?>
    	pointX = (<?=$item['timestamp']?> - <?=$startD?>) * k;
    	pointY = areaHeight - (<?=$item['g']?>  * k_y);
    	ctx.lineTo(pointX, pointY);
    <?endforeach?>

    ctx.lineTo(pointX, areaHeight);
    ctx.stroke(); 
    ctx.closePath();  
    ctx.fill();
    ctx.translate(0, canvas.height);
    ctx.fillStyle = "#ffffff";
    <?foreach($arResult as $key => $item):?>
    	<?if($key % 10 == 0):?>
    		pointX = (<?=$item['timestamp']?> - <?=$startD?>) * k;
    		//ctx.fillText("<?=$item['d']?>", pointX, 50);
    	<?endif?>
    <?endforeach?>
});
</script>
<style type="text/css">
	body {
		padding: 0;
		margin: 0;
	}
	#canvas {
		width:100%;
		height:100%;
		box-sizing: border-box;
		padding: 0;
		margin: 0;
	}
</style>