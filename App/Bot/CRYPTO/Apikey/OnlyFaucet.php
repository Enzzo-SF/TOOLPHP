<?php
const
host = "https://onlyfaucet.com/",
register_link = "https://onlyfaucet.com/?r=985",
typeCaptcha = "Antibot",
youtube = "https://youtube.com/@iewil";

function h($data=0){
	preg_match('@^(?:https://)?([^/]+)@i',host,$host);
	$h[] = "Host: ".$host[1];
	if($data)$h[] = "Content-Length: ".strlen($data);;
	$h[] = "User-Agent: ".ua();
	$h[] = "Cookie: ".simpan("Cookie");
	return $h;
}

function Firewall(){
	global $api;
	while(1){
		$r = curl(host."firewall",h())[1];
		$csrf = explode('"',explode('name="csrf_token_name" value="',$r)[1])[0];
		$captcha = explode('"',explode('name="captchaType" value="',$r)[1])[0];
		$turnstile = explode('"',explode('<div class="cf-turnstile" data-sitekey="',$r)[1])[0];
		$recap = explode('"',explode('<div class="g-recaptcha" data-sitekey="',$r)[1])[0];
		if($turnstile){
			$cap = $api->Turnstile($turnstile, host."firewall");
			$data["cf-turnstile-response"] = $cap;
		}else
		if($recap){
			$cap = $api->RecaptchaV2($recap, host."firewall");
			$data["g-recaptcha-response"] = $cap;
		}else{
			continue;
		}
		if(!$cap)continue;
		$data["captchaType"] = $captcha;
		$data["csrf_token_name"] = $csrf;
		$r = curl(host."firewall/verify",h(),http_build_query($data))[1];
		if(preg_match('/Invalid Captcha/',$r))continue;
		Cetak("Firewall","Bypassed");
		return 0;
	}
}
function login($email){
	ulang:
	if($email == "purna.iera@gmail.com"){
		$r = curl(host,h(),'',1)[1];
	}else{
		$r = curl(register_link,h(),'',1)[1];
	}
	$csrf = explode('">',explode('<input type="hidden" name="csrf_token_name" id="token" value="',$r)[1])[0];
	$data = [
	"wallet" => simpan("Email"),
	"csrf_token_name" => $csrf
	];
	$r = curl(host."auth/login",h(),http_build_query($data),1)[1];
	$ss = explode("',",explode("html: '",$r)[1])[0];
	if($ss){
		print Sukses($ss);print line();sleep(5);
		ban(1);
	}else{
		print Error("Error!\n");
		sleep(3);
		print "\r              \r";
		hapus("cookie.txt");
		goto ulang;
	}
}
function getSl($coin, $api){
	$i = 0;
	ulang_Onlyfaucet:
	$r = curl(host.'links/currency/'.$coin,h())[1];
	$list = explode('<h4 class="card-title mt-0">',$r);
	foreach($list as $a => $cok){
		if($a == 0)continue;
		$name = explode('</h4>',$cok)[0];
		$link = explode('"',explode('<a href="',$cok)[1])[0];
		$sisa = explode('/',explode('<span class="badge badge-info">',$cok)[1])[0];
		if(!$sisa)continue;
		if(in_array($name, ['Clicksfly.me', 'Wefly.me', 'Urlsfly.me', 'Linksfly.me'])){
			$r = curl($link,h());
			if(preg_match('/You still have uncompleted shortlink/',$r[1])){
				$loc = explode('"',explode('location.href = "',$r[1])[1])[0];
				$r = curl($loc,h())[0];
				$location = explode('location: ',$r);
				foreach($location as $finallocation){
					$dest = trim(explode("\n", $finallocation)[0]);
					if(in_array(parse_url($dest)['host'], ['clicksfly.me', 'wefly.me', 'urlsfly.me', 'linksfly.me'])){
						$final = $dest;
						break;
					}
				}
			}
			if($final)break;
			$final = trim(explode("\n",explode('location:',$r[0])[1])[0]);
			break;
		}
	}
	print Error($final);
	$cap = @Captcha::fly($final);
	print "\r                               \r";
	if(!$cap){
		$i++;
		if($i > 3)return 0;
		goto ulang_Onlyfaucet;
	}
	tmr(30);
	$r = curl($cap,h())[1];
	$ss = explode("account!",explode("html: '0.",$r)[1])[0];
	if($ss){
		print Cetak($coin,$sisa);
		print Sukses("0.".str_replace("has been sent ","",strip_tags($ss)));
		Cetak("Bal_Api",$api->getBalance());
		print line();
		return 1;
	}else{
		return 0;
	}
}

