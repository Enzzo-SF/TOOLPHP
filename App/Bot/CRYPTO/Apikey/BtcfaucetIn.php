<?php
const
register_link = "https://btcfaucet.in/?r=18jswG2t9EZrnHju5dyiYw1yGbkcrTSgJg",
host = "https://btcfaucet.in/",
typeCaptcha = "RecaptchaV2",
youtube = "https://youtube.com/c/iewil",
ref = "?r=18jswG2t9EZrnHju5dyiYw1yGbkcrTSgJg";

function h($ref=0){
	preg_match('@^(?:https://)?([^/]+)@i',host,$host);
	$h = [
	"Host: ".$host[1],
	"content-type: application/x-www-form-urlencoded",
	"user-agent: ".ua(),
	"referer: ".host.ref,
	"accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
	"accept-language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7"];
	if($ref){
		$h = array_merge($h,["referer: ".$ref]);
	}
	return $h;
}
Ban(1);
cookie:
Cetak("Register",register_link);
print line();
$email = simpan("Email_Faucetpay");
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

Ban(1);
print p."Jangan lupa \033[101m\033[1;37m Subscribe! \033[0m youtub saya :D";sleep(2);
//system("termux-open-url ".youtube);
Ban(1);
hapus("cookie.txt");

Cetak("Wallet", $email);
print line();

while(true){
	$r = curl(host.ref,h(),'',1)[1];
	if(preg_match('/banned.php/',$r)){
		jam();
		print Error("Sorry your ip is banned".n);
		print Error("Ganti ip dengan mode pesawat dulu".n);
		print line();
		exit;
	}
	$bal = explode(' ',explode(' Balance: ',$r)[1])[0];
	if($bal < 2){
		jam();
		print Error("Sufficient funds".n);
		exit;
	}
	$token = explode('"',explode('<input type="text" name="',$r)[1])[0];
	$sitekey = explode('"',explode('<div class="g-recaptcha" data-sitekey="',$r)[1])[0];
	$cap = $api->RecaptchaV2($sitekey, host.ref);
	if(!$cap)continue;
	$atb = $api->AntiBot($r);
	if(!$atb)continue;
	
	$data = $token."=".urlencode($email)."&g-recaptcha-response=".$cap."&antibotlinks=".$atb;
	
	$r = curl(host.ref,h(),$data,1)[1];
	$ss = explode('<a',explode('<div class="alert alert-success">',$r)[1])[0];//2 satoshi was sent to you <a
	$wr = explode('</div>',explode('<div class="alert alert-danger">',$r)[1])[0];
	if($wr)Tmr(60);
	if($ss){
		print Sukses($ss);
		print line();
		Tmr(60);
	}
}