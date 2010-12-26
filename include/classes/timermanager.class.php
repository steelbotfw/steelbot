<?php

class TimerManager extends SComponent {

	protected $savetimers,
			  $timerIds,
			  $timers = array(),
			  $nextTimer,
			  $maxtimerId;

	public function __construct($bot) {
		parent::__construct($bot);
	}

	public function init() {
		$this->nextTimer = 60*60*24*500;
		return true;
	}

	public function checkTimers() {
		$time = time();
		if ($time >= $this->nextTimer) {
			$this->TimerRun($time);
 		}
	}

    /**
     * @desc Записывает вызов заданной через заданное время
     * 
     * @param int $time время в секундах, по прохождении которого будет вызвана функция
     * @param string $func имя функции
     * @return int идентификатор таймера.
     */
    function TimerAdd($time,$func) {
        $time = time()+$time;
        $this->timerIds[ $this->maxtimerId ] = array($time, $func);
        $this->timers[$time][] = array($func, $this->maxtimerId);
        $this->nextTimer = min(array_keys($this->timers));        
        return $this->maxtimerid++;
    }

    /**
     * @desc Вызывает все функции, время вызова которых меньше чем заданное (т.е.
     * вызывает все функции, которые к моменту времени $time должны были выполнится,
     * но не выполнились
     *
     * @param int $time - время в формате UNIX
     */
    function TimerRun($time) {
        $called_handlers = 0;
        foreach ( array_keys($this->timers) as $timer_time) {
            if ($timer_time <= $time) {
                foreach ($this->timers[$timer_time] as $vv) {
                    if ( call_user_func($vv[0]) ){
                        $called_handlers++;
                    }
                    unset( $this->timerIds[$vv[1]] );
                }
                unset($this->timers[$timer_time]);
            }
        }
        return $called_handlers;
    }

    /**
     * Удаляет таймер по его идентификатору.
     * 
     * @param int $timer_id
     * @return bool
     */
    function TimerDeleteById($timer_id) {
        if (array_key_exists($timer_id, $this->timerIds)) {
            $timer_record = $this->timerIds[ $timer_id ];
            unset( $this->timerIds[$timer_id] );
            foreach ( $this->timers[$timer_record[0]] as $k=>$v ) {
                if ($v[1] == $timer_id) {
                    unset( $this->timers[$timer_record[0]][$k] );
                    break;
                }
            }        
            return true;
        } else {
            return false;
        }
    }

    /**
     * Синхронизация таймеров
     */
    function SyncTimers() {
        foreach ($this->timers as $k=>$v) {
            if (count($v) < 1) {
                unset($this->timers[$k]);
            }
        }
        $this->nextTimer = min(array_keys($this->timers));
        return $this->nextTimer;
    }

    /**
     * Записать таймеры на диск
     *
     */
    function SaveTimers() {
        //slog::add('core',"Saving timers ... ", LOG_CORE);
        $timers_file = STEELBOT_DIR.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.
                Proto::BotID().'.timers';
        if ( file_put_contents( $timers_file, serialize($this->timers)) ) {
            //slog::result("OK");
            return true;
        } else {
            //slog::result("ERROR");
            return false;
        }
    }

/**
 * Загрузить таймеры из файла.
 * @param string $filename - имя файла, из которого загружаются таймеры (необязательный параметр)
 */
function LoadTimers($filename = false) {
    
    if (!$filename) {
        $filename = STEELBOT_DIR.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.
            Proto::BotID().'.timers';
    }
    //slog::add('core', 'Loading timers '.$filename."... ", LOG_CORE);
    $count = 0;

    if ( is_readable($filename) ) {
        $timers = @unserialize( file_get_contents($filename) );

        if ( is_array($timers) ) {
            foreach ($timers as $time=>$funcs) {
                $now = time();
                if ($now < $time) {
                    foreach ($funcs as $func_record) {
                        self::TimerAdd($time-$now, $func_record[0]);
                        $count++;
                    }
                } else {
                    slog::add('core', "   timer skipped: ($time) ", LOG_CORE);
                }
            }
            slog::result(" OK");
        } else {
            slog::result(" no timers");
        }
        unlink($filename);
    } else {
        slog::result("OK");
    }
    return $count;
            
}

	

}
