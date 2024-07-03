<?php
const
host = "https://adbeast.org/",
register_link = "https://adbeast.org/?ref=1424",
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
	$data['user'] = explode('</font>', explode('<font class="text-success">', $r)[1])[0];
	$data['balance'] = explode('</b>', explode('Account Balance <div class="text-primary"><b>', $r)[1])[0];
	$data['bits'] = explode('</b>', explode('Coins Value <div class="text-success"><b>', $r)[1])[0];
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
			Cetak("Balance",$r["balance"].'-'.$r["bits"]);
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
		$r = Internal(host, h())[1];
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
		
		$cap = $api->RecaptchaV2($recaptcha, host);
		if(!$cap)continue;
		$data = 'a=getFaucet&token='.$token.'&captcha=1&challenge=false&response='.$cap;
		$r = json_decode(Internal(host.'system/ajax.php', h(1), $data)[1], 1);
		if ($r['status'] == 200) {
			print Sukses(str_replace([" Congratulations, your ","was","and you won"],["","->","->"],strip_tags($r["message"])));
			$r = GetDashboard();
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
Cetak("Bal_Api",$api->getBalance());
print line();
getPtc();
getFaucet();
exit;