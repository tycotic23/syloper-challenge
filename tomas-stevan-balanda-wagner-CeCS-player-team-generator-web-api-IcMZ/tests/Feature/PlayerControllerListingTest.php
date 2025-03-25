<?php

// /////////////////////////////////////////////////////////////////////////////
// TESTING AREA
// THIS IS AN AREA WHERE YOU CAN TEST YOUR WORK AND WRITE YOUR TESTS
// /////////////////////////////////////////////////////////////////////////////

namespace Tests\Feature;

use App\Models\Player;

class PlayerControllerListingTest extends PlayerControllerBaseTest
{
    public function test_sample()
    {
        Player::factory()->withSkills(2)->create();

        $res = $this->get(self::REQ_URI);

        $res->assertJsonStructure(['*'=>["id","name","position","playerSkills"=>[
            '*'=>["skill","value"]
        ],]]);
        $this->assertNotNull($res);
    }

}
