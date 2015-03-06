<?php


class TestBase {

    private $_result = array(); // [ { name=>'something', status=>'error/failed/passed', detail='...'}, ...]
    const ERROR = 'error';
    const FAILED = 'failed';
    const PASSED = 'passed';

    /**
     * 测试用例（方法）的前缀
     */
    const TEST_CASE_PREFIX = 'test';


    /**
     * 运行所有的测试用例
     */
    public static function runTests(){
        $instance = new static(time());
        return $instance->doRunTest();
    }

    /**
     * @param null $cases
     * @throws Exception
     */
    public function doRunTest($cases=null){
        try{
            $this->prepare();
            if (!$cases){
                $this->runAllTests();
            } else if (is_string($cases)){
                $this->runOneTest($cases);
            } else if (is_array($cases)){
                foreach ($cases as $case) {
                    $this->runOneTest($case);
                }
            }
            $this->reportResult();
            return $this->tearDown();
        } catch (Exception $e) {
            $this->tearDown();
            throw $e;
        }
    }

    public function prepare(){

    }
    public function tearDown(){

    }

    public function runAllTests(){
        $class = get_class($this);
        $methods = get_class_methods($class);
        $prefix = self::TEST_CASE_PREFIX;
        foreach ($methods as $m) {
            $pos = strpos($m, $prefix);
            //echo $m . " $pos <br/>";
            if ($pos === 0){
                $case = substr($m, strlen($prefix));
                $this->runOneTest($case);
            }
        }

    }

    public function runOneTest($case){
        trace("Begin running testcase $case");
        try{
            $this->setResult($case, self::FAILED);
            call_user_func_array(array($this, self::TEST_CASE_PREFIX . $case), array());
            $this->setResult($case, self::PASSED);
        } catch (TestFailedException $e){
            $this->setResult($case, self::FAILED, $e->getMessage(). ' : '. $e->getTraceAsString());
        } catch (Exception $e){
            $this->setResult($case, self::ERROR, $e->getMessage(). ' : '. $e->getTraceAsString());

        }
        trace("End running testcase '$case' => RESULT: " . $this->getResult($case)->status);
        if ($this->needTraceDetail() && $this->getResult($case)->status != self::PASSED){
            trace("Detailed:" . strval($this->getResult($case)->detail));
        }
    }
    public function reportResult(){
        $statistic = array();
        foreach ($this->_result as $result) {
            $statistic[$result->status]++;
        }
        echo "Result: ";
        foreach ($statistic as $status => $count) {
            echo "$status: $count ";
        }
        echo "\n";
        flush();
    }
    public function setResult($case, $status, $detail=null){
        $this->_result[$case] = $result = new stdClass();
        $result->name = $case;
        $result->status = $status;
        $result->detail = $detail;
    }
    public function getResult($case){
        return $this->_result[$case];
    }

    public function failed($msg){
        throw new TestFailedException($msg);
    }

    public function assert($condition, $msg=null){
        if (!$condition){
            $this->failed($msg);
        }
    }

    public function assertEqual($value, $expected, $msg=null){
        if ($value != $expected){
            $this->failed($msg.": \n value: ".var_export($value, true)."\n expected: ".var_export($expected, true));
        }
    }

    protected function needTraceDetail(){
        return true;
    }

}

class TestFailedException extends \Exception{

}

function trace($msg){
    $now = date('Y-m-d H:i:s');
    output("$now TRACE: " . strval($msg) . "\n");
}
function output($msg){
    echo $msg;
    flush();
}

