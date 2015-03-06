<?php

require_once(__DIR__ . '/TestBase.php');
require_once(__DIR__ . '/DataQuery.php');
define('MAX_TEST_SIZE', 1000);
/**
 * 测试DataQuery
 * Class DataQueryTest
 */
class DataQueryTest extends TestBase {

    public function runOneTest($case){
        $initMemory = memory_get_usage();
        parent::runOneTest($case);
        $usedMemory = memory_get_usage() - $initMemory;
        trace("Used memory: ".convertSize($usedMemory));
    }

    public function testMemoryUsage(){
        $initMemory = memory_get_usage();
        $outputDelta = function($msg) use($initMemory){
            $usedMemory = memory_get_usage() - $initMemory;
            trace($msg." used memory: ".convertSize($usedMemory));
        };
        $outputDelta('Initial');
        $a = array_fill(0, MAX_TEST_SIZE, array('productId' => 1, 'productName' => 'test'));
        $outputDelta('Fill '.MAX_TEST_SIZE.' elements');
        unset($a);
        $outputDelta('After unset');
        call_user_func_array(function(){
            $a = array_fill(0, MAX_TEST_SIZE, array('productId' => 1, 'productName' => 'test'));
        }, null);
        $outputDelta('Fill '.MAX_TEST_SIZE.' elements in closure');
        call_user_func_array(function(){
        }, null);
        $outputDelta('Empty closure');
    }

