<?php

class TextTest extends Unittest_TestCase
{
    /**
     * @covers Text::strip_tags
     * @covers Text::remove_event_attributes
     * @covers Text::remove_event_attributes_from_tag
     */
    function testStripTags()
    {
        $html = "<b>dsadasd</b><p onclick=\"javascript::alert('dd')\" >sd</p>";
        $this->assertSame('<b>dsadasd</b><p  >sd</p>', Text::strip_tags($html));
    }

    /**
     * @covers Text::truncate
     */
    function testTruncate()
    {
        $this->assertSame(
            'Learn more about...',
            Text::truncate('Learn more about sdfds', 20 )
        );
    }

    /**
     * @covers Text::month_name
     */
    function testMonthName()
    {
        $this->assertSame(
            tr('January'),
            Text::month_name(1)
        );
        $this->assertNull(Text::month_name(354));
    }

    /**
     * @covers Text::strip_ansi_color
     */
    function testStripAnsiColor()
    {
        $text = "\033[1mHello Bold World!\033[0m\n";
        $this->assertSame(" Hello Bold World! \n", Text::strip_ansi_color($text));
    }

    /**
     * @coversNothing
     */
    function providerXssClean()
    {
        return array(
            array(
                '<IMG SRC=javascript:alert("XSS")>',
                '<IMG SRC=nojavascript...alert("XSS")>'
            ),
        );
    }
    /**
     * @dataProvider providerXssClean
     * @covers Text::xss_clean
     */
    function testXSSClean($xss, $result)
    {
        $this->assertSame($result, Text::xss_clean($xss));
    }
}
