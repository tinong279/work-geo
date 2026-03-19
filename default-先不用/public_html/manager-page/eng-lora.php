<?php
    
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\admin-session.php");
    require("C:\\xampp\\htdocs\\default\\resources.php");
    
?>
<!DOCTYPE html>
<html>
	<head>
		<title>工程模式 - LoRa</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
<?php get_css_js_link() ?>

		<script language="javascript">
			function sendData(path, parameters, method='post')
			{
				const form = document.createElement('form');
				form.method = method;
				form.action = path;
				form.target = "_blank";
				document.body.appendChild(form);
				for (const key in parameters)
				{
					const formField = document.createElement('input');
					formField.type = 'hidden';
					formField.name = key;
					formField.value = parameters[key];
					form.appendChild(formField);
				}
				form.submit();
			}
			function checkForm()
			{
				var msg_str = "";
				
				var lora_id = $("#lora_id").val();
				lora_id = lora_id.toString();
				lora_id = parseInt(lora_id);
				msg_str += padZeroLeft(lora_id.toString(16), 2);
				
				var ad1 = $("#ad1").val();
				ad1 = ad1.toString();
				ad1 = parseFloat(ad1);
				ad1 /= 5;
				ad1 *= 32767;
				if (ad1 < 0)
				{
					ad1 += 65535;
				}
				ad1 = parseInt(ad1);
				msg_str += padZeroLeft(ad1.toString(16), 4);
				
				var ad2 = $("#ad2").val();
				ad2 = ad2.toString();
				ad2 = parseFloat(ad2);
				ad2 /= 5;
				ad2 *= 32767;
				if (ad2 < 0)
				{
					ad2 += 65535;
				}
				ad2 = parseInt(ad2);
				msg_str += padZeroLeft(ad2.toString(16), 4);
				
				var count = $("#count").val();
				count = count.toString();
				count = parseInt(count);
				msg_str += padZeroLeft(count.toString(16), 4);
				
				msg_str += "0000";
				
				var battery_voltage = $("#battery_voltage").val();
				battery_voltage = battery_voltage.toString();
				battery_voltage = parseFloat(battery_voltage);
				battery_voltage *= 100;
				battery_voltage = parseInt(battery_voltage);
				msg_str += padZeroLeft(battery_voltage.toString(16), 4);
				
				msg_str += "5a";
				
				var rssi = $("#rssi").val();
				rssi = rssi.toString();
				msg_str += " " + rssi;
				
				var snr = $("#snr").val();
				snr = snr.toString();
				msg_str += " " + snr;
				
				sendData('/robot/lora-in.php', {msg: msg_str, token: 'a2e7176e8ada09cf8a3c3a23556cf68700ef248b622a582512261538ad5551e8dbae1498352d7f421c50d4c6d7b9fd163300441c363fd441dd30b6ad278e7641'});
				
				return false;
			}
			function padZeroLeft(str, lenght)
			{
				if(str.length >= lenght)
					return str;
				else
					return padZeroLeft("0" +str,lenght);
			}
		</script>
	</head>
	<body>
		<div style="padding-top:16px;"></div>
		<div class="w3-container w3-content">
			<div class="w3-card-4" style="max-width:420px;margin:auto;">
				<header class="w3-container w3-blue">
					<h4 class="w3-center"><b>工程模式 - LoRa</b></h4>
				</header>
				<div class="w3-container">
					<div class="w3-text-red w3-center" style="margin-top:12px;"></div>
					<form action="" method="post" class="" onSubmit="return checkForm()">

					<div class="w3-row w3-section">
						<div class="w3-col"><b>LoRa ID(0-127):</b></div>
						<input class="w3-input w3-border" id="lora_id" type="text" value="1">
					</div>

					<div class="w3-row w3-section">
						<div class="w3-col"><b>AD1(電壓±5):</b></div>
						<input class="w3-input w3-border" id="ad1" type="text" value="0">
					</div>

					<div class="w3-row w3-section">
						<div class="w3-col"><b>AD2(電壓±5):</b></div>
						<input class="w3-input w3-border" id="ad2" type="text" value="0">
					</div>

					<div class="w3-row w3-section">
						<div class="w3-col"><b>COUNT(0-65535):</b></div>
						<input class="w3-input w3-border" id="count" type="text" value="0">
					</div>

					<div class="w3-row w3-section">
						<div class="w3-col"><b>LoRa電池電壓:</b></div>
						<input class="w3-input w3-border" id="battery_voltage" type="text" value="3.3">
					</div>

					<div class="w3-row w3-section">
						<div class="w3-col"><b>RSSI:</b></div>
						<input class="w3-input w3-border" id="rssi" type="text" value="-40">
					</div>

					<div class="w3-row w3-section">
						<div class="w3-col"><b>SNR:</b></div>
						<input class="w3-input w3-border" id="snr" type="text" value="30">
					</div>

					<p class="w3-center">
					<button class="w3-button w3-section w3-blue w3-ripple">送出</button>
					</p>

					</form>
				</div>
			</div>
		</div>
		<div class="w3-padding-16"></div>
	</body>
</html> 