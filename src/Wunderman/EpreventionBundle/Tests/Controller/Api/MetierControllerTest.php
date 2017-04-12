<?php
namespace Wunderman\EpreventionBundle\Tests\Controller\Api;

use Wunderman\EpreventionBundle\Test\ApiTestCase;

class MetierControllerTest extends ApiTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testPOST()
    {
        // Form (fields, data)
        $data = array(
            'titre' => 'nouveau métier',
            'code' => 1000,
           // 'remote_id' => '2000'
        );

        // 1) Create a metier
        $response = $this->client->post('/api/metiers', [
            'body' => json_encode($data)
        ]);

        // 1) Check creation success
        $this->assertEquals(201, $response->getStatusCode());
        // get response body
        $finishedData = json_decode($response->getBody(true), true);

        //2)  Check response fields list (same list as setup in $data)
        $this->assertArrayHasKey('id', $finishedData);
        foreach ($data as $key => $value){
            $this->assertArrayHasKey($key, $finishedData);
        }

        // 3) Check response data (same list as setup in $data)
        foreach ($data as $key => $value){
            $this->assertEquals($value, $finishedData[$key]);
        }

    }

    public function testGETMetier()
    {
        /*
        $response = $this->client->get('/api/metiers/1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, array(
            'id',
            'titre',
            'code',
        ));
        $this->asserter()->assertResponsePropertyEquals($response, 'titre', 'nouveau métier');
        */
        /*
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            '_links.self',
            $this->adjustUri('/api/programmers/UnitTester')
        );
        */
    }
    public function testValidationErrors()
    {
        /*
         * Test empty fields
         */
        $data = array(
            'titre' => '',
            'code' => '',
            //'remote_id' => ''
        );

        $response = $this->client->post('/api/metiers', [
            'body' => json_encode($data)
        ]);

        $finishedData = json_decode($response->getBody(true), true);
        $this->assertEquals(400, $response->getStatusCode());


        // titre
        $this->assertArrayHasKey('errors', $finishedData['children']['titre']);
        $this->assertEquals('Please enter a clever title', $finishedData['children']['titre']['errors'][0]);
        // code
        $this->assertArrayHasKey('errors', $finishedData['children']['code']);
        $this->assertEquals('Please enter a clever code', $finishedData['children']['code']['errors'][0]);

        /*
        * Test UNIQUE Fields
        */

    }

}
