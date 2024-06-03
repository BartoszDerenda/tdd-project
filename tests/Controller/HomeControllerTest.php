<?php
/**
 * Home controller tests.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * class HelloControllerTest.
 */
class HomeControllerTest extends WebTestCase
{
    /**
     * Test '/hello' route.
     */
    public function testHomeControllerRoute(): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request('GET', '/home');
        $resultHttpStatusCode = $client->getResponse()->getStatusCode();

        // then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

}
