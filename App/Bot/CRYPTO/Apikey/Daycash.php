<?php
const
host = "https://daycash.net/",
register_link = "https://daycash.net/?ref=nr0l0zcq7hbkyefy",
typeCaptcha = "RecaptchaV2",
youtube = "https://youtube.com/@iewil";

function h($xml = 0, $img = 0){
	$h[]	= "Host: ".parse_url(host)['host'];
	if($xml){
		$h[]	= "X-Requested-With: XMLHttpRequest";
	}
	if($img){
        $h[] = "accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8";
    }
	$h[]	= "cookie: ".Simpan("Cookie");
	$h[]	= "user-agent: ".ua();
	return $h;
}
function Internal($url, $h = 0, $p = 0){
	while(1){
		if($p){
			$r = curl($url, $h, $p);
		}else{
			$r = curl($url, $h);
		}
		if($r[1] == "error code: 520"){
			print m."520: Internal server Error";
			sleep(3);
			print "\r                           \r";
			continue;
		}else{
			return $r;
		}
	}
}
function GetDashboard(){
	$r = Internal(host.'faucet.html', h())[1];
	$data['user'] = explode('</a>', explode('<a href="/membership.html" class="text-success">', $r)[1])[0];
	$data['balance'] = explode('</b>', explode('<b id="sidebarCoins">', $r)[1])[0];
	$data['token'] = explode('</b>', explode('<div class="text-success"><b>', $r)[1])[0];
	$data['bits'] = explode('</small>', explode('<small class="text-success">', $r)[1])[0];
	return $data;
}
function getPtc(){
	Title("Ptc");
	while(true){
		$r = Internal(host.'ptc.html',h())[1];
		$id = explode('">', explode('<div class="website_block" id="', $r)[1])[0];
		$key = explode("',", explode("&key=", $r)[1])[0];
		if(!$id)break;
		
		$r = Internal(host.'surf.php?sid='.$id.'&key='.$key,h())[1];
		if (preg_match('/Session expired!/', $r)) {
			print Error("Session expired!\n");
			print line();
			return 1;
		}
		
		$token = explode("';", explode("var token = '", $r)[1])[0];
		$tmr = explode(";", explode('var secs = ', $r)[1])[0];
		tmr($tmr);
		
		$cap = @Captcha::icon();
		$data = "a=proccessPTC&data=".$id."&token=".$token."&captcha-idhf=0&captcha-hf=".$cap;
		$r = json_decode(Internal(host.'system/ajax.php', h(1), $data)[1], 1);
		if ($r['status'] == 200) {
			print Sukses(trim(strip_tags($r['message'])));
			$r = GetDashboard();
			Cetak("Token",$r["token"]);
			print line();
		}
	}
	print Error("Ptc has finished\n");
	print line();
	
}
function getFaucet(){
	global $api;
	Title("Faucet");
	while(true){
		$r = Internal(host.'roll.html', h())[1];
		$sl = explode(' more', explode('<br/>You must visit ', $r)[1])[0];
		if (preg_match('/You must visit/', $r)) {
			exit(Error("Visit $sl Shortlinks to be able to Roll\n"));
		}
		$tmr = explode(' ', explode('<span id="claimTime">', $r)[1])[0];
		if ($tmr) {
			Tmr($tmr*60+60); continue;
		}
		$token = explode("'", explode("var token = '", $r)[1])[0];
		$recaptcha = explode('"',explode('<div class="g-recaptcha" data-sitekey="',$r)[1])[0];
		if(!$recaptcha){
			print Error("Sitekey Error\n"); continue;
		}
		
		$cap = $api->RecaptchaV2($recaptcha, host.'faucet.html');
		if(!$cap)continue;
		$data = 'a=getBonusRoll&token='.$token.'&captcha=1&challenge=false&response='.$cap;
		$r = json_decode(Internal(host.'system/ajax.php', h(1), $data)[1], 1);
		if ($r['status'] == 200) {
			print Sukses(str_replace([" Congratulations, your ","was","and you won"],["","->","->"],strip_tags($r["message"])));
			$r = GetDashboard();
			Cetak("Token",$r["token"]);
			print line();
		}
	}
}
function getAutoclaim(){
	Title('payout');
	Menu(1, 'Coins');
	Menu(2, 'Faucetpay');
	$payout = readline(Isi('Number'));
	print line();

	Title('Frequency');
	Menu(1, '2 Minutes');
	Menu(2, '5 Minutes');
	Menu(3, '10 Minutes');
	Menu(4, '15 Minutes');
	Menu(5, '20 Minutes');
	$frequency = readline(Isi('Number'));
	print line();
	
	Title('Multiplier');
	Menu(1, 'x1');
	Menu(2, 'x2');
	Menu(3, 'x3');
	Menu(4, 'x4');
	Menu(5, 'x5');
	$x = readline(Isi('Number'));
	print line();
	
	while(true){
		$r = Internal(host.'faucet.html', h())[1];
		$token = explode('|',explode('calcEarnings|POST|',$r)[1])[0];
		if(!$token)continue;
		$r = json_decode(Internal(host.'system/ajax.php?a=calcEarnings&token='.$token.'&payout='.trim($payout).'&frequency='.trim($frequency).'&boost='.trim($x), h())[1],1);
		if($r["status"] == 200){
			//$notice = strip_tags($r["message"]);
			$tmr = explode(' minutes', explode('account every ', $r["message"])[1])[0];
			//print sukses($notice);
			//print line();
			//break;
		}elseif(!$r["status"]){
			$sesi = "Session expired";
			if(preg_match("/$sesi/", $r["message"])){
				return;
			}
			print_r($r);
			exit;
		}else{
			print_r($r);
			continue;
		}
		
		$data = 'a=startClaim&token='.$token.'&payout='.trim($payout).'&frequency='.trim($frequency).'&boost='.trim($x);
		$r = json_decode(Internal(host . 'system/ajax.php', h(), $data)[1],1);
		if(preg_match("/You don't have enough Faucet Tokens!/", $r["message"])){
			print Error("You don't have enough Faucet Tokens!\n");
			print line();
			return 1;
		}
        
        $data = 'a=validateClaim&token='.$token;
        tmr($tmr * 60);
        $res = json_decode(Internal(host . 'system/ajax.php', h(1), $data)[1]);
        if ($res->status == 200) {
            $msg = explode('!', explode('<i class="fas fa-check-circle"></i>', $res->message)[1])[0];
            print sukses(trim($msg));
            $r = GetDashboard();
			Cetak("Token",$r["token"]);
			Cetak("Balance",$r["balance"].'-'.$r["bits"]);
			print line();
        }
    }
}
Ban(1);
cookie:
Cetak("Register",register_link);
print line();
if(!Simpan("Cookie"))print "\n".line();
if(!ua())print "\n".line();

if(!$cek_api_input){
	$apikey = MenuApi();
	if(provider_api == "Multibot"){
		$api = New ApiMultibot($apikey);
	}else{
		$api = New ApiXevil($apikey);
	}
	$cek_api_input = 1;
}

print p."Jangan lupa \033[101m\033[1;37m Subscribe! \033[0m youtub saya :D";sleep(2);
//system("termux-open-url ".youtube);
Ban(1);
$r = GetDashboard();
if(!$r["user"]){
	print Error("Session expired".n);
	hapus("Cookie");
	sleep(3);
	print line();
	goto cookie;
}

Cetak("Username",$r["user"]);
Cetak("Balance",$r["balance"].'-'.$r["bits"]);
Cetak("Token",$r["token"]);
Cetak("Bal_Api",$api->getBalance());
print line();
menu:
Menu(1, "Ptc");
Menu(2, "Faucet");
Menu(3, "AutoClaim");
$pil = readline(Isi("Number"));
print line();
if($pil == 1){
	getPtc();
	goto menu;
}elseif($pil == 2){
	getFaucet();
	goto menu;
}elseif($pil == 3){
	if(!getAutoclaim())goto cookie;
	goto menu;
}else{
	print Error("Bad Number\n");
	print line();
	goto menu;
}