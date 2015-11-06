<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 15.08.2015
 * Time: 21:44
 */





class hiweb_cron {


    static private function getArr_fromStr($jobs = '') {
        $array = explode("\r\n", trim($jobs)); // trim() gets rid of the last \r\n
        foreach ($array as $key => $item) {
            if ($item == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }

    static private function getStr_fromArr($jobs = array()) {
        $string = implode("\r\n", $jobs);
        return $string;
    }

    static public function getArr_jobs() {
        $output = shell_exec('crontab -l');
        return self::getArr_fromStr($output);
    }

    static public function do_saveJobs($jobs = array()) {
        $output = shell_exec('echo "'.self::getStr_fromArr($jobs).'" | crontab -');
        return $output;
    }

    static public function getBool_jobExists($job = '') {
        $jobs = self::getArr_jobs();
        if (in_array($job, $jobs)) {
            return true;
        } else {
            return false;
        }
    }

    static public function do_addJob($job = '') {
        if (self::getBool_jobExists($job)) {
            return false;
        } else {
            $jobs = self::getArr_jobs();
            $jobs[] = $job;
            return self::do_saveJobs($jobs);
        }
    }

    static public function do_removeJob($job = '') {
        if(hiweb()->string()->getBool_isRegex($job)){
            $jobs = self::getArr_jobs();
            foreach($jobs as $j){
                if(preg_match($job,$j) > 0) unset($jobs[array_search($job, $jobs)]);
            }
            return self::do_saveJobs($jobs);
        } else {
            if (self::getBool_jobExists($job)) {
                $jobs = self::getArr_jobs();
                unset($jobs[array_search($job, $jobs)]);
                return self::do_saveJobs($jobs);
            } else {
                return false;
            }
        }
    }


    public function do_clearJobs(){
        exec('crontab -r', $crontab);
    }


}