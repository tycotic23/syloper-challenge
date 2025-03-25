<?php

// /////////////////////////////////////////////////////////////////////////////
// TESTING AREA
// THIS IS AN AREA WHERE YOU CAN TEST YOUR WORK AND WRITE YOUR TESTS
// /////////////////////////////////////////////////////////////////////////////

namespace Tests\Feature;

use App\Models\Player;

class PlayerControllerUpdateTest extends PlayerControllerBaseTest
{
    public function test_sample()
    {
        $data = [
            "name" => "test",
            "position" => "defender",
            "playerSkills" => [
                0 => [
                    "skill" => "attack",
                    "value" => 60
                ],
                1 => [
                    "skill" => "speed",
                    "value" => 80
                ]
            ]
        ];

        Player::factory()->withSkills(2)->create();
       
        $res = $this->putJson(self::REQ_URI . '1', $data);
        $res->assertJsonStructure(["name","position","playerSkills"=>['*'=>["skill","value"]]]);
        $this->assertNotNull($res);
    }

    public function test_badSkill()
    {
        $data = [
            "name" => "test",
            "position" => "defender",
            "playerSkills" => [
                0 => [
                    "skill" => "brave",
                    "value" => 60
                ],
                1 => [
                    "skill" => "attack",
                    "value" => 80
                ]
            ]
        ];

        Player::factory()->withSkills(2)->create();

        $res = $this->putJson(self::REQ_URI . '1', $data);
        $res->assertJsonStructure(["message"]);
        $this->assertNotNull($res);
    }
    public function test_badPosition()
    {
        $data = [
            "name" => "test",
            "position" => "wizard",
            "playerSkills" => [
                0 => [
                    "skill" => "stamina",
                    "value" => 60
                ],
                1 => [
                    "skill" => "attack",
                    "value" => 80
                ]
            ]
        ];

        Player::factory()->withSkills(2)->create();

        $res = $this->putJson(self::REQ_URI . '1', $data);
        $res->assertJsonStructure(["message"]);
        $this->assertNotNull($res);
    }
    public function test_notRepeatSkill()
    {
        $data = [
            "name" => "test",
            "position" => "defender",
            "playerSkills" => [
                0 => [
                    "skill" => "attack",
                    "value" => 60
                ],
                1 => [
                    "skill" => "attack",
                    "value" => 80
                ]
            ]
        ];

        Player::factory()->withSkills(2)->create();

        $res = $this->putJson(self::REQ_URI . '1', $data);
        $res->assertJsonStructure(["message"]);
        $this->assertNotNull($res);
    }
}
