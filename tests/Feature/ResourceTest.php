<?php

namespace Tests\Feature;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;
use Tests\TestsHelper;

class ResourceTest extends TestCase
{
    public function testAppJs()
    {
        $url = TestsHelper::getRoutePath('js');

        $response = $this->call('GET', $url);

        $response->assertStatus(200);

        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);

        $content = TestsHelper::getTestResponseContent($response);

        $response->assertHeader('content-type', 'application/javascript');

        $this->assertStringEndsWith('public/js/app.js', $response->getFile()->getPathname());

        $this->assertStringContainsString('function', $content);
    }

    public function testQuestionsJs()
    {
        $url = TestsHelper::getRoutePath('questions');

        $response = $this->call('GET', $url);

        $response->assertStatus(200);

        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);

        $content = TestsHelper::getTestResponseContent($response);

        $response->assertHeader('content-type', 'application/javascript');

        $this->assertStringEndsWith('public/js/questions.js', $response->getFile()->getPathname());

        $this->assertStringContainsString('function', $content);
    }

    public function testStartSurveyJs()
    {
        $url = TestsHelper::getRoutePath('start-survey');

        $response = $this->call('GET', $url);

        $response->assertStatus(200);

        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);

        $content = TestsHelper::getTestResponseContent($response);

        $response->assertHeader('content-type', 'application/javascript');

        $this->assertStringEndsWith('public/js/start-survey.js', $response->getFile()->getPathname());

        $this->assertStringContainsString('function', $content);
    }

    public function testManageSurveyJs()
    {
        $url = TestsHelper::getRoutePath('manage-survey');

        $response = $this->call('GET', $url);

        $response->assertStatus(200);

        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);

        $content = TestsHelper::getTestResponseContent($response);

        $response->assertHeader('content-type', 'application/javascript');

        $this->assertStringEndsWith('public/js/manage-survey.js', $response->getFile()->getPathname());

        $this->assertStringContainsString('function', $content);
    }

    public function testStatsJs()
    {
        $url = TestsHelper::getRoutePath('stats');

        $response = $this->call('GET', $url);

        $response->assertStatus(200);

        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);

        $content = TestsHelper::getTestResponseContent($response);

        $response->assertHeader('content-type', 'application/javascript');

        $this->assertStringEndsWith('public/js/stats.js', $response->getFile()->getPathname());

        $this->assertStringContainsString('function', $content);
    }

    public function testAppCss()
    {
        $url = TestsHelper::getRoutePath('css');

        $response = $this->call('GET', $url);

        $response->assertStatus(200);

        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);

        $response->assertHeader('content-type', 'text/css');

        $this->assertStringEndsWith('public/css/app.css', $response->getFile()->getPathname());
    }

    public function testJpgImages()
    {
        $url = TestsHelper::getRoutePath('jpgImages', ['world-map.jpg']);

        $response = $this->call('GET', $url);

        $response->assertStatus(200);

        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);

        $response->assertHeader('content-type', 'image/jpg');

        $this->assertStringEndsWith('public/images/world-map.jpg', $response->getFile()->getPathname());
    }

    public function testFonts()
    {
        $url = TestsHelper::getRoutePath('fonts', ['glyphicons-halflings-regular.woff']);

        $response = $this->call('GET', $url);

        $response->assertStatus(200);

        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);

        $this->assertStringEndsWith('public/fonts/glyphicons-halflings-regular.woff', $response->getFile()->getPathname());
    }
}