    /**
     * alias映射
     */
    public function testBasicSelect1() {
        $data = array(
            array('productId' => 1, 'productName' => 'test'),
        );
        $data = DataQuery::from($data)
            ->select(array('productId' => 'id', 'productName' => 'name'))
            ->toArray();

        $expected = array(
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEqual($data, $expected);
    }

    /**
     * alias映射
     */
    public function testBasicSelect2() {
        $data = array_fill(0, MAX_TEST_SIZE,
            array('productId' => 1, 'productName' => 'test')
        );
        $data = DataQuery::from($data)->select(array('productId' => 'id', 'productName' => 'name'))
            ->toArray();

        $expected = array_fill(0, MAX_TEST_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEqual($data, $expected);
    }

    /**
     * alias映射
     */
    public function testBasicSelect3() {
        $data = array_fill(0, MAX_TEST_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $data = DataQuery::from($data)->select(array('id', 'name'))
            ->toArray();

        $expected = array_fill(0, MAX_TEST_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEqual($data, $expected);
    }

    /**
     * alias映射
     */
    public function testBasicSelect4() {
        $dataSource = array_fill(0, MAX_TEST_SIZE,
            array('id' => 1, 'productName' => 'test')
        );
        $data = DataQuery::from($dataSource)
            ->select(array('id', 'productName' => 'name'))
            ->toArray();

        $expected = array_fill(0, MAX_TEST_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEqual($data, $expected);
    }

    /**
     * alias映射
     */
    public function testBasicSelect5() {
        $dataSource = array_fill(0, MAX_TEST_SIZE,
            array('productId' => 1, 'name' => 'test')
        );
        $data = DataQuery::from($dataSource)
            ->select(array('productId' => 'id', 'name'))
            ->toArray();

        $expected = array_fill(0, MAX_TEST_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEqual($data, $expected);
    }

    /**
     * 如果参数为空则表示选择所有数据
     */
    public function testBasicSelect6() {
        $dataSource = array_fill(0, MAX_TEST_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $data = DataQuery::from($dataSource)
            ->select(array())
            ->toArray();

        $expected = array_fill(0, MAX_TEST_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEqual($data, $expected);
    }

    public function testWhere1() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE));
        $data = DataQuery::from($dataSource)
            ->select(array())
            ->where(array('id' => 1))
            ->toArray();

        $expected = array(
            array('id' => 1, 'name' => 'test1')
        );
        $this->assertEqual($data, $expected);
    }

    public function testWhere2() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE));
        $data = DataQuery::from($dataSource)
            ->select(array())
            ->where(function ($item) {
                return $item['id'] > 5;
            }, DataQuery::CONDITION_CUSTOM)
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(6, MAX_TEST_SIZE));
        $this->assertEqual($data, $expected);
    }

    /**
     * 单个key
     */
    public function testIndexedBy1() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE));
        $data = DataQuery::from($dataSource)
            ->indexedBy('id')
            ->toArray();

        $expected = array_reduce(array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE)), function ($carry, $item) {
            $carry[$item['id']] = $item;

            return $carry;
        }, array());
        $this->assertEqual($data, $expected);
    }

    /**
     * 单个key
     */
    public function testIndexedBy2() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        $data = DataQuery::from($dataSource)
            ->indexedBy('name')
            ->toArray();

        $expected = array_reduce(array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE)), function ($carry, $item) {
            $carry[$item['name']] = $item;

            return $carry;
        }, array());
        $this->assertEqual($data, $expected);
    }

    /**
     * 两个key
     */
    public function testIndexedBy3() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        $data = DataQuery::from($dataSource)
            ->indexedBy(array('id', 'name'))
            ->toArray();

        $expected = array_reduce(array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE)), function ($carry, $item) {
            $carry[$item['id'] . '_' . $item['name']] = $item;

            return $carry;
        }, array());
        $this->assertEqual($data, $expected);
    }

    public function testOrderBy1() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        shuffle($dataSource);

        $data = DataQuery::from($dataSource)
            ->orderBy('id')
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE));
        $this->assertEqual($data, $expected);
    }

    public function testOrderBy2() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        shuffle($dataSource);

        $data = DataQuery::from($dataSource)
            ->orderBy('id', SORT_DESC)
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(MAX_TEST_SIZE, 0, -1));
        $this->assertEqual($data, $expected);
    }

    public function testOrderBy3() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        shuffle($dataSource);

        $data = DataQuery::from($dataSource)
            ->orderBy(array('id' => SORT_DESC))
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(MAX_TEST_SIZE, 0, -1));
        $this->assertEqual($data, $expected);
    }

    public function testOrderBy4() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        shuffle($dataSource);

        $data = DataQuery::from($dataSource)
            ->orderBy(array('id' => SORT_DESC, 'name' => SORT_ASC))
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(MAX_TEST_SIZE, 0, -1));
        $this->assertEqual($data, $expected);
    }

    public function testGroupBy1(){

        $dataSource = array_merge(
            array_map(function($i){
                return array('id' => $i, 'name' => 'test'.$i, 'class' => 10);
            }, range(0, 10)),
            array_map(function($i){
                return array('id' => $i, 'name' => 'test'.$i, 'class' => 20);
            }, range(11, 20)),
            array_map(function($i){
                return array('id' => $i, 'name' => 'test'.$i, 'class' => 30);
            }, range(21, 30))
        );

        shuffle($dataSource);

        $data = DataQuery::from($dataSource)
            ->orderBy(array('id' => SORT_ASC))
            ->groupBy('class')
            ->toArray();

        $expected = array(
            10 =>
                array_map(function ($i) {
                    return array('id' => $i, 'name' => 'test' . $i, 'class' => 10);
                }, range(0, 10)),
            20 =>
                array_map(function ($i) {
                    return array('id' => $i, 'name' => 'test' . $i, 'class' => 20);
                }, range(11, 20)),
            30 =>
                array_map(function ($i) {
                    return array('id' => $i, 'name' => 'test' . $i, 'class' => 30);
                }, range(21, 30))
        );
        $this->assertEqual($data, $expected);
    }

    public function testCombo1() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'akkka' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        shuffle($dataSource);

        $data = DataQuery::from($dataSource)
            ->orderBy(array('id' => SORT_DESC, 'name' => SORT_ASC))
            ->select(array('id', 'name'))
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(MAX_TEST_SIZE, 0, -1));
        $this->assertEqual($data, $expected);
    }

    public function testCombo2() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'akkka' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        shuffle($dataSource);

        $data = DataQuery::from($dataSource)
            ->select(array('id', 'name'))
            ->where(function ($row) {
                return $row['id'] > 5;
            }, DataQuery::CONDITION_CUSTOM)
            ->orderBy('id', SORT_DESC)
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(MAX_TEST_SIZE, 6, -1));
        $this->assertEqual($data, $expected);
    }

    public function testCombo3() {
        $dataSource = array_map(function ($i) {
            return array(
                'productId' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'other' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        shuffle($dataSource);

        $data = DataQuery::from($dataSource)
            ->select(array('productId' => 'id', 'name'))
            ->where(function ($row) {
                return $row['id'] > 5;
            }, DataQuery::CONDITION_CUSTOM)
            ->orderBy('id', SORT_DESC)
            ->indexedBy('name')
            ->toArray();

        $expected = array_reduce(
            array_map(function ($i) {
                return array(
                    'id' => $i,
                    'name' => 'test' . $i
                );
            }, range(MAX_TEST_SIZE, 6, -1)),
            function ($carry, $item) {
                $carry[$item['name']] = $item;
                return $carry;
            }, array());
        $this->assertEqual($data, $expected);
    }

    public function testMap(){
        $dataSource = array_map(function ($i) {
            return array(
                'productId' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'other' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        shuffle($dataSource);

        $data = DataQuery::from($dataSource)
            ->select(array('productId' => 'id', 'name'))
            ->where(function ($row) {
                return $row['id'] > 5;
            }, DataQuery::CONDITION_CUSTOM)
            ->orderBy('id', SORT_DESC)
            ->indexedBy('name')
            ->map(function($row){ return $row['id']; })
            ->toArray();

        $expected = array_reduce(
            array_map(function ($i) {
                return array(
                    'id' => $i,
                    'name' => 'test' . $i
                );
            }, range(MAX_TEST_SIZE, 6, -1)),
            function ($carry, $item) {
                $carry[$item['name']] = $item['id'];
                return $carry;
            }, array());
        $this->assertEqual($data, $expected);
    }

    public function testIterator(){

        $dataSource = array_map(function ($i) {
            return array(
                'productId' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'other' . $i
            );
        }, range(0, MAX_TEST_SIZE));

        shuffle($dataSource);

        $query = DataQuery::from($dataSource)
            ->select(array('productId' => 'id', 'name'))
            ->where(function ($row) {
                return $row['id'] > 5;
            }, DataQuery::CONDITION_CUSTOM);

        $data = $query->toArray();

        foreach ($query as $key => $value) {
            echo $key . ' => '.var_export($value, true);
            $this->assertEqual($data[$key], $value);
        }

        foreach ($query as $value) {
            echo var_export($value, true);
        }

        $this->assertEqual(count($data), count($query));

        reset($query);
    }
}

function convertSize($size){
    $unit=array('byte','kb','mb','gb','tb','pb');
    return round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}
function output_memory_usage(){
    echo convertSize(memory_get_usage());
    echo PHP_EOL;
    flush();
}

list($exe, $case) = $argv;
if ($exe == basename(__FILE__)) {
    if ($case) {
        $i = new DataQueryTest();
        $i->doRunTest($case);
    } else {
        DataQueryTest::runTests();
    }
}