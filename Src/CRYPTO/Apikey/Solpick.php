<?php
const
host = "https://solpick.io/",
register_link = "https://solpick.io/?ref=iewilmaestro",
typeCaptcha = "hcaptcha",
youtube = "https://youtube.com/@iewil";

function h($data=0){
	preg_match('@^(?:https://)?([^/]+)@i',host,$host);
	$h[] = "Host: ".$host[1];
	$h[] = "x-requested-with: XMLHttpRequest";
	$h[] = "Cookie: ".simpan("Cookie");
	$h[] = "User-Agent: ".ua();
	return $h;
}

function GetDashboard(){
	$r = curl(host.'faucet.php',h())[1];
	$data['user'] = trim(explode('<',explode('16px;font-weight:bold">Welcome back, ',$r)[1])[0]);
	$data['bal'] = explode('<',explode('class="user_balance">',$r)[1])[0];
	return $data;
}

function HourlyFaucet($api){
	while(true){
		$r = curl(host.'faucet.php',h());
		$cek = GlobalCheck($r[1]);
		if($cek['cf']){
			print Error("Cloudflare Detect\n");
			hapus("Cookie");
			print line();
			return 'cf';
		}
		$tmr = explode('|',explode('select_hourly_faucet|',$r[1])[1])[0];
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $r[0], $matches);
		$cookies = array();
		foreach($matches[1] as $item) {
			parse_str($item, $cookie);
			$cookies = array_merge($cookies, $cookie);
		}
		$cap = $api->Hcaptcha("f67eb825-e7db-46b8-a48c-819b59bad81c", host.'faucet.php');
		if(!$cap)continue;
		$data = 'action=claim_hourly_faucet&g-recaptcha-response='.$cap.'&h-captcha-response=null&captcha=&ft=&csrf_test_name='.$cookies['csrf_cookie_name'];
		
		$r = json_decode(curl(host.'process.php',h(),$data)[1],1);
		if($r["ret"]){
			Cetak("Number",$r["num"]);
			print Sukses($r["mes"]);
			Cetak("Balance",GetDashboard()["bal"]);
			Cetak("Bal_Api",$api->getBalance());
			print line();
		}else{
			print_r($r);
			print Error("Please wait for a minutes\n");
		}
		Tmr(3600);
	}
}

function ClaimBonus(){
	while(true){
		$r = curl(host.'faucet.php',h());
		$bonus = explode('</span>',explode('<span id="free_spins">',$r[1])[1])[0];
		if(!$bonus){
			print Error("No Bonus\n");
			break;
		}
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $r[0], $matches);
		$cookies = array();
		foreach($matches[1] as $item) {
			parse_str($item, $cookie);
			$cookies = array_merge($cookies, $cookie);
		}
		$data = "action=claim_bonus_faucet&csrf_test_name=".$cookies['csrf_cookie_name'];
		$r = json_decode(curl(host.'process.php',h(),$data)[1],1);
		if($r["ret"]){
			Cetak("Number",$r["num"]);
			print Sukses($r["mes"]);
			Cetak("Balance",GetDashboard()["bal"]);
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
Cetak("Balance",$r["bal"]);
Cetak("Bal_Api",$api->getBalance());
print line();

menu:
$r = curl(host.'faucet.php',h());
$bonus = explode('</span>',explode('<span id="free_spins">',$r[1])[1])[0];

Menu(1,"Claim Bonus [$bonus]");
Menu(2, "Hourly Bonus [Unlimited]");
$pil = readline(Isi("Nomor"));
print line();
if($pil == 1)ClaimBonus();
if($pil == 2){
	$x = HourlyFaucet($api);
	if($x == 'cf')goto cookie;
}
goto menu;