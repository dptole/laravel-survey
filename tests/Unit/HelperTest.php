<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

use App\Helper;

class HelperTest extends TestCase
{
  public function lsrGetLanguageRegionsProvider() {
    return [
      [ 'pt-BR,en-US',
        'English/United States, Portuguese/Brazil'
      ]
    ];
  }

  public function tzGetCountriesProvider() {
    return [
      [ 'Argentina, Brazil (Brasilia), Falkland Islands, French Guiana, Greenland (Nuuk), Saint Pierre and Miquelon, Suriname, Uruguay',
        'Bahrain, Belarus, Comoros, Djibouti, Eritrea, Ethiopia, Iraq, Kenya, Kuwait, Madagascar, Qatar, Russia (Moscow), Saudi Arabia, Somalia, South Sudan, Tanzania, Turkey, Uganda, Yemen'
      ]
    ];
  }

  public function getIpFromRequestInfoProvider() {
    $x_forwarded_for = (object)[
      'headers' => (object)[
        'x-forwarded-for' => ['192.168.0.1']
      ]
    ];
    $ip = (object)[
      'headers' => (object)[],
      'ips' => ['192.168.0.2']
    ];

    $ip_not_found = (object)[
      'headers' => (object)[]
    ];

    return [
      [$x_forwarded_for, $ip, $ip_not_found]
    ];
  }

  public function testGetPusherOptions()
  {
    $pusher_options = Helper::getPusherOptions();

    $this->assertEquals(2, count($pusher_options));

    $this->assertArrayHasKey('cluster', $pusher_options);
    $this->assertIsString($pusher_options['cluster'], true);

    $this->assertArrayHasKey('encrypted', $pusher_options);
    $this->assertEquals(true, $pusher_options['encrypted']);
  }

  public function testUrlRemoveDomain()
  {
    $url1 = Helper::urlRemoveDomain('https://dptole.ngrok.io/laravel');
    $this->assertEquals('/laravel', $url1);

    $url2 = Helper::urlRemoveDomain('https://dptole.ngrok.io');
    $this->assertEquals('', $url2);
  }

  public function testIsGoogleReCaptchaEnabled() {
    $this->assertIsBool(Helper::isGoogleReCaptchaEnabled());
  }

  public function testIsPusherEnabled() {
    $this->assertIsBool(Helper::isPusherEnabled());
  }

  public function testIsValidHTTPStatus() {
    $status1 = Helper::isValidHTTPStatus(0);
    $status2 = Helper::isValidHTTPStatus(100);
    $status3 = Helper::isValidHTTPStatus(600);

    $this->assertFalse($status1);
    $this->assertTrue($status2);
    $this->assertFalse($status3);
  }

  public function testIsSuccessHTTPStatus() {
    $status1 = Helper::isSuccessHTTPStatus(0);
    $status2 = Helper::isSuccessHTTPStatus(200);
    $status3 = Helper::isSuccessHTTPStatus(299);

    $this->assertFalse($status1);
    $this->assertTrue($status2);
    $this->assertTrue($status3);
  }

  public function testGenerateRandomString() {
    $random_string_length1 = 0;
    $random_string_length2 = 5;
    $random_string_length3 = 32;

    $random_string1 = Helper::generateRandomString($random_string_length1);
    $random_string2 = Helper::generateRandomString($random_string_length2);
    $random_string3 = Helper::generateRandomString($random_string_length3);

    $this->assertIsString($random_string1);
    $this->assertEquals(10, strlen($random_string1));

    $this->assertIsString($random_string2);
    $this->assertEquals($random_string_length2, strlen($random_string2));

    $this->assertIsString($random_string3);
    $this->assertEquals(10, strlen($random_string3));
  }

  public function testIsPositiveInteger() {
    $int1 = Helper::isPositiveInteger(-1);
    $int2 = Helper::isPositiveInteger(1);
    $int3 = Helper::isPositiveInteger(0);
    $int4 = Helper::isPositiveInteger(1.2);

    $this->assertFalse($int1);
    $this->assertTrue($int2);
    $this->assertTrue($int3);
    $this->assertFalse($int4);
  }

  public function testCreateCarbonRfc1123String() {
    $date1 = Helper::createCarbonRfc1123String('2020-02-02');
    $date2 = Helper::createCarbonRfc1123String('2030-02-02T10:20:30');

    $this->assertIsString($date1);
    $this->assertEquals('Sun, 02 Feb 2020 00:00:00 +0000', $date1);

    $this->assertIsString($date2);
    $this->assertEquals('Sat, 02 Feb 2030 10:20:30 +0000', $date2);
  }

