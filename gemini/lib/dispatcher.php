<?php

define('STATO_APP_PATH', STATO_APP_ROOT_PATH.'/app');

interface SIDispatchable
{
    public static function instantiate($name, $module = null);
    
    public function dispatch(SRequest $request, SResponse $response);
    
    public function process_to_log($request);
}

class SDispatchException extends Exception {}

class SDispatcher
{	
    protected $request;
    protected $logger;
    protected $response;
    
    public function __construct()
    {
        set_error_handler('stato_error_handler');
        
        $this->request = new SRequest();
        $this->response = new SResponse();
        $this->logger = SLogger::get_instance();
    }
    
    public function dispatch() 
    {
    	try
    	{
            $map = include(STATO_APP_ROOT_PATH.'/conf/routes.php');
            SRoutes::initialize($map);
            $this->request->inject_params(SRoutes::recognize($this->request->request_uri()));
            SUrlRewriter::initialize($this->request);
            
    		if (file_exists($path = STATO_APP_PATH.'/helpers/application_helper.php')) require_once($path);
            
            if (isset($this->request->params['module'])) $module = $this->request->params['module'];
            else $module = null;
            
            if (isset($this->request->params['resource']) && !isset($this->request->params['controller']))
                $dispatchable = SResource::instantiate($this->request->params['resource'], $module);
            elseif (isset($this->request->params['controller']) && !isset($this->request->params['resource']))
                $dispatchable = SActionController::instantiate($this->request->params['controller'], $module);
            else
                throw new SDispatchException('No dispatchable class identified !');
            
            $this->log_processing($dispatchable->process_to_log($this->request));
    		
    		$this->response = $dispatchable->dispatch($this->request, $this->response);
        }
        catch (Exception $e)
        {
            $this->logger->log_error($e);
            $this->response = SRescue::response($this->request, $this->response, $e);
        }
        
        $this->response->out();
        
        $this->log_benchmarking();
	}
    
    protected function log_processing($process)
    {
        if (is_array($process)) $process = $process[0].'::'.$process[1];
        
        $log = "\nProcessing {$process}() for ".$this->request->remote_ip().' at '
            .SDateTime::today()->__toString().' ['.strtoupper($this->request->method()).']';
        
        //if (($sess_id = session_id()) != '') $log.= "\n    Session ID: ".$sess_id;
        $log.= "\n    Parameters: ".serialize($this->request->params);
        
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
