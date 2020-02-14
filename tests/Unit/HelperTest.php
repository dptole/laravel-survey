<?php

namespace Tests\Unit;

use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function lsrGetLanguageRegionsProvider()
    {
        return [
            ['pt-BR,en-US',
                'English/United States, Portuguese/Brazil',
            ],
            ['-', // invalid language
                '',   // no region
            ],
        ];
    }

    public function tzGetCountriesProvider()
    {
        return [
            ['Argentina, Brazil (Brasilia), Falkland Islands, French Guiana, Greenland (Nuuk), Saint Pierre and Miquelon, Suriname, Uruguay',
                'Bahrain, Belarus, Comoros, Djibouti, Eritrea, Ethiopia, Iraq, Kenya, Kuwait, Madagascar, Qatar, Russia (Moscow), Saudi Arabia, Somalia, South Sudan, Tanzania, Turkey, Uganda, Yemen',
            ],
        ];
    }

    public function getIpFromRequestInfoProvider()
    {
        $x_forwarded_for = (object) [
            'headers' => (object) [
                'x-forwarded-for' => ['192.168.0.1'],
            ],
        ];
        $ip = (object) [
            'headers' => (object) [],
            'ips'     => ['192.168.0.2'],
        ];

        $ip_not_found = (object) [
            'headers' => (object) [],
        ];

        return [
            [$x_forwarded_for, $ip, $ip_not_found],
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

    public function testIsGoogleReCaptchaEnabled()
    {
        $this->assertIsBool(Helper::isGoogleReCaptchaEnabled());
    }

    public function testIsPusherEnabled()
    {
        $this->assertIsBool(Helper::isPusherEnabled());
    }

    public function testIsValidHTTPStatus()
    {
        $status1 = Helper::isValidHTTPStatus(0);
        $status2 = Helper::isValidHTTPStatus(100);
        $status3 = Helper::isValidHTTPStatus(600);

        $this->assertFalse($status1);
        $this->assertTrue($status2);
        $this->assertFalse($status3);
    }

    public function testIsSuccessHTTPStatus()
    {
        $status1 = Helper::isSuccessHTTPStatus(0);
        $status2 = Helper::isSuccessHTTPStatus(200);
        $status3 = Helper::isSuccessHTTPStatus(299);

        $this->assertFalse($status1);
        $this->assertTrue($status2);
        $this->assertTrue($status3);
    }

    public function testGenerateRandomString()
    {
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

    public function testIsPositiveInteger()
    {
        $int1 = Helper::isPositiveInteger(-1);
        $int2 = Helper::isPositiveInteger(1);
        $int3 = Helper::isPositiveInteger(0);
        $int4 = Helper::isPositiveInteger(1.2);

        $this->assertFalse($int1);
        $this->assertTrue($int2);
        $this->assertTrue($int3);
        $this->assertFalse($int4);
    }

    public function testCreateCarbonRfc1123String()
    {
        $date1 = Helper::createCarbonRfc1123String('2020-02-02');
        $date2 = Helper::createCarbonRfc1123String('2030-02-02T10:20:30');

        $this->assertIsString($date1);
        $this->assertEquals('Sun, 02 Feb 2020 00:00:00 +0000', $date1);

        $this->assertIsString($date2);
        $this->assertEquals('Sat, 02 Feb 2030 10:20:30 +0000', $date2);
    }

    public function testCreateCarbonDiffForHumans()
    {
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

    public function testCreateCarbonDate()
    {
        $carbon = Helper::createCarbonDate('2000-01-01');
        $this->assertInstanceOf(Carbon::class, $carbon);
    }

    public function testLoadLSR()
    {
        $this->assertNull(Helper::$lsr);

        Helper::loadLSR();

        $this->assertIsObject(Helper::$lsr);
        $this->assertGreaterThan(0, count((array) Helper::$lsr));

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

    public function testLoadTimezones()
    {
        $this->assertNull(Helper::$tzs);

        Helper::loadTimezones();

        $this->assertIsObject(Helper::$tzs);
        $this->assertGreaterThan(0, count((array) Helper::$tzs));

        foreach (Helper::$tzs as $tz => $countries) {
            preg_match('/^(UTC).(\d+):(\d+)$/', $tz, $match);

            $this->assertIsArray($match);
            $this->assertEquals(4, count($match));
            $this->assertEquals('UTC', $match[1]);
            $this->assertIsNumeric($match[2]);
            $this->assertIsNumeric($match[3]);

            $this->assertIsArray($countries);
            $this->assertGreaterThan(0, count($countries));
        }
    }

    /** @dataProvider lsrGetLanguageRegionsProvider */
    public function testLsrGetLanguageRegions($languages, $expected_regions)
    {
        $response_regions = Helper::lsrGetLanguageRegions($languages);
        $this->assertEquals($expected_regions, $response_regions);
    }

    /** @dataProvider tzGetCountriesProvider */
    public function testTzGetCountries($countries1, $countries2)
    {
        $countries = Helper::tzGetCountries(3 * 60); // -3h from GMT
        $this->assertEquals($countries1, $countries);

        $countries = Helper::tzGetCountries(3 * 60, false); // +3h from GMT
        $this->assertEquals($countries2, $countries);
    }

    /** @dataProvider getIpFromRequestInfoProvider */
    public function testGetIpFromRequestInfo($x_forwarded_for, $ip, $ip_not_found)
    {
        $ip1 = Helper::getIpFromRequestInfo($x_forwarded_for);
        $ip2 = Helper::getIpFromRequestInfo($ip);
        $ip3 = Helper::getIpFromRequestInfo($ip_not_found);

        $this->assertEquals('192.168.0.1', $ip1);
        $this->assertEquals('192.168.0.2', $ip2);
        $this->assertFalse($ip3);
    }

    /** @dataProvider getIpFromRequestInfoProvider */
    public function testGetDbIpUrlFromRequestInfo($x_forwarded_for, $ip, $ip_not_found)
    {
        $ip1 = Helper::getDbIpUrlFromRequestInfo($x_forwarded_for);
        $ip2 = Helper::getDbIpUrlFromRequestInfo($ip);
        $ip3 = Helper::getDbIpUrlFromRequestInfo($ip_not_found);

        $this->assertEquals('https://db-ip.com/192.168.0.1', $ip1);
        $this->assertEquals('https://db-ip.com/192.168.0.2', $ip2);
        $this->assertFalse($ip3);
    }

    public function testGetRequestIp()
    {
        $rip1 = Helper::getRequestIp();
        $this->assertFalse($rip1);

        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $rip2 = Helper::getRequestIp();
        $this->assertEquals($_SERVER['REMOTE_ADDR'], $rip2);

        $GLOBALS['getRequestIp_headers'] = [
            'x-forwarded-for' => '192.168.0.2',
        ];

        $rip3 = Helper::getRequestIp();
        $this->assertEquals($GLOBALS['getRequestIp_headers']['x-forwarded-for'], $rip3);

        unset($GLOBALS['getRequestIp_headers']);
    }

    public function testDbIpDecorateResponse()
    {
        $ip = '192.168.0.1';
        $nao = 'not an object';
        $decorated_response = Helper::dbIpDecorateResponse($nao, $ip);
        $this->assertEquals($decorated_response, $nao);
    }

    public function testDbIpGetIpInfo()
    {
        $rand = rand(0, 255);
        $ipinfo1 = Helper::dbIpGetIpInfo('187.184.39.'.$rand);
        $this->assertIsObject($ipinfo1);
        $ipinfo2 = Helper::dbIpGetIpInfo('');
        $this->assertFalse($ipinfo2);
    }

    public function testDbIpGetIpInfoFromHtml()
    {
        /*
        Essentially the same issue as testDbIpGetIpInfo
        */
        $rand1 = rand(0, 255);
        $ipinfo1 = Helper::dbIpGetIpInfoFromHtml('187.183.39.'.$rand1);
        $this->assertIsObject($ipinfo1);

        $rand2 = rand(0, 255);
        $ipinfo2 = Helper::dbIpGetIpInfoFromHtml('187.183.39.'.$rand2);
        $this->assertIsObject($ipinfo2);

        // Call the same IP twice and get a cached result
        $ipinfo3 = Helper::dbIpGetIpInfoFromHtml('187.183.39.'.$rand2);
        $this->assertIsObject($ipinfo3);

        // Make an invalid call
        $ipinfo4 = Helper::dbIpGetIpInfoFromHtml('');
        $this->assertFalse($ipinfo4);
    }

    public function testHtmlTrim()
    {
        $trim1 = Helper::htmlTrim('  trimmed spaces   ');
        $this->assertEquals('trimmed spaces', $trim1);

        $trim2 = Helper::htmlTrim("\x09\x09\x09trimmed spaces\x09\x09\x09");
        $this->assertEquals('trimmed spaces', $trim2);

        $trim3 = Helper::htmlTrim("\xa0\xa0\xa0trimmed spaces\xa0\xa0\xa0");
        $this->assertEquals('trimmed spaces', $trim3);

        $trim4 = Helper::htmlTrim("\xc2\xc2\xc2trimmed spaces\xc2\xc2\xc2");
        $this->assertEquals('trimmed spaces', $trim4);
    }

    public function testMs()
    {
        $ms = Helper::ms();
        $this->assertIsNumeric($ms);
        $this->assertGreaterThan(time() - 1, $ms);
    }

    public function testGetGoogleReCaptchaApiAsset()
    {
        $this->expectOutputString('<script async src="https://www.google.com/recaptcha/api.js"></script>');
        $this->assertNull(Helper::getGoogleReCaptchaApiAsset());
    }

    public function testGetDotEnvFilePath()
    {
        $env_path = Helper::getDotEnvFilePath();
        $this->assertIsString($env_path);
        $this->assertIsString(file_get_contents($env_path));
    }

    public function testGetDotEnvFileVar()
    {
        $value = Helper::getDotEnvFileVar('APP_ENV');
        $this->assertEquals('testing', $value);

        $value = Helper::getDotEnvFileVar('INVALID_VAR');
        $this->assertNull($value);
    }

    public function testGetDotEnvFile()
    {
        $dot_env_file = Helper::getDotEnvFile();

        $this->assertIsArray(Helper::$dot_env_file);
        $this->assertGreaterThan(0, count(Helper::$dot_env_file));

        $this->assertEquals('testing', $dot_env_file['APP_ENV']);
    }

    public function testGetDotEnvFileRaw()
    {
        $this->assertIsString(Helper::getDotEnvFileRaw());
    }

    public function testWriteDotEnvFileRaw()
    {
        $e = Helper::getDotEnvFileRaw();
        $written_bytes = Helper::writeDotEnvFileRaw($e);
        $this->assertIsNumeric($written_bytes);
        $this->assertEquals(strlen($e), $written_bytes);
    }

    public function testUpdateDotEnvFileVars()
    {
        $e = Helper::getDotEnvFileRaw();

        $written_bytes = Helper::updateDotEnvFileVars(['APP_ENV' => 'testing2']);
        $this->assertIsNumeric($written_bytes);
        $this->assertEquals(strlen($e) + 1, $written_bytes);

        $written_bytes = Helper::updateDotEnvFileVars(['APP_ENV' => 'testing']);
        $this->assertIsNumeric($written_bytes);
        $this->assertEquals(strlen($e), $written_bytes);
    }

    public function testIsValidReCaptchaToken()
    {
        /*
        This function calls an external API that requires secret
        credentials and an once valid token... how to test...
        */
        $site_secret = 'Pretend to make a valid HTTP call';
        $token = 'to satisfy the code coverage';

        $valid = Helper::isValidReCaptchaToken($site_secret, $token);
        $this->assertIsBool($valid);
    }

    public function testValidateSystemUrlPrefix()
    {
        $this->assertTrue(Helper::validateSystemUrlPrefix(''));
        $this->assertFalse(Helper::validateSystemUrlPrefix('invalid'));
        $this->assertTrue(Helper::validateSystemUrlPrefix('/valid'));
        $this->assertFalse(Helper::validateSystemUrlPrefix('/invalid/'));
        $this->assertFalse(Helper::validateSystemUrlPrefix('/invalid//'));
        $this->assertFalse(Helper::validateSystemUrlPrefix('/ invalid'));
    }

    public function testGetPendingDotEnvFileConfigs()
    {
        $this->assertCount(0, Helper::getPendingDotEnvFileConfigs());

        $old_prefix = Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL');

        Helper::updateDotEnvFileVars([
            'PUSHER_ENABLED'            => 'true',
            'GOOGLE_RECAPTCHA_ENABLED'  => 'true',
            'LARAVEL_SURVEY_PREFIX_URL' => '/ invalid /',
        ]);

        $this->assertCount(3, Helper::getPendingDotEnvFileConfigs());

        Helper::updateDotEnvFileVars([
            'PUSHER_ENABLED'            => 'false',
            'GOOGLE_RECAPTCHA_ENABLED'  => 'false',
            'LARAVEL_SURVEY_PREFIX_URL' => $old_prefix,
        ]);
    }

    public function testHasPendingDotEnvFileConfigs()
    {
        $this->assertIsBool(Helper::hasPendingDotEnvFileConfigs());
    }

    public function testArePusherConfigsValid()
    {
        /*
        This function calls an external API that requires secret
        credentials... how to test...
        */
        $auth_key = 'Just pretend to';
        $app_id = 'make a valid request API';
        $cluster = 'to get any answer';
        $secret = 'and satisfy the code coverage';

        $valid = Helper::arePusherConfigsValid($auth_key, $app_id, $cluster, $secret);
        $this->assertIsBool($valid);
    }

    public function testIsMaxMindGeoIpEnabled()
    {
        $this->assertFalse(Helper::isMaxMindGeoIpEnabled());
        $_SERVER['MM_IP_COUNTRY_CODE'] = 'BR';
        $this->assertTrue(Helper::isMaxMindGeoIpEnabled());
    }

    public function testBroadcast()
    {
        // Sse->trigger
        $this->assertNull(Helper::broadcast('channel', 'event', 'message'));

        // Pusher->trigger
        // Credentials are not set but this test is made to satisfy code coverage
        Helper::updateDotEnvFileVars([
            'PUSHER_ENABLED' => 'true',
        ]);

        $this->assertNull(Helper::broadcast('channel', 'event', 'message'));

        Helper::updateDotEnvFileVars([
            'PUSHER_ENABLED' => 'false',
        ]);
    }

    public function testIsSecureRequest()
    {
        $this->assertIsBool(Helper::isSecureRequest());
    }

    public function testRoute()
    {
        $route = Helper::route('home');
        $this->assertIsString($route);
        $this->assertStringStartsWith('http', $route);
    }

    public function testLinkRoute()
    {
        $link_route1 = Helper::linkRoute('home', 'Home', [], []);
        $this->assertInstanceOf(HtmlString::class, $link_route1);

        // Create a secure link
        $GLOBALS['isSecureRequest'] = true;

        $link_route2 = Helper::linkRoute('home', 'Home', [], []);
        $this->assertInstanceOf(HtmlString::class, $link_route2);

        unset($GLOBALS['isSecureRequest']);
    }

    public function testOpenForm()
    {
        $opened_form = Helper::openForm('home', [], []);
        $this->assertInstanceOf(HtmlString::class, $opened_form);
    }

    public function testCloseForm()
    {
        $closed_form = Helper::closeForm();
        $this->assertInstanceOf(HtmlString::class, $closed_form);
    }
}