Ban(1);
cookie:
Cetak("Register",register_link);
print line();
simpan("Cookie");
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

login:
//login($email);
$r = curl(host,h())[1];
if(!explode('Logout',$r)[1]){
	//login($email);
	hapus("Cookie");
	goto cookie;
}

//Cetak("Email",$email);
Cetak("Bal_Api",$api->getBalance());
print line();

gaslagi:
$con = explode('/faucet/currency/',$r);
$num = 0;
while(true){
	$cecker = curl(host,h())[1];
	if(!explode('Logout',$cecker)[1]){
		//login($email);
		hapus("Cookie");
		goto cookie;
	}
	foreach($con as $a => $coins){
		if($a == 0)continue;
		$coin = explode('"',$coins)[0];
		$r = curl(host."faucet/currency/".$coin,h())[1];
		if(preg_match('/Firewall/',$r)){firewall();continue;}
		if(preg_match('/An uncaught Exception was encountered/',$r)){print Error("An uncaught Exception was encountered\n");sleep(2);print "\r                                 \r";tmr(60);continue;}
		if(preg_match('/Just moment/',$r)){hapus("Cookie");print Error("Cloudflare\n");goto cookie;}
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
		$csrf = explode('">',explode('<input type="hidden" name="csrf_token_name" id="token" value="',$r)[1])[0];
		$hiden = explode('">',explode('<input type="hidden" name="token" value="',$r)[1])[0];
		$tmr = explode("-",explode('var wait = ',$r)[1])[0];
		//$sitekey = explode('"',explode('<div class="cf-turnstile" data-sitekey="',$r)[1])[0];
		$sisa = explode('</span>',explode('<span class="badge badge-info">',$r)[1])[0];
		//if($sitekey){
		//	$cap = $api->Turnstile($sitekey, host."faucet/currency/".$coin);
		//	if(!$cap)continue;
		//	$data = "csrf_token_name=".$csrf."&token=".$hiden."&captcha=turnstile&cf-turnstile-response=".$cap;
		//}else{
		//	$data = "csrf_token_name=".$csrf."&token=".$hiden;
		//}
		
		#atb only
		if(explode('\"',explode('rel=\"',$r)[1])[0]){
			$atb = $api->AntiBot($r);
			if(!$atb){print Error("Atb @".provider_api." Error\n"); continue;}
		}else{
			print Error("atb error!");
			sleep(6);
			print "\r                         \r";
			continue;
		}
		$data = "antibotlinks=$atb&csrf_token_name=".$csrf."&token=".$hiden;
		
		if($tmr){
			tmr($tmr);
		}
		
		$r = curl(host."faucet/verify/".$coin,h(),$data)[1];
		$ban = explode('</div>',explode('<div class="alert text-center alert-danger"><i class="fas fa-exclamation-circle"></i> Your account',$r)[1])[0];
		$ss = explode("account!",explode("html: '0.",$r)[1])[0];
		$wr = explode(".",explode("html: '",$r)[1])[0];
		if($ban){print "\r                      \r";exit(m."Your account".$ban.n);}
		if(preg_match('/You must complete at least/',$r)){
			$bp = getSl($coin, $api);
			if(!$bp){
				exit(Error(explode("'",explode("html: '",$r)[1])[0]));
			}
			$res = his([$coin=>1],$res);
		}
		if(preg_match('/sufficient funds/',$r)){
			$res = his([$coin=>3],$res);
			print Cetak($coin,"Sufficient funds");
			print line();
			continue;
		}
		if($ss){
			print Cetak($coin,$sisa);
			print Sukses("0.".str_replace("has been sent ","",strip_tags($ss)));
			Cetak("Bal_Api",$api->getBalance());
			print line();
			$res = his([$coin=>1],$res);
		}elseif($wr){
			print Error(substr($wr,0,30));
			sleep(3);
			print "\r                  \r";
			$res = his([$coin=>1],$res);
		}else{
			print Error("Server Down\n");
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