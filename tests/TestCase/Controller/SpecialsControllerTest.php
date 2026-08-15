<?php
namespace App\Test\TestCase\Controller;

use App\Controller\SpecialsController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\SpecialsController Test Case
 */
class SpecialsControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'Specials' => 'app.specials',
        'Students' => 'app.students',
        'Schools' => 'app.schools',
        'Status' => 'app.status'
    ];

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex()
    {
        $this->get('/specials');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum dolor sit amet');
        $this->assertResponseContains('data-page-length="30"');
        $this->assertResponseNotContains('<div class="paginator">');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
