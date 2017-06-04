<?php  
class WaitEvent extends \Workerman\Events\Select
{     
    public function event_select(){
        $read = $this->_readFds;
        $write = $except = [];
        $ret =  @stream_select($read, $write, $except, 0, (int)($this->_selectTimeout.''));
        if($ret){
            return $read;
        }
    }
    public function event_trigger($fd_key){ 
        if (isset($this->_allEvents[$fd_key][self::EV_READ])) {
            call_user_func_array($this->_allEvents[$fd_key][self::EV_READ][0],
                array($this->_allEvents[$fd_key][self::EV_READ][1]));
        }
    }
}