  public function testCreateCarbonDiffForHumans() {
    // N <seconds/minutes/hours/days/weeks/months/years> ago
    $datetime_ago = date('Y-m-d\TH:i:s', time() - 86400);
    $date_diff1 = Helper::createCarbonDiffForHumans($datetime_ago);

    preg_match('/^(\d+)/', $date_diff1, $match);
    $this->assertIsArray($match);
    $this->assertEquals(2, count($match));
    $this->assertIsNumeric($match[0]);

    $this->assertIsString($date_diff1);
    $this->assertStringContainsString('ago', $date_diff1);

    // now (1 second ago)
    $datetime_now = date('Y-m-d\TH:i:s', time());
    $date_diff2 = Helper::createCarbonDiffForHumans($datetime_now);

    preg_match('/^(\d+)/', $date_diff2, $match);
    $this->assertIsArray($match);
    $this->assertEquals(2, count($match));
    $this->assertIsNumeric($match[0]);

    $this->assertIsString($date_diff2);
    $this->assertEquals('1 second ago', $date_diff2);

    // N <seconds/minutes/hours/days/weeks/months/years> from now
    $datetime_from_now = date('Y-m-d\TH:i:s', time() + 86400);
    $date_diff3 = Helper::createCarbonDiffForHumans($datetime_from_now);

    preg_match('/^(\d+)/', $date_diff3, $match);
    $this->assertIsArray($match);
    $this->assertEquals(2, count($match));
    $this->assertIsNumeric($match[0]);

    $this->assertIsString($date_diff3);
    $this->assertStringContainsString('from now', $date_diff3);
  }

  public function testCreateCarbonDate() {
    $carbon = Helper::createCarbonDate('2000-01-01');
    $this->assertInstanceOf(Carbon::class, $carbon);
  }

  public function testLoadLSR() {
    $this->assertNull(Helper::$lsr);

    Helper::loadLSR();

    $this->assertIsObject(Helper::$lsr);
    $this->assertGreaterThan(0, count((array)Helper::$lsr));

    $this->assertObjectHasAttribute('language', Helper::$lsr);
    $this->assertObjectHasAttribute('extlang', Helper::$lsr);
    $this->assertObjectHasAttribute('script', Helper::$lsr);
    $this->assertObjectHasAttribute('region', Helper::$lsr);
    $this->assertObjectHasAttribute('variant', Helper::$lsr);
    $this->assertObjectHasAttribute('grandfathered', Helper::$lsr);
    $this->assertObjectHasAttribute('redundant', Helper::$lsr);

    $this->assertIsArray(Helper::$lsr->language);
    $this->assertGreaterThan(0, count(Helper::$lsr->language));

    $this->assertIsArray(Helper::$lsr->extlang);
    $this->assertGreaterThan(0, count(Helper::$lsr->extlang));

    $this->assertIsArray(Helper::$lsr->script);
    $this->assertGreaterThan(0, count(Helper::$lsr->script));

    $this->assertIsArray(Helper::$lsr->region);
    $this->assertGreaterThan(0, count(Helper::$lsr->region));

    $this->assertIsArray(Helper::$lsr->variant);
    $this->assertGreaterThan(0, count(Helper::$lsr->variant));

    $this->assertIsArray(Helper::$lsr->grandfathered);
    $this->assertGreaterThan(0, count(Helper::$lsr->grandfathered));

    $this->assertIsArray(Helper::$lsr->redundant);
    $this->assertGreaterThan(0, count(Helper::$lsr->redundant));
  }

  public function testLoadTimezones() {
    $this->assertNull(Helper::$tzs);

    Helper::loadTimezones();

    $this->assertIsObject(Helper::$tzs);
    $this->assertGreaterThan(0, count((array)Helper::$tzs));

    foreach(Helper::$tzs as $tz => $countries):
      preg_match('/^(UTC).(\d+):(\d+)$/', $tz, $match);

      $this->assertIsArray($match);
      $this->assertEquals(4, count($match));
      $this->assertEquals('UTC', $match[1]);
      $this->assertIsNumeric($match[2]);
      $this->assertIsNumeric($match[3]);

      $this->assertIsArray($countries);
      $this->assertGreaterThan(0, count($countries));
    endforeach;
  }

  /** @dataProvider lsrGetLanguageRegionsProvider */
  public function testLsrGetLanguageRegions($regions1, $regions2) {
    $regions = Helper::lsrGetLanguageRegions($regions1);
    $this->assertEquals($regions2, $regions);
  }

