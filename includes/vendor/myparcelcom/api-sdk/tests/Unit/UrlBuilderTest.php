<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Utils\UrlBuilder;
use PHPUnit\Framework\TestCase;

class UrlBuilderTest extends TestCase
{
    /** @test */
    public function testUrl()
    {
        $url = new UrlBuilder('https://lol:wut@sub.domain.com:1337/dir?yup=nope#dope');
        $this->assertEquals('https://lol:wut@sub.domain.com:1337/dir?yup=nope#dope', $url->getUrl());

        $this->assertEquals(['que', 'pasa'], $url->setQuery(['que'])->addQuery(['pasa'])->getQuery());
        $this->assertEquals('torrent', $url->setScheme('torrent')->getScheme());
        $this->assertEquals('guest', $url->setHost('guest')->getHost());
        $this->assertEquals('Tawny', $url->setPort('Tawny')->getPort());
        $this->assertEquals('GabeN', $url->setUser('GabeN')->getUser());
        $this->assertEquals('hl3c0nf1rm3d', $url->setPassword('hl3c0nf1rm3d')->getPassword());
        $this->assertEquals('/bin', $url->setPath('/bin')->getPath());
        $this->assertEquals('imagination', $url->setFragment('imagination')->getFragment());

        $this->assertEquals('torrent://GabeN@guest:Tawny/bin?0=que&1=pasa#imagination', (string)$url->setPassword(''));
    }
}
