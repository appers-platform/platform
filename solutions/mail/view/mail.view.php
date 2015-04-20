<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style type="text/css">
		a:link {
			color:#000;
			text-decoration:underline;
		}
		a:visited {
			color:#000;
			text-decoration:none;
		}
		a:hover {
			color:#000;
			text-decoration:none;
		}
		a:active {
			color:#000;
			text-decoration:none;
		}
		hr {
			border:0;
			color:#e8e8e8;
			background-color:#e8e8e8;
			height:1px;
			width:100%;
			text-align:left;
		}
		.social {
			padding:0 20px 0 0;
		}
		.red {
			color:#d32d27;
		}
	</style>
	<title><?=$title?></title>
</head>
<body style="margin:0;padding:0;border:0;background-color:#fff;text-align:center;font-family:open sans,arial,verdana,sans-serif;color:#333;font-size:12px;">
<table width="100%" style="background:#f0f0f0; padding:20px;">
	<tr>
		<td>
			<table width="600" style="padding:0;border:0;margin:auto;font-size:12px;" cellspacing="0" cellpadding="0px">
				<tr>
					<td width="210" style="padding:20px;">
						<a href="http://<?=PROJECT?>" target="_blank" style="font-size:30px; text-decoration: none; color: black;"><?=PROJECT?></a>
					</td>
				</tr>
			</table>

			<table width="600" align="center" style=" -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px; background-color:#ffffff; padding:10px 45px 10px 45px;border:0;margin:auto;font-size:14px;line-height:175%" cellspacing="0" cellpadding="0px">
				<tr>
					<td style="text-align:left;">
						<?=$content?>
					</td>
				</tr>
			</table>

			<table width="600" align="center" style="padding:20px 20px 0px 20px;border:0;margin:auto;font-size:11px;line-height:250%;color:#666;letter-spacing: -0.05em;" cellspacing="0" cellpadding="0px">
				<tr>
					<td align="center">
						<p style="text-align: center;">
							<strong style="line-height: 15px; font-weight: normal;">
								<?=__('This email was sent to %s.', $receiver_email)?><br>
								<a href="http://<?=PROJECT.\solutions\mail::getUrl('unsubscribe')?>?email=<?=urlencode(\helper::encode($receiver_email, \solutions\mail::getSecret()))?>"><?=__('Unsubscribe')?></a><br>
								&copy; <?=PROJECT?>
							</strong>
						</p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br />
<br />
<br />
<br />
<br />
<br />
</body>
</html>