<?php

namespace App\Test\Unit\Helper;

use Wexample\SymfonyHelpers\Helper\ClassHelper;
use Wexample\SymfonyHelpers\Tests\Class\AbstractApplicationTestCase;

class ClassHelperTest extends AbstractApplicationTestCase
{
    public function testHelper()
    {
        $this->assertEquals(
            'This\\Is\\ATest\\ClassPath',
            ClassHelper::buildClassNameFromPath('this/is/a_test/class-path')
        );
    }
}
