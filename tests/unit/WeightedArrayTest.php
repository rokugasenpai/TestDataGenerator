<?php
use rokugasenpai\TestDataGenerator\WeightedArray;

class WeightedArrayTest extends PHPUnit_Framework_TestCase
{
    public function test_WeightedArrayの生成とランダム抽出()
    {
        $weighted = new WeightedArray();
        $weighted->append('common', 70);
        $weighted->append('uncommon', 25);
        $weighted->append('rare', 5);
        $num_common = 0;
        $num_uncommon = 0;
        $num_rare = 0;
        $num_unexpected = 0;
        for ($i = 0; $i < 1000; $i++)
        {
            $chosen = $weighted->rand();
            if ($chosen == 'common') $num_common++;
            else if ($chosen == 'uncommon') $num_uncommon++;
            else if ($chosen == 'rare') $num_rare++;
            else $num_unexpected++;
        }
        $this->assertLessThan($num_common, $num_uncommon);
        $this->assertLessThan($num_uncommon, $num_rare);
        $this->assertGreaterThan(0, $num_common);
        $this->assertGreaterThan(0, $num_uncommon);
        $this->assertGreaterThan(0, $num_rare);
        $this->assertEquals(0, $num_unexpected);
    }

    public function test_WeightedArrayの生成とget_array_without_weight()
    {
        $weighted = new WeightedArray();
        $weighted->append(100);
        $weighted->append(123.456);
        $weighted->append(TRUE);
        $weighted->append(FALSE);
        $weighted->append('');
        $weighted->append('abc');
        $weighted->append(['d', 'e', 'f']);
        $stack = new SplStack();
        $stack->push('ghi');
        $weighted->append($stack);
        $arr = $weighted->get_array_without_weight();
        $this->assertCount(8, $arr);
        $this->assertSame(100, $arr[0]);
        $this->assertSame(123.456, $arr[1]);
        $this->assertSame(TRUE, $arr[2]);
        $this->assertSame(FALSE, $arr[3]);
        $this->assertSame('', $arr[4]);
        $this->assertSame('abc', $arr[5]);
        $this->assertSame(['d', 'e', 'f'], $arr[6]);
        $this->assertEquals($stack, $arr[7]);
    }
}