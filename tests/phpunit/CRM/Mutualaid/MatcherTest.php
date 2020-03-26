<?php
/*-------------------------------------------------------+
| SYSTOPIA Mutual Aid Extension                          |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
|         J. Schuppe (schuppe@systopia.de)               |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use Civi\Test\Api3TestTrait;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

use CRM_Mutualaid_ExtensionUtil as E;

/**
 *
 * @group headless
 */
class CRM_Mutialaid_MatcherTest extends CRM_Mutualaid_TestBase
{
    use Api3TestTrait {
        callAPISuccess as protected traitCallAPISuccess;
    }


    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests the simples match
     */
    public function testSimplestMatch()
    {
        $help_offer   = $this->createHelpOffer(['types' => [1]]);
        $help_request = $this->createHelpRequest(['types' => [1]]);
        $this->runMatcher();

        $this->assertRequestIsMatched($help_request['contact_id'], $help_offer['contact_id'], [1]);
    }

    /**
     * Tests the calculateDistance function
     */
    public function testDistanceCalculation()
    {
        // medium range example
        $p1 = [6.9073476, 50.9541944];
        $p2 = [7.105531,  50.7308496];
        $expected_distance_p1_p2 = 28500;

        $calculated_distance_p1_p2 = CRM_Mutualaid_Matcher::calculateDistance($p1, $p2);
        $deviation_p1_p2 = abs($expected_distance_p1_p2 - $calculated_distance_p1_p2);
        $this->assertLessThan(100, $deviation_p1_p2); // 100m accepted

        // short range example
        $p3 = [6.9260379,  50.9463283];
        $expected_distance_p1_p3 = 1580;

        $calculated_distance_p1_p3 = CRM_Mutualaid_Matcher::calculateDistance($p1, $p3);
        $deviation_p1_p3 = abs($expected_distance_p1_p3 - $calculated_distance_p1_p3);
        $this->assertLessThan(10, $deviation_p1_p3); // 10m accepted

        // long range example
        $p4 = [-122.4804438,  37.8199328];
        $expected_distance_p1_p4 = 9003990;

        $calculated_distance_p1_p4 = CRM_Mutualaid_Matcher::calculateDistance($p1, $p4);
        $deviation_p1_p4 = abs($expected_distance_p1_p4 - $calculated_distance_p1_p4);
        $this->assertLessThan(30000, $deviation_p1_p4); // 30km accepted

    }


    /**
     * Tests a simple match, but with 100km distance
     */
    public function testLongRangeMatch()
    {
        $help_offer   = $this->createHelpOffer(['types' => [1], 'radius' => 100000]);
        $help_request = $this->createHelpRequest(['types' => [1]]);
        $this->runMatcher();

        $this->assertRequestIsMatched($help_request['contact_id'], $help_offer['contact_id'], [1]);
    }
}
