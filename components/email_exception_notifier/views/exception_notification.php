A <?= get_class($exception)." occured in {$controller_name}::{$action_name}()"; ?>

    <?= $exception->getMessage(); ?>
    
<?= "Exception occured at : ".$exception->getFile()." : ".$exception->getLine(); ?>


* URL: <?= $request->protocol().$request->host().'/'.$request->request_uri(); ?>

* Parameters: <?= var_export($request->params); ?>

* Session ID: <?= $session->id(); ?>

* Data: <?= var_export($session->data()); ?>
    

Stack trace
    
<?= $exception->getTraceAsString(); ?>