<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php
/******************************************************************************

参数说明:
$max_file_size  : 上传文件大小限制, 单位BYTE
$destination_folder : 上传文件路径
$watermark   : 是否附加水印(1为加水印,其他为不加水印);

使用说明:
1. 将PHP.INI文件里面的"extension=php_gd2.dll"一行前面的;号去掉,因为我们要用到GD库;
2. 将extension_dir =改为你的php_gd2.dll所在目录;
******************************************************************************/

//上传文件类型列表
$uptypes=array(
    'image/jpg',
    'image/jpeg',
    'image/png',
    'image/pjpeg',
    'image/gif',
    'image/bmp',
    'image/x-png',
	'image/JPG',
);

//$QueryServerIP='10.1.243.216';
// $QueryServerIP='10.15.208.110';
$QueryServerIP='10.15.226.177';
$QueryServerPort='6600';
$max_file_size=10000000;     //上传文件大小限制, 单位BYTE
$destination_folder="uploadimg/"; //上传文件路径
$destination=$destination_folder."query.jpg";
$imgpreviewsize=1/2;    //缩略图比例
$imgpreview=1;
$pagesize = 30;
?>
<html>
<head>
<title>同款检索</title>
<link rel="stylesheet" type="text/css" href="css/imgareaselect-default.css" />
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/jquery.imgareaselect.pack.js"></script>
<style type="text/css">
<!--
body
{
     font-size: 9pt;
}
input
{
     /*background-color: #66CCFF;
     border: 1px inset #CCCCCC;*/
}
table{
	margin:auto;
	border-collapse:collapse;
}
a{
	text-decoration:none;
}
#tb_result td img {
	width:450px;
}
#tb_query td img {
	width:300px;
}
-->
</style>
</head>

<body>

<div id="header_apart">
</div>


<script type="text/javascript">
    function submitForm() {
        document.getElementById("submit_form").submit();
    }
