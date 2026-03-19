<?php
    
    function get_chn_option($chn)
    {
        $filename = "C:\\xampp\\htdocs\\default\\chn-setting.json";
        $fp = fopen($filename, "r");
        $json_obj = fread($fp, filesize($filename));
        $json_obj = json_decode($json_obj);
        fclose($fp);
        foreach($json_obj->{"chn_name"} as $key => $val)
        {
            $str_buf = $json_obj->{"chn_type"}->{$key};
            if ($str_buf == "1" || $str_buf == "2" || $str_buf == "3" || $str_buf == "8" || $str_buf == "9")
            {
                if ($chn == $key)
                {
    
                    echo '<option value="' . $key . '" selected="selected">' . $val . '</option>';
                }
                else
                {
                    echo '<option value="' . $key . '">' . $val . '</option>';
                }
            }
        }
    }
    
?>
