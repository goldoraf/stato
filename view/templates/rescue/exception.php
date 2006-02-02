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
        <h1><?php echo get_class($this->exception)." in {$this->controller}::{$this->action}()"; ?></h1>
        <pre><?php echo $this->exception->getMessage(); ?></pre>
        <p><?php echo "Exception occured at : ".$this->exception->getFile()." : ".$this->exception->getLine(); ?></p>
        <table>
            <caption>Stack trace</caption>
            <tr><th>#</th><th>File</th><th>Line</th><th>Function</th></tr>
            <?php foreach ($this->exception->getTrace() as $num => $trace) { ?>
            <tr>
                <td><?php echo $num + 1; ?></td>
                <td><?php echo $trace['file']; ?></td>
                <td><?php echo $trace['line']; ?></td>
                <td><?php echo $trace['class'].$trace['type'].$trace['function']; ?></td>
            </tr>
            <?php } ?>
        </table>
  </body>
</html>
