<?php
const
host = "https://ourcoincash.xyz/",
register_link = "https://ourcoincash.xyz/?r=3408",
typeCaptcha = "RecaptchaV2",
youtube = "https://youtu.be/xSEIsTlVqig";

function h(){
	$h[] = "Cookie: ".simpan("Cookie");
	$h[] = "User-Agent: ".ua();
	return $h;
}

function GetDashboard(){
	$r = curl(host.'dashboard',h())[1];
	$data['bal'] = explode('</p>', explode('<i class="fas fa-coins"></i> ', $r)[1])[0];
	return $data;
}
function faucet(){
	global $api;
	while(true){
		$data = "";
		$r = curl(host.'faucet',h())[1];
		$cek = GlobalCheck($r);
		if($cek['cf']){
			print Error("Cloudflare Detect\n");
			hapus("Cookie");
			print line();
			return 'cf';
		}
		if(preg_match('/Daily limit reached/',$r)){
			break;
		}
		$csrf = explode('"',explode('id="token" value="',$r)[1])[0];
		$token = explode('"',explode('name="token" value="',$r)[1])[0];
		$sitekey = explode('"',explode('data-sitekey="',$r)[1])[0];
		$tmr = explode(';',explode('let timer = ',$r)[1])[0];
		if(!$sitekey){
			print Error("Sitekey Error");
			sleep(6);
			print "\r                         \r";
			continue;
		}
		if($tmr){tmr($tmr);}
		if(explode('\"',explode('rel=\"',$r)[1])[0]){
			$atb = $api->AntiBot($r);
			if(!$atb){print Error("Atb @".provider_api." Error\n"); continue;}
			$data = "antibotlinks=".$atb."&";
		}
		$cap = $api->RecaptchaV2($sitekey, host.'faucet');
		if(!$cap)continue;
		
		$data .= "csrf_token_name=".$csrf."&token=".$token."&captcha=recaptchav2&g-recaptcha-response=".$cap;
		$r = curl(host.'faucet/verify',h(),$data)[1];
		$ss = explode('has',explode("text: '",$r)[1])[0];
		if($ss){
			print Sukses($ss);
			Cetak("Balance",GetDashboard()["bal"]);
			Cetak("Bal_Api",$api->getBalance());
			print line();
		}else {
			Cetak("Balance",GetDashboard()["bal"]);
			Cetak("Bal_Api",$api->getBalance());
			print line();
		}
	}
	print Error("Daily faucet limit\n");
	print line();
}
function ptc(){
	global $api;
	while(true){
		$r = curl(host.'ptc',h())[1];
		$cek = GlobalCheck($r);
		if($cek['cf']){
			print Error("Cloudflare Detect\n");
			hapus("Cookie");
			print line();
			return 'cf';
		}
		if(preg_match('/Daily limit reached/',$r)){
			break;
		}
		$id = explode("'",explode('/view/',$r)[1])[0];
		if(!$id){
			print Error("Ptc Habis\n");
			print line();
			return 0;
		}
		$r = curl(host."ptc/view/".$id,h())[1];
		$csrf = explode('"',explode('name="csrf_token_name" value="',$r)[1])[0];
		$token = explode('"',explode('name="token" value="',$r)[1])[0];
		$sitekey = explode('"',explode('data-sitekey="',$r)[1])[0];
		if(!$sitekey){
			print Error("Sitekey Error");
			sleep(6);
			print "\r                         \r";
			continue;
		}
		$tmr = explode(';',explode('let timer = ',$r)[1])[0];
		$ptc = explode('</title>',explode('<title>',$r)[1])[0];
		if(strlen($ptc) > 15){$ptc = substr($ptc,0,15);}
		Cetak("Visit",$ptc);
		if($tmr){
			tmr($tmr);
		}
        
		$cap = $api->RecaptchaV2($sitekey, host."ptc/view/".$id);
		if(!$cap)continue;
		
		$data = "captcha=recaptchav2&g-recaptcha-response=".$cap."&csrf_token_name=".$csrf."&token=".$token;
		$r = curl(host."ptc/verify/".$id,h(),$data)[1];
		$ss = explode('has',explode("text: '",$r)[1])[0];
		if($ss){
			print Sukses($ss);
			Cetak("Balance",GetDashboard()["bal"]);
			Cetak("Bal_Api",$api->getBalance());
			print line();
		}else {
			Cetak("Balance",GetDashboard()["bal"]);
			Cetak("Bal_Api",$api->getBalance());
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

$apikey = MenuApi();
if(provider_api == "Multibot"){
	$api = New ApiMultibot($apikey);
}else{
	$api = New ApiXevil($apikey);
}

print p."Jangan lupa \033[101m\033[1;37m Subscribe! \033[0m youtub saya :D";sleep(2);
//system("termux-open-url ".youtube);
Ban(1);

$r = GetDashboard();
if(!$r["bal"]){
	print Error("Session expired".n);
	hapus("Cookie");
	sleep(3);
	print line();
	goto cookie;
}
Cetak("Balance",$r["bal"]);
Cetak("Bal_Api",$api->getBalance());
print line();
while(true){
	$x = ptc();
	if($x == 'cf'){
		sleep(3);
		print line();
		goto cookie;
	}
	$x = faucet();
	if($x == 'cf'){
		sleep(3);
		print line();
		goto cookie;
	}
	tmr(600);
}