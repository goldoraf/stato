<?php

define('STATO_APP_PATH', STATO_APP_ROOT_PATH.'/app');

interface SIDispatchable
{
    public static function instanciate($name);
    
    public function dispatch(SRequest $request);
    
    public function process_to_log($request);
}

class SDispatchException extends Exception {}

class SDispatcher
{	
	protected $logger;
    
    public function __construct()
    {
        $this->logger = SLogger::get_instance();
    }
    
    public function dispatch() 
    {
    	try
    	{
            $map = include(STATO_APP_ROOT_PATH.'/conf/routes.php');
            SRoutes::initialize($map);
            $request = SRoutes::recognize(new SRequest());
            
    		if (file_exists($path = STATO_APP_PATH.'/helpers/application_helper.php')) require_once($path);
            
            if (isset($request->params['resource']) && !isset($request->params['controller']))
                $dispatchable = SResource::instanciate($request->params['resource']);
            elseif (isset($request->params['controller']) && !isset($request->params['resource']))
                $dispatchable = SActionController::instanciate($request->params['controller']);
            else
                throw new SDispatchException('No dispatchable class identified !');
            
            $this->log_processing($dispatchable->process_to_log($request), $request);
    		
    		$response = $dispatchable->dispatch($request);
        }
        catch (Exception $e)
        {
            $this->logger->log_error($e);
            
            if (SActionController::$consider_all_requests_local)
                $response = SRescue::locally($request, $e);
            else
                $response = SRescue::in_public($request, $e);
        }
        
        $response->out();
        
        $this->log_benchmarking();
	}
    
    protected function log_processing($process, $request)
    {
        if (is_array($process)) $process = $process[0].'::'.$process[1];
        
        $log = "\nProcessing {$process}() for ".$request->remote_ip().' at '
            .SDateTime::today()->__toString().' ['.strtoupper($request->method()).']';
        
        //if (($sess_id = session_id()) != '') $log.= "\n    Session ID: ".$sess_id;
        $log.= "\n    Parameters: ".serialize($request->params);
        
        $this->logger->info($log);
    }
    
    protected function log_benchmarking()
    {
        $runtime = microtime(true) - STATO_TIME_START;
        $info = 'Completed in '.sprintf("%.5f", $runtime).' seconds';
        if (class_exists('SActiveRecord', false))
        {
            $db_runtime = SActiveRecord::connection_benchmark();
            $db_percentage = ($db_runtime * 100) / $runtime;
            $info.= ' | DB: '.sprintf("%.5f", $db_runtime).' ('.sprintf("%d", $db_percentage).' %)';
        }
        $this->logger->info($info);
        
        if (class_exists('SActiveRecord', false) && SActiveRecord::$log_sql === true)
            SActiveRecord::connection()->write_log();
    }
}

?>