  /** @dataProvider tzGetCountriesProvider */
  public function testTzGetCountries($countries1, $countries2) {
    $countries = Helper::tzGetCountries(3 * 60); // -3h from GMT
    $this->assertEquals($countries1, $countries);

    $countries = Helper::tzGetCountries(3 * 60, false); // +3h from GMT
    $this->assertEquals($countries2, $countries);
  }

  /** @dataProvider getIpFromRequestInfoProvider */
  public function testGetIpFromRequestInfo($x_forwarded_for, $ip, $ip_not_found) {
    $ip1 = Helper::getIpFromRequestInfo($x_forwarded_for);
    $ip2 = Helper::getIpFromRequestInfo($ip);
    $ip3 = Helper::getIpFromRequestInfo($ip_not_found);

    $this->assertEquals('192.168.0.1', $ip1);
    $this->assertEquals('192.168.0.2', $ip2);
    $this->assertFalse($ip3);
  }

  /** @dataProvider getIpFromRequestInfoProvider */
  public function testGetDbIpUrlFromRequestInfo($x_forwarded_for, $ip, $ip_not_found) {
    $ip1 = Helper::getDbIpUrlFromRequestInfo($x_forwarded_for);
    $ip2 = Helper::getDbIpUrlFromRequestInfo($ip);
    $ip3 = Helper::getDbIpUrlFromRequestInfo($ip_not_found);

    $this->assertEquals('https://db-ip.com/192.168.0.1', $ip1);
    $this->assertEquals('https://db-ip.com/192.168.0.2', $ip2);
    $this->assertFalse($ip3);
  }

  public function testGetRequestIp() {
    $rip1 = Helper::getRequestIp();
    $this->assertFalse($rip1);

    $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
    $rip2 = Helper::getRequestIp();
    $this->assertEquals($_SERVER['REMOTE_ADDR'], $rip2);

    // This function calls https://www.php.net/getallheaders
    $nothing_from_apache = getallheaders();
    $this->assertIsArray($nothing_from_apache);
    $this->assertEmpty($nothing_from_apache);
  }

  public function testDbIpDecorateResponse() { // ($ip_info, $ip) {
    /*
    $decorated_response = Helper::dbIpDecorateResponse($ip_info, $ip);

    This method should have a dependency on testDbIpGetIpInfo's return
    But because I don't know how to test that method
    this one will not be tested either
    */
    $this->assertFalse(false);
  }

  public function testDbIpGetIpInfo() {
    /*
    This call sends a HTTP request
    The result of this request may vary over time
    The result of this request may vary over environment
    The result of this request may vary depending on local configurations
    I don't know how to test that

    $ipinfo1 = Helper::dbIpGetIpInfo('2804:14c:3ba1:35ac:25d3:85a5:a378:620f');

    object(stdClass)#9748 (18) {
      ["Address type"]=>
      string(5) "IPv6 "
      ["ASN"]=>
      string(18) "28573 - CLARO S.A."
      ["ISP"]=>
      string(10) "Claro S.A."
      ["Organization"]=>
      string(9) "Claro S.A"
      ["Security / Crawler"]=>
      string(2) "No"
      ["Security / Proxy"]=>
      string(2) "No"
      ["Security / Attack source"]=>
      string(2) "No"
      ["Country"]=>
      string(7) "Brazil "
      ["State / Region"]=>
      string(10) "São Paulo"
      ["City"]=>
      string(8) "Campinas"
      ["Zip / Postal code"]=>
      string(9) "13000-000"
      ["Weather station"]=>
      string(19) "BRXX0050 - Campinas"
      ["Coordinates"]=>
      string(18) "-22.9099, -47.0626"
      ["Timezone"]=>
      string(25) "America/Sao_Paulo (UTC-2)"
      ["Languages"]=>
      string(17) "pt-BR, es, en, fr"
      ["Currency"]=>
      string(10) "Real (BRL)"
      ["Elapsed"]=>
      int(1453)
      ["Ip"]=>
      string(38) "2804:14c:3ba1:35ac:25d3:85a5:a378:620f"
    }
    */
    $this->assertFalse(false);
  }

  public function testDbIpGetIpInfoFromHtml() {
    /*
    Essentially the same issue as testDbIpGetIpInfo

    $ipinfo1 = Helper::dbIpGetIpInfoFromHtml('2804:14c:3ba1:35ac:25d3:85a5:a378:620f');
    */
    $this->assertFalse(false);
  }

