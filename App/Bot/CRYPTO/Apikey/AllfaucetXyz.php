<?php
const
host = "https://allfaucet.xyz/",
register_link = "https://allfaucet.xyz/?r=15759",
typeCaptcha = "RecaptchaV2",
youtube = "https://youtu.be/xSEIsTlVqig";

function h(){
	$h[] = "Cookie: ".simpan("Cookie");
	$h[] = "User-Agent: ".ua();
	return $h;
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

$r = curl(host."dashboard",h())[1];
$user = explode('</b>',explode('<span id="greeting"></span> <b>',$r)[1])[0];
if(!$user){
	print Error("Session expired".n);
	hapus("Cookie");
	print line();
	goto cookie;
}
$bal = explode(' USD</b>',explode('<b>Account Balance: ',$r)[1])[0];
$min = explode('"',explode('name="amount" min="',$r)[1])[0];

Cetak("Username",$user);
Cetak("Balance",$bal);
$address = explode('">',explode('placeholder="Connect Your FaucetPay Email" value="',$r)[1])[0];
$csrf = explode('">',explode('<input type="hidden" name="csrf_token_name" id="token" value="',$r)[1])[0];
$token = explode('">',explode('<input type="hidden" name="token" value="',$r)[1])[0];
if(!$address){
	$wallet = readline(Isi("email fp"));
	$data = "csrf_token_name=".$csrf."&token=".$token."&wallet=".str_replace('@','%40',$wallet);
	$r = curl(host."dashboard/authorize",h(),$data)[1];
	$ss = explode("'",explode("html: '",$r)[1])[0];
	if($ss){
		print Sukses($ss);
		print line();
		goto cookie;
	}
}
Cetak("Wallet",$address);
Cetak("Bal_Api",$api->getBalance());
print line();
if($bal >= $min){
	$csrf = explode('">',explode('<input type="hidden" name="csrf_token_name" id="token" value="',$r)[1])[0];
	$token = explode('">',explode('<input type="hidden" name="token" value="',$r)[1])[0];
	$coin = explode('"',explode('value="',explode('<input class="form-check-input" type="radio" name="currency"',$r)[1])[1])[0];
	$data = "csrf_token_name=".$csrf."&token=".$token."&amount=".substr($bal,0,5)."&currency=".$coin;
	$r = curl(host."withdraw",h(),$data)[1];
	$ss = explode("account!'",explode("html: '0.",$r)[1])[0];
	if($ss){
		print Sukses($ss);
		print line();
	}
}

$r = curl(host."dashboard",h())[1];
$con = explode('/faucet/currency/',$r);
while(true){
	foreach($con as $a => $coins){
		if($a == 0)continue;
		$coin = explode('"',$coins)[0];
		$r = curl(host."faucet/currency/".$coin,h())[1];
		if(preg_match('/Firewall/',$r)){firewall();continue;}
		if(preg_match('/An uncaught Exception was encountered/',$r)){print Error("An uncaught Exception was encountered\n");sleep(2);print "\r                                 \r";tmr(60);continue;}
		if(preg_match('/Just a moment.../',$r)){hapus("Cookie");print Error("Cloudflare\n");print line();goto cookie;}
		if(preg_match('/Please confirm your email address to be able to claim or withdraw/',$r)){print Error("Please confirm your email address to be able to claim or withdraw\n");print line();exit;}
		if($res){
			if($res[$coin] > 2)continue;
		}
		if(preg_match("/You don't have enough energy for Auto Faucet!/",$r)){exit(Error("You don't have enough energy for Auto Faucet!\n"));}
		if(preg_match('/Daily claim limit/',$r)){
			$res = his([$coin=>3],$res);
			print Cetak($coin,"Daily claim limit");continue;}
		$status_bal = explode('</span>',explode('<span class="badge badge-danger">',$r)[1])[0];
		if($status_bal == "Empty"){
			$res = his([$coin=>3],$res);
			print Cetak($coin,"Sufficient funds");continue;
		}
		$tmr = explode('-',explode('var wait = ',$r)[1])[0];
		if($tmr){
			tmr($tmr);
		}
		preg_match('/(\d{1,})\/(\d{1,})/',$r,$sisa);
		if($sisa[1] <= null){
			$res = his([$coin=>3],$res);
			print Cetak($coin,"Daily claim limit");continue;
		}
		$csrf = explode('">',explode('<input type="hidden" name="csrf_token_name" id="token" value="',$r)[1])[0];
		$token = explode('">',explode('<input type="hidden" name="token" value="',$r)[1])[0];
		$sitekey = explode('"',explode('<div class="g-recaptcha" data-sitekey="',$r)[1])[0];
		if(!$sitekey){print Error("Sitekey Error\n"); continue;}
		$cap = $api->RecaptchaV2($sitekey, host."faucet/currency/".$coin);
		if(!$cap)continue;
		$atb = $api->Antibot($r);
		if(!$atb)continue;
		
		$data = "antibotlinks=$atb&csrf_token_name=$csrf&token=$token&captcha=recaptchav2&g-recaptcha-response=".$cap;
		$r = curl(host."faucet/verify/".$coin,h(),$data)[1];
		if(preg_match('/Shortlink in order to claim from the faucet!/',$r)){
			exit(Error(explode("'",explode("html: '",$r)[1])[0]));
		}
		$ss = explode("account!'",explode("html: '0.",$r)[1])[0];
		$wr = explode(".",explode("html: '",$r)[1])[0];
		$ban = explode('</div>',explode('<div class="alert text-center alert-danger"><i class="fas fa-exclamation-circle"></i> Your account',$r)[1])[0];
		if(preg_match('/Shortlink in order to claim from the faucet!/',$r)){
			exit(Error(explode("'",explode("html: '",$r)[1])[0]));
		}
		if($ban){
			exit(Error("Your account".$ban.n));
		}
		if(preg_match('/sufficient funds/',$r)){
			$res = his([$coin=>3],$res);
			print Cetak($coin,"Sufficient funds");
			continue;
		}
		if($ss){
			print Cetak($coin,$sisa[0]);
			print Sukses("0.".$ss);
			Cetak("Bal_Api",$api->getBalance());
			print line();
			$res = his([$coin=>1],$res);
		}elseif($wr){
			print Error(substr($wr,0,30));
			sleep(3);
			print "\r                  \r";
			$res = his([$coin=>1],$res);
		}else{
			print Error("Something wrong\n");
			sleep(3);
			print "\r                  \r";
			$res = his([$coin=>1],$res);
		}
	}
	if(!$res){
		continue; 
	}
	if(min($res) > 2)break;
}