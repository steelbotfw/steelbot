<?php

function Element($name, $value, $check, $good = true) {
    if ($good) {
        $check = '<span class="check_good">'.$check.'</span>';
    } else {
        $check = '<span class="check_bad">'.$check.'</span>';
    }
    
    return "<td>$name</td>
            <td>$value</td>
            <td>$check</td>";
}

function CheckValue($option) {
    $return = '';
    switch ($option) {
        case 'ver': $ver = (int)phpversion();
                    if ($ver < 5) {
                        return Element('Версия PHP', $ver, 'Ошибка', false);
                    } else {
                        return Element('Версия PHP', $ver, 'OK');
                    }
            break;
 
            
        case 'timelimit': set_time_limit(0);
                            if( ($timelimit = (int)ini_get('max_execution_time')) > 0) {
                                return Element('Время работы скрипта', $timelimit, 'Ошибка', false);
                            } else {
                                return Element('Время работы скрипта', $timelimit, 'ОК', true);
                            }
    }
}

?>
<html>
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
    SteelBot web-hosting warning
  </title>
  <style>
  body, html {
    
  }
  
  table, tr, td {
    font-size: 12pt;
    font-family: Verdana;
  }
  h1 {
    font-size: 14pt;
  }
  a:link {
      color: #0000ff;
  }
  
  a:hover {
      color: #ff0000;
  }
  
  a:hover:visited {
      color: #ff0000;
  }
  
  a:visited {
      color: #0000ff;
  }
  
  a:link {
      color: #999999;
  }
  
  .footer {
      font-size: 10pt;
      color: #999999;
  } 
  
  
  .footer a:hover {
      color: #ff0000;
    
  }
  
  .footer a:hover:visited {
      color: #ff0000;
    
  }
  
  .footer a:visited {
      color: #999999;
  }
  
  .check_table {
      background-color: #777;
      
  }
  
  .check_table tr,td{
      background-color: #fff;
      padding: 5px;
  }
  
  .check_good {
      color: #008800;
  }
  
  .check_bad {
      color: #ff0000;
      font-weight: bold;
  }
 

  </style>
</head>

<body>
<table align="center" width="70%" height="100%">
    <tr>
        <td valign="top">
            <center><h1>SteelBot</h1></center>
            
        </td>
    </tr>
    
    <tr>
        <td height="100%" valign="top">
            <span style="color: #FF0000; font-weight:bold">Предупреждение</span>
             <p>   Вы пытаетесь запустить бота на веб-хостинге.
                <br>
                Так как хостинги не позволяют устанавливать бесконечное время
                работы скрипта, возможно бот будет работать некорректно.
                <br>               
                
                <h1>Проверка системы</h1>
                <table class="check_table" cellspacing="1">
                 <tr>
                    <td style="background:#eeeeee;">Опция</td>
                    <td style="background:#eeeeee;">Значение</td>
                    <td style="background:#eeeeee;">Проверка</td>
                 </tr>
                 <tr>
                    <?php echo CheckValue('ver');
                    ?>
                    
                 </tr>
                 <tr>
                    <?php echo CheckValue('sockets');
                    ?>
                 </tr>
                 <tr>
                    <?php echo CheckValue('timelimit');
                    ?>
                 </tr>
                 
                </table>
                <?php
                
                
              
                
                ?>
                <br>
                <h1>Действия</h1>
                <br>
                <form action="../bot.php?web=skip" method="POST">
                <label for="password">Пароль для запуска бота через веб:</label>
                <br>
                <input type="text" name="password"><br>
                <input type="submit" value="Запустить бота">
                </form>
                
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <div class="footer">
                <center>SteelBot by N3x^0r, <a href="http://steelbot.net">http://steelbot.net</a><br></center>
            </div>
        </td>
    </tr>
    
</table>
</body>

</html>