</script>
<script type="text/javascript">
	function checkcidvalue() {
		alert ('请选择类别');
		var cid = document.getElementById('label_cid').value;
		if(cid==0) {
			alert ('请选择类别');
			return false;
		} else {
			alert("correct input");
			return true;
    }
</script>

<div id="form_apart">
	<form enctype="multipart/form-data" method="post" name="upform" id="submit_form">
	 <table width="400" border="0" align="center">
		<tr>
			<td align="left">
				<input  name="upfile" type="file"  style="width:200px" onchange="submitForm();">
				<input type="hidden" name="type" value="image" />
			</td>
		</tr>
	  </table>
	</form>

	<form enctype="multipart/form-data" method="post" name="search" >
	 <table width="600" border="0" align="center">
		<tr>
			<td align="right">URL</td>
			<td align="left"><input type="text" name="URL"/></td>
			<td align="right">类别: </td>
			<td align="left">
				<select name="label" id="label_cid">
					<option value="0" selected="selected" >-- --</option>
					<option value="1">裙装</option>
					<option value="2">上装</option>
					<option value="3">下装</option>
					<option value="4">箱包</option>
				</select>
			</td>
			<td align="right">剧名: </td>
			<td align="left"><input type="text" name="serials"/></td>
			<td align="right">
				<input type="submit" name="button" value="搜索" >
				<input type="hidden" name="x1" value="" />
				<input type="hidden" name="y1" value="" />
				<input type="hidden" name="x2" value="" />
				<input type="hidden" name="y2" value="" />
			</td>
		</tr>
	  </table>
	</form>

</div>

<script type="text/javascript">
$(function () {
    $('#queryimg').imgAreaSelect(
		{
			handles: true,
			fadeSpeed: 200,
			onSelectEnd: function (img, selection) {
				$('input[name="x1"]').val(selection.x1);
				$('input[name="y1"]').val(selection.y1);
				$('input[name="x2"]').val(selection.x2);
				$('input[name="y2"]').val(selection.y2);
			}
		});
});
</script>


<?php

//printArray($_POST);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['type']=="image")
{
	if (!is_uploaded_file($_FILES["upfile"]['tmp_name']))
    //是否存在文件
    {
		echo $_FILES["upfile"]['tmp_name'];
		echo "图片不存在!";
		exit;
    }

    $file = $_FILES["upfile"];
	//echo $file['size'];
    if($max_file_size < $file["size"])
    //检查文件大小
    {
        echo "文件太大!";
        exit;
    }

    if(!in_array($file["type"], $uptypes))
    //检查文件类型
    {
        echo "文件类型不符!".$file["type"];
        exit;
    }
    if(!file_exists($destination_folder))
    {
        mkdir($destination_folder);
    }

    $filename=$file["tmp_name"];
	//echo 'tmp_name'.$filename;
	//echo '<br>'.'name'.$file['name'];
    $image_size = getimagesize($filename);
	//echo $image_size[0];
	$image_name_pre = strstr($file["name"],'.',true);
	//echo $image_name_pre;

    $pinfo=pathinfo($file["name"]);
    $ftype=$pinfo['extension'];


    if(!move_uploaded_file ($filename, $destination))
    {
        echo "移动文件出错";
        exit;
    }
	echo "<table id='tb_query'><tr>";
	echo "<td><img id='queryimg' src='{$destination}' ></td>";
	echo "</tr></table>";

}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['button']=="搜索")
{
	$postdata = array();
	/// get product ID
	if($shop_url = $_POST['URL']) {
		if(is_numeric($shop_url)){
			$product_id = $shop_url;
		}
		else{
			$urlarr = parse_url($shop_url);
			parse_str($urlarr['query'],$paras);
			//print_r($paras);
			//print_r($paras['id']);
			if(!$paras['id']){
				echo '无法获取商品ID';
				exit;
			}
			$product_id = $paras['id'];
		}
		$postdata['product_id'] = $product_id;
	}
	else{
		$content = file_get_contents($destination);
		$postdata['image'] = base64_encode($content);
	}


	//print_r(gettype($_POST('label')));
	$sel_label = intval($_POST['label']);
	$x1 = (int)$_POST['x1'];
	$y1 = (int)$_POST['y1'];
	$x2 = (int)$_POST['x2'];
	$y2 = (int)$_POST['y2'];

	$infodata = array(
		'qiidlist' => json_encode(array('1231s','24dfweo2u3345','34534')),
        //'uuidlist' => json_encode(array("f69e5c60b7417ab7e45820606cf5a685",
        //"0597ac60d7ecad2e369aae2834f6f37d",
        //"8712dd6cba53124460b5c1590395151d")),
		'cid' => $sel_label,
	);

	if($x1 != $x2 && $y1!=$y2){
		$qimage_size = getimagesize($destination);
		$qscale = (float)$qimage_size[0]/300;
		$x1 = intval($qscale*$x1);
		$y1 = intval($qscale*$y1);
		$x2 = intval($qscale*$x2);
		$y2 = intval($qscale*$y2);
		if($x2 >= $qimage_size[0]) $x2 = $qimage_size[0]-1;
		if($y2 >= $qimage_size[1]) $y2 = $qimage_size[1]-1;
		echo $x1, $y1, $x2, $y2,'ori:',$qimage_size[0],$qimage_size[1];

		$infodata['rect'] =json_encode(array($x1, $y1,$x2-$x1 +1,$y2-$y1+1));
	}
	$postdata['info'] = json_encode($infodata);
	// Connecting to website.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://'.$QueryServerIP.':'.$QueryServerPort.'/extract_search');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

	$res = curl_exec ($ch);
	if ($errno = curl_errno($ch)) {
		$msg = curl_error($ch);
	}
	else {
		$msg = 'File uploaded successfully.';
	}
	curl_close ($ch);

	$return = array('errno' => $errno, 'msg' => $msg);
	if($errno){
		print_r($return);
	}
	//var_dump($res);
	$res = json_decode($res, true);//Server返回结果
	//print_r($res);




	$cidstr = array("背景", "裙装", "上装", "下装", "包");
	$sel_label = intval($_POST['label']);

	// save return images and draw rectangles
	$num = 0;
	if($res['success'] == 1)
	{
		$resmsg = json_decode($res['msg'], true);

		// draw rectangle on query image
		$queryinfo = $resmsg['query'];//json_decode($resmsg['query'], true);
		// save query image
		$im_query = imagecreatefromjpeg($queryinfo['uri']);
		imagejpeg($im_query, $destination);
		$query_save_name = "temp/"."query.jpg";

		$im_query = imagecreatefromfile($destination);
		$color = imagecolorallocate($im_query, 255, 0, 0);
		imagerectangle($im_query, $queryinfo['rect'][0], $queryinfo['rect'][1],$queryinfo['rect'][0]+$queryinfo['rect'][2]-1,$queryinfo['rect'][1]+$queryinfo['rect'][3]-1, $color);
		//imagerectangle($im_query, $x1, $y1, $x2, $y2, $color);
		imagejpeg($im_query, $query_save_name);
		imagedestroy($im_query);

		$cidname = $cidstr[$queryinfo['cid']];
		echo "<table id='tb_query'><tr>";
		echo "<td><img id='queryimg' src='{$query_save_name}' ></td>";
		echo "<td><br>&nbsp类别: {$cidname}<br>";
		// show distance
		echo "<br>&nbspdistance: ";
		foreach($resmsg['result'] as $v)
		{
				$dist = $v['distance'];
				$uuid = $v['uuid'];
				echo "<br>&nbsp&nbsp&nbsp{$dist}", ",  uuid:", $uuid;
		}
		echo "</td></tr></table>";

		echo "<table id='tb_result'><tr>";

		// show search results
		$mRes = array_chunk($resmsg['result'], 3);
		foreach($mRes as $value)
		{
			echo "<tr>";
			foreach($value as $v)
			{
				$num++;
				$res_save_name = "temp/"."res_".strval($num).".jpg";
				//echo $res_save_name;

				$im_res = imagecreatefromjpeg($v['uri']);
				imagejpeg($im_res, $res_save_name);

				$image_size  = getimagesize($res_save_name);
				$upperleftx  = $image_size[0]*$v['rect'][0]/$v['width'];
				$imagewidth  = $image_size[0]*$v['rect'][2]/$v['width'];
				$upperlefty  = $image_size[1]*$v['rect'][1]/$v['height'];
				$imageheight = $image_size[1]*$v['rect'][3]/$v['height'];
				//echo $upperleftx, $upperlefty, $upperleftx+$imagewidth, $upperlefty+$imageheight, '<br/>';

				imagerectangle($im_res, $upperleftx, $upperlefty, $upperleftx+$imagewidth, $upperlefty+$imageheight, $color);

				imagejpeg($im_res, $res_save_name);
				imagedestroy($im_res);

				echo "<td><img src='{$res_save_name}'></td>";
			}
			echo '</tr>';
		}
		echo '</table>';
	}
	else
	{
		print_r($res['msg']);
	}


	exit;

}

function printArray($array){
     foreach ($array as $key => $value){
        echo " $key => $value ";
        if(is_array($value)){ //If $value is an array, print it as well!
            printArray($value);
        }
    }
}
function imagecreatefromfile( $filename ) {
    if (!file_exists($filename)) {
        throw new InvalidArgumentException('File "'.$filename.'" not found.');
    }
    switch ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ))) {
        case 'jpeg':
        case 'jpg':
            return imagecreatefromjpeg($filename);
        break;

        case 'png':
            return imagecreatefrompng($filename);
        break;

        case 'gif':
            return imagecreatefromgif($filename);
        break;

        default:
            throw new InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');
        break;
    }
}


?>


</body>
</html>