  public function testHtmlTrim() {
    $trim1 = Helper::htmlTrim('  trimmed spaces   ');
    $this->assertEquals('trimmed spaces', $trim1);

    $trim2 = Helper::htmlTrim("\x09\x09\x09trimmed spaces\x09\x09\x09");
    $this->assertEquals('trimmed spaces', $trim2);

    $trim3 = Helper::htmlTrim("\xa0\xa0\xa0trimmed spaces\xa0\xa0\xa0");
    $this->assertEquals('trimmed spaces', $trim3);

    $trim4 = Helper::htmlTrim("\xc2\xc2\xc2trimmed spaces\xc2\xc2\xc2");
    $this->assertEquals('trimmed spaces', $trim4);
  }

  public function testMs() {
    $ms = Helper::ms();
    $this->assertIsNumeric($ms);
    $this->assertGreaterThan(time() - 1, $ms);
  }

  public function testGetGoogleReCaptchaApiAsset() {
    $this->expectOutputString('<script async src="https://www.google.com/recaptcha/api.js"></script>');
    $this->assertNull(Helper::getGoogleReCaptchaApiAsset());
  }

  public function testGetDotEnvFilePath() {
    $env_path = Helper::getDotEnvFilePath();
    $this->assertIsString($env_path);
    $this->assertIsString(file_get_contents($env_path));
  }

  public function testGetDotEnvFileVar() {
    $value = Helper::getDotEnvFileVar('APP_ENV');
    $this->assertEquals('testing', $value);
  }

  public function testGetDotEnvFile() {
    $dot_env_file = Helper::getDotEnvFile();

    $this->assertIsArray(Helper::$dot_env_file);
    $this->assertGreaterThan(0, count(Helper::$dot_env_file));

    $this->assertEquals('testing', $dot_env_file['APP_ENV']);
  }

  public function testGetDotEnvFileRaw() {
    $this->assertIsString(Helper::getDotEnvFileRaw());
  }

  public function testWriteDotEnvFileRaw() {
    $e = Helper::getDotEnvFileRaw();
    $written_bytes = Helper::writeDotEnvFileRaw($e);
    $this->assertIsNumeric($written_bytes);
    $this->assertEquals(strlen($e), $written_bytes);
  }

  public function testUpdateDotEnvFileVars() {
    $e = Helper::getDotEnvFileRaw();

    $written_bytes = Helper::updateDotEnvFileVars(['APP_ENV' => 'testing2']);
    $this->assertIsNumeric($written_bytes);
    $this->assertEquals(strlen($e) + 1, $written_bytes);

    $written_bytes = Helper::updateDotEnvFileVars(['APP_ENV' => 'testing']);
    $this->assertIsNumeric($written_bytes);
    $this->assertEquals(strlen($e), $written_bytes);
  }

  public function testIsValidReCaptchaToken() {
    /*
    This function calls an external API that requires secret
    credentials and an once valid token... how to test...

    $valid = Helper::isValidReCaptchaToken($site_secret, $token);
    $this->assertIsBool($valid);

    $invalid = Helper::isValidReCaptchaToken($site_secret, $token);
    $this->assertIsBool($invalid);
    */
    $this->assertFalse(false);
  }

  public function testValidateSystemUrlPrefix() {
    $this->assertTrue(Helper::validateSystemUrlPrefix(''));
    $this->assertFalse(Helper::validateSystemUrlPrefix('invalid'));
    $this->assertTrue(Helper::validateSystemUrlPrefix('/valid'));
    $this->assertFalse(Helper::validateSystemUrlPrefix('/invalid/'));
    $this->assertFalse(Helper::validateSystemUrlPrefix('/invalid//'));
    $this->assertFalse(Helper::validateSystemUrlPrefix('/ invalid'));
  }

  public function testGetPendingDotEnvFileConfigs() {
    $this->assertIsArray(Helper::getPendingDotEnvFileConfigs());
  }

  public function testHasPendingDotEnvFileConfigs() {
    $this->assertIsBool(Helper::hasPendingDotEnvFileConfigs());
  }

  public function testArePusherConfigsValid() {
    /*
    This function calls an external API that requires secret
    credentials... how to test...

    $valid = Helper::arePusherConfigsValid($auth_key, $app_id, $cluster, $secret);
    $this->assertIsBool($valid);

    $invalid = Helper::arePusherConfigsValid($auth_key, $app_id, $cluster, $secret);
    $this->assertIsBool($invalid);
    */
    $this->assertFalse(false);
  }

  public function testIsMaxMindGeoIpEnabled() {
    $this->assertFalse(Helper::isMaxMindGeoIpEnabled());
    $_SERVER['MM_IP_COUNTRY_CODE'] = 'BR';
    $this->assertTrue(Helper::isMaxMindGeoIpEnabled());
  }
}
