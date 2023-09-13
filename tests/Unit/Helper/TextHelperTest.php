<?php

namespace Wexample\SymfonyApi\Tests\Unit\Helper;

use Wexample\SymfonyHelpers\Helper\TextHelper;
use Wexample\SymfonyHelpers\Tests\Class\AbstractApplicationTestCase;

class TextHelperTest extends AbstractApplicationTestCase
{
    public function testHelper()
    {
        $this->assertEquals(
            'some-thing-in-class-case',
            TextHelper::toKebab('Some_ThingInClassCase')
        );
    }
}
