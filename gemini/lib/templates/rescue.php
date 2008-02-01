<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Exception caught</title>
  <style>
    body { background-color: #fff; color: #333; }

    body, p, ol, ul, td {
      font-family: verdana, arial, helvetica, sans-serif;
      font-size:   13px;
      line-height: 18px;
    }

    pre {
      background-color: #eee;
      padding: 10px;
      font-size: 13px;
    }
    
    table {
        border: 1px solid #000;
        border-spacing: 0;
        border-collapse: collapse;
        margin: 0;
    }
    
    td {
        border: 1px solid #000;
        padding: 10px;
    }
    
    caption {
        font-weight: bold;
        text-align:left;
    }
  </style>
  </head>
  <body>
        <h1><?= get_class($exception); ?></h1>
        <pre><?= $exception->getMessage(); ?></pre>
        <p><?= "Exception occured at : ".$exception->getFile()." : ".$exception->getLine(); ?></p>
        <table>
            <caption>Stack trace</caption>
            <tr><th>#</th><th>File</th><th>Line</th><th>Function</th></tr>
            <? foreach ($exception->getTrace() as $num => $trace) : ?>
                <tr>
                    <td><?= $num + 1; ?></td>
                    <td><?= (isset($trace['file'])) ? $trace['file'] : ''; ?></td>
                    <td><?= (isset($trace['line'])) ? $trace['line'] : ''; ?></td>
                    <td><?= (isset($trace['class'])) ? $trace['class'].$trace['type'].$trace['function'] : $trace['function']; ?></td>
                </tr>
            <? endforeach; ?>
        </table>
  </body>
</html>
