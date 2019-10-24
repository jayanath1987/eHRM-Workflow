<?php

 $arr = Array();
       
        foreach ($empidList as $list) {
            $arr[]=$list['wf_main_app_employee'];
          
        }
      

    echo json_encode($arr);
?